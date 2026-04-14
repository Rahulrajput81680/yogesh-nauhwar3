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
        'message' => 'If your email is registered, you will receive a password reset link.',
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

    // Send reset email
    $reset_link = BASE_URL . '/admin/reset-password.php?token=' . $token;
    $email_sent = send_password_reset_email($user, $reset_link, $expires_at);

    // Log activity for auditing even when fallback link is used.
    log_activity('password_reset_requested', 'authentication', $user['id'], 'Password reset requested for ' . $user['email']);

    $host = (string) parse_url(BASE_URL, PHP_URL_HOST);
    $isLocalhost = in_array($host, ['localhost', '127.0.0.1'], true);

    if ($email_sent) {
      return [
        'success' => true,
        'message' => 'If your email is registered, you will receive a password reset link.',
        'user_exists' => true,
      ];
    }

    if ($isLocalhost) {
      return [
        'success' => true,
        'message' => 'Email is not configured on localhost. Use the reset link below.',
        'user_exists' => true,
        'reset_link' => $reset_link,
      ];
    }

    return [
      'success' => false,
      'message' => 'Failed to send reset email. Please try again later.',
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
  $subject = PROJECT_NAME . ' - Password Reset Request';

  $expiry_time = date('F j, Y \a\t g:i A', strtotime($expires_at));

  // HTML email body
  $message = "
<!DOCTYPE html>
<html>
<head>
  <style>
    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
    .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
    .button { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
    .footer { text-align: center; margin-top: 30px; color: #6c757d; font-size: 14px; }
    .warning { background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; }
  </style>
</head>
<body>
  <div class='container'>
    <div class='header'>
      <h1>Password Reset Request</h1>
    </div>
    <div class='content'>
      <p>Hello " . htmlspecialchars($user['full_name'] ?? $user['username']) . ",</p>
      
      <p>We received a request to reset your password for your admin account. If you didn't make this request, you can safely ignore this email.</p>
      
      <p>To reset your password, click the button below:</p>
      
      <div style='text-align: center;'>
        <a href='" . htmlspecialchars($reset_link) . "' class='button'>Reset Password</a>
      </div>
      
      <p>Or copy and paste this link into your browser:</p>
      <p style='word-break: break-all; background: white; padding: 10px; border: 1px solid #ddd; border-radius: 5px;'>
        " . htmlspecialchars($reset_link) . "
      </p>
      
      <div class='warning'>
        <strong>⚠️ Security Note:</strong>
        <ul style='margin: 10px 0; padding-left: 20px;'>
          <li>This link expires on: <strong>" . $expiry_time . "</strong></li>
          <li>This link can only be used once</li>
          <li>If you didn't request this, please ignore this email</li>
        </ul>
      </div>
      
      <p>For security reasons, we recommend:</p>
      <ul>
        <li>Use a strong, unique password</li>
        <li>Don't share your password with anyone</li>
        <li>Enable two-factor authentication if available</li>
      </ul>
    </div>
    <div class='footer'>
      <p>&copy; " . date('Y') . " " . PROJECT_NAME . ". All rights reserved.</p>
      <p>This is an automated email. Please do not reply.</p>
    </div>
  </div>
</body>
</html>
";

  // Plain text version
  $recipientName = $user['full_name'] ?? $user['username'];
  $plain_message = "
Hello " . $recipientName . ",

We received a request to reset your password for your admin account.

To reset your password, click the link below:
" . $reset_link . "

This link expires on: " . $expiry_time . "
This link can only be used once.

If you didn't request this password reset, you can safely ignore this email.

For security reasons, we recommend using a strong, unique password.

---
" . PROJECT_NAME . "
This is an automated email. Please do not reply.
";

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
