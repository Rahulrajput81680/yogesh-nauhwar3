<?php


// Prevent direct access
if (!defined('ADMIN_INIT')) {
  die('Direct access not permitted');
}

/**
 * Generate a cryptographically secure reset token
 * 
 * @return string 64-character hexadecimal token
 */
function generate_reset_token()
{
  return bin2hex(random_bytes(32));
}

/**
 * Ensure password_resets table exists.
 * This keeps reset flow operational on partially imported local databases.
 */
function ensure_password_reset_table($pdo)
{
  static $checked = false;

  if ($checked) {
    return;
  }

  $checked = true;

  $sql = "CREATE TABLE IF NOT EXISTS password_resets (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    reset_token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY reset_token (reset_token),
    KEY idx_reset_token (reset_token),
    KEY idx_user_id (user_id),
    KEY idx_expires_at (expires_at),
    CONSTRAINT password_resets_ibfk_1 FOREIGN KEY (user_id) REFERENCES admin_users (id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

  $pdo->exec($sql);
}

/**
 * Create a password reset request
 * 
 * @param PDO $pdo Database connection
 * @param string $email User's email address
 * @return array Result with success status and message
 */
function create_password_reset_request($pdo, $email)
{
  try {
    $genericSuccessMessage = 'If your email is registered, you will receive a password reset link.';

    ensure_password_reset_table($pdo);
    // Check rate limiting first
    // if (!check_reset_rate_limit($pdo, $email)) {
    //   return [
    //     'success' => false,
    //     'message' => 'Too many password reset requests. Please try again later.',
    //     'user_exists' => false
    //   ];
    // }

    // Check if user exists and is active
    $stmt = $pdo->prepare("
      SELECT id, username, email, full_name 
      FROM admin_users 
      WHERE email = ? AND status = 'active' 
      LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
      // Don't reveal if email exists (security best practice)
      return [
        'success' => true,
        'message' => $genericSuccessMessage,
        'user_exists' => false
      ];
    }

    // Generate secure token
    $token = generate_reset_token();
    $expires_at = date('Y-m-d H:i:s', strtotime('+30 minutes'));

    // Invalidate any existing unused tokens for this user
    $invalidateStmt = $pdo->prepare("
      UPDATE password_resets 
      SET used = 1 
      WHERE user_id = ? AND used = 0 AND expires_at > NOW()
    ");
    $invalidateStmt->execute([$user['id']]);

    // Insert new reset token
    $insertStmt = $pdo->prepare("
      INSERT INTO password_resets (user_id, reset_token, expires_at, used) 
      VALUES (?, ?, ?, 0)
    ");
    $insertStmt->execute([$user['id'], $token, $expires_at]);

    if (empty($user['email']) || !filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
      error_log('Password reset email skipped due to invalid registered email for user ID ' . $user['id']);
      return [
        'success' => true,
        'message' => $genericSuccessMessage,
        'user_exists' => true
      ];
    }

    // Send reset email
    $reset_link = BASE_URL . '/admin/reset-password.php?token=' . $token;
    $email_sent = send_password_reset_email($user, $reset_link, $expires_at);

    // Log activity for auditing even when fallback link is used.
    log_activity('password_reset_requested', 'authentication', $user['id'], 'Password reset requested for ' . $user['email']);

    if ($email_sent) {
      return [
        'success' => true,
        'message' => $genericSuccessMessage,
        'user_exists' => true,
      ];
    }

    error_log('Password reset email dispatch failed for: ' . $user['email']);

    return [
      'success' => true,
      'message' => $genericSuccessMessage,
      'user_exists' => true
    ];
  } catch (PDOException $e) {
    error_log('Password reset request error: ' . $e->getMessage());
    return [
      'success' => false,
      'message' => 'An error occurred. Please try again later.',
      'user_exists' => false
    ];
  }
}

/**
 * Check rate limiting for password reset requests
 * Max 3 requests per hour per email
 * 
 * @param PDO $pdo Database connection
 * @param string $email User's email address
 * @return bool True if request is allowed, false if rate limit exceeded
 */
function check_reset_rate_limit($pdo, $email)
{
  try {
    // Get user ID from email
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
      // For non-existent emails, still apply a basic rate limit to prevent enumeration
      return true;
    }

    // Count reset requests in the last hour
    $countStmt = $pdo->prepare("
      SELECT COUNT(*) as count 
      FROM password_resets 
      WHERE user_id = ? 
      AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $countStmt->execute([$user['id']]);
    $result = $countStmt->fetch();

    // Allow max 3 requests per hour
    return $result['count'] < 3;
  } catch (PDOException $e) {
    error_log('Rate limit check error: ' . $e->getMessage());
    return false; // Fail safely by denying request
  }
}

/**
 * Validate a password reset token
 * 
 * @param PDO $pdo Database connection
 * @param string $token Reset token
 * @return array Validation result with user data or error
 */
function validate_reset_token($pdo, $token)
{
  try {
    ensure_password_reset_table($pdo);
    if (empty($token) || strlen($token) !== 64) {
      return [
        'valid' => false,
        'error' => 'Invalid reset token format.'
      ];
    }

    // Get reset record with user data
    $stmt = $pdo->prepare("
      SELECT 
        pr.id as reset_id,
        pr.user_id,
        pr.expires_at,
        pr.used,
        au.username,
        au.email
      FROM password_resets pr
      INNER JOIN admin_users au ON pr.user_id = au.id
      WHERE pr.reset_token = ?
      LIMIT 1
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
      return [
        'valid' => false,
        'error' => 'Invalid or expired reset link.'
      ];
    }

    // Check if token has been used
    if ($reset['used'] == 1) {
      return [
        'valid' => false,
        'error' => 'This reset link has already been used.'
      ];
    }

    // Check if token has expired
    if (strtotime($reset['expires_at']) < time()) {
      return [
        'valid' => false,
        'error' => 'This reset link has expired. Please request a new one.'
      ];
    }

    return [
      'valid' => true,
      'reset_id' => $reset['reset_id'],
      'user_id' => $reset['user_id'],
      'username' => $reset['username'],
      'email' => $reset['email']
    ];
  } catch (PDOException $e) {
    error_log('Token validation error: ' . $e->getMessage());
    return [
      'valid' => false,
      'error' => 'An error occurred while validating the token.'
    ];
  }
}

/**
 * Reset user password using valid token
 * 
 * @param PDO $pdo Database connection
 * @param string $token Reset token
 * @param string $new_password New password
 * @return array Result with success status and message
 */
function reset_user_password($pdo, $token, $new_password)
{
  try {
    // Validate token first
    $validation = validate_reset_token($pdo, $token);

    if (!$validation['valid']) {
      return [
        'success' => false,
        'message' => $validation['error']
      ];
    }

    // Validate password strength
    if (strlen($new_password) < 6) {
      return [
        'success' => false,
        'message' => 'Password must be at least 6 characters long.'
      ];
    }

    // Hash the new password
    $hashed_password = hash_password($new_password);

    // Begin transaction
    $pdo->beginTransaction();

    try {
      // Update user password
      $updateStmt = $pdo->prepare("
        UPDATE admin_users 
        SET password = ? 
        WHERE id = ?
      ");
      $updateStmt->execute([$hashed_password, $validation['user_id']]);

      // Mark token as used
      $markUsedStmt = $pdo->prepare("
        UPDATE password_resets 
        SET used = 1 
        WHERE id = ?
      ");
      $markUsedStmt->execute([$validation['reset_id']]);

      // Log activity
      log_activity('password_reset_completed', 'authentication', $validation['user_id'], 'Password successfully reset');

      // Commit transaction
      $pdo->commit();

      // Destroy any active sessions for this user (security measure)
      destroy_user_sessions($validation['user_id']);

      return [
        'success' => true,
        'message' => 'Password reset successfully. You can now login with your new password.'
      ];
    } catch (Exception $e) {
      $pdo->rollBack();
      throw $e;
    }
  } catch (PDOException $e) {
    error_log('Password reset error: ' . $e->getMessage());
    return [
      'success' => false,
      'message' => 'An error occurred while resetting your password. Please try again.'
    ];
  }
}

/**
 * Destroy all active sessions for a user
 * This is called after password reset for security
 * 
 * @param int $user_id User ID
 * @return void
 */
function destroy_user_sessions($user_id)
{
  // For this implementation, we'll just clear the current session if it matches
  if (isset($_SESSION['admin_id']) && $_SESSION['admin_id'] == $user_id) {
    session_unset();
    session_destroy();
  }
}

/**
 * Send password reset email
 * 
 * @param array $user User data array
 * @param string $reset_link Password reset link
 * @param string $expires_at Token expiration time
 * @return bool True if email sent successfully
 */
function send_password_reset_email($user, $reset_link, $expires_at)
{
  $to = $user['email'];
  $subject = PROJECT_NAME . ' - Password Reset Instructions';
  $expiry_time = date('F j, Y \a\t g:i A', strtotime($expires_at));
  $current_year = date('Y');
  $recipient_name = htmlspecialchars($user['full_name'] ?? $user['username'], ENT_QUOTES, 'UTF-8');
  $reset_link_safe = htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8');
  $project_name = htmlspecialchars(PROJECT_NAME, ENT_QUOTES, 'UTF-8');

  $message = <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body { margin: 0; padding: 0; background: #edf7f0; font-family: Arial, Helvetica, sans-serif; color: #1f2937; }
    .wrapper { width: 100%; padding: 32px 12px; }
    .container { max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 18px 48px rgba(17, 24, 39, 0.12); }
    .header { background: linear-gradient(135deg, #2f8f5b 0%, #1f6f4a 100%); color: #ffffff; padding: 36px 32px; text-align: center; }
    .badge { display: inline-block; margin-bottom: 16px; padding: 8px 14px; border-radius: 999px; background: rgba(255,255,255,0.16); font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; }
    .content { padding: 34px 32px 28px; }
    .lead { font-size: 16px; line-height: 1.7; margin: 0 0 18px; }
    .button-wrap { text-align: center; margin: 28px 0; }
    .button { display: inline-block; padding: 14px 28px; background: linear-gradient(135deg, #2f8f5b 0%, #23734a 100%); color: #ffffff !important; text-decoration: none; border-radius: 12px; font-weight: 700; }
    .link-box { word-break: break-all; background: #f4faf6; border: 1px solid #cfe8d8; padding: 14px 16px; border-radius: 12px; color: #1f2937; }
    .note { margin: 24px 0; padding: 16px 18px; border-left: 4px solid #2f8f5b; background: #f4faf6; border-radius: 10px; font-size: 14px; line-height: 1.65; }
    .footer { padding: 18px 32px 30px; text-align: center; color: #6b7280; font-size: 13px; background: #ffffff; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="container">
      <div class="header">
        <div class="badge">Password Assistance</div>
        <h1 style="margin: 0; font-size: 28px;">Password Reset Request</h1>
        <p style="margin: 12px 0 0; opacity: 0.95;">Secure instructions for resetting your admin password</p>
      </div>
      <div class="content">
        <p class="lead">Hello {$recipient_name},</p>
        <p class="lead">We received a request to reset the password for your admin account. If you did not make this request, you can safely ignore this email.</p>
        <div class="button-wrap">
          <a href="{$reset_link_safe}" class="button">Reset Password</a>
        </div>
        <p class="lead" style="margin-bottom: 10px;">You can also copy and paste this link into your browser:</p>
        <div class="link-box">{$reset_link_safe}</div>
        <div class="note">
          <strong>Important:</strong><br>
          This link expires on <strong>{$expiry_time}</strong> and can only be used once.
        </div>
        <p class="lead" style="margin-bottom: 0;">For better account security, we recommend using a strong unique password and enabling two-factor authentication if available.</p>
      </div>
      <div class="footer">
        <p style="margin: 0 0 8px;">&copy; {$current_year} {$project_name}. All rights reserved.</p>
        <p style="margin: 0;">This is an automated email. Please do not reply.</p>
      </div>
    </div>
  </div>
</body>
</html>
HTML;

  $plain_message = "Hello " . ($user['full_name'] ?? $user['username']) . ",\n\n" .
    "We received a request to reset the password for your admin account. If you did not make this request, you can safely ignore this email.\n\n" .
    "Reset password link:\n" . $reset_link . "\n\n" .
    "This link expires on: " . $expiry_time . "\n" .
    "This link can only be used once.\n\n" .
    "For better account security, we recommend using a strong unique password and enabling two-factor authentication if available.\n\n" .
    "" . PROJECT_NAME . "\n" .
    "This is an automated email. Please do not reply.";

  // Send via send_email() which uses SMTP if configured, else PHP mail()
  $mail_sent = send_email($to, $subject, $message, $plain_message);

  if (!$mail_sent) {
    error_log("Failed to send password reset email to: " . $to);
  }

  return $mail_sent;
}

/**
 * Clean up expired and used tokens
 * Should be called periodically (e.g., via cron job)
 * 
 * @param PDO $pdo Database connection
 * @return int Number of tokens cleaned up
 */
function cleanup_expired_reset_tokens($pdo)
{
  try {
    ensure_password_reset_table($pdo);
    $stmt = $pdo->prepare("
      DELETE FROM password_resets 
      WHERE expires_at < NOW() OR used = 1
    ");
    $stmt->execute();
    return $stmt->rowCount();
  } catch (PDOException $e) {
    error_log('Token cleanup error: ' . $e->getMessage());
    return 0;
  }
}
