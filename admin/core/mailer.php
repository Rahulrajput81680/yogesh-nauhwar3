<?php

if (!defined('ADMIN_INIT')) {
  die('Direct access not permitted');
}



/**
 * Send an email.
 *
 * @param string $to         Recipient address
 * @param string $subject    Subject line
 * @param string $htmlBody   HTML body
 * @param string $plainBody  Optional plain-text body (auto-stripped from HTML if empty)
 * @return bool
 */
function send_email(string $to, string $subject, string $htmlBody, string $plainBody = ''): bool
{
  if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    error_log('send_email aborted: invalid recipient email [' . $to . ']');
    return false;
  }

  $readSetting = static function (string $key, ?string $default = null): ?string {
    if (function_exists('get_admin_setting')) {
      try {
        return get_admin_setting($key, $default);
      } catch (Throwable $e) {
        return $default;
      }
    }
    return $default;
  };

  if (!$plainBody) {
    $plainBody = trim(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $htmlBody)));
  }

  $smtpHost = trim((string) $readSetting('smtp_host', defined('MAIL_SMTP_HOST') ? MAIL_SMTP_HOST : ''));
  $smtpPort = (int) $readSetting('smtp_port', (string) (defined('MAIL_SMTP_PORT') ? (int) MAIL_SMTP_PORT : 587));
  $smtpUser = trim((string) $readSetting('smtp_user', defined('MAIL_SMTP_USER') ? MAIL_SMTP_USER : ''));
  $smtpPass = (string) $readSetting('smtp_pass', defined('MAIL_SMTP_PASS') ? MAIL_SMTP_PASS : '');
  $smtpEncryption = strtolower(trim((string) $readSetting('smtp_encryption', 'tls')));

  $defaultFromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : (defined('PROJECT_NAME') ? PROJECT_NAME : 'Website');
  $fromName = trim((string) $readSetting('smtp_from_name', $defaultFromName));
  if ($fromName === '') {
    $fromName = $defaultFromName;
  }

  $defaultFromAddress = defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : '';
  if (!filter_var($defaultFromAddress, FILTER_VALIDATE_EMAIL) && filter_var($smtpUser, FILTER_VALIDATE_EMAIL)) {
    $defaultFromAddress = $smtpUser;
  }
  if (!filter_var($defaultFromAddress, FILTER_VALIDATE_EMAIL)) {
    $defaultFromAddress = 'no-reply@example.com';
  }

  $fromAddress = trim((string) $readSetting('smtp_from_email', $defaultFromAddress));
  if (!filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
    $fromAddress = $defaultFromAddress;
  }

  if (!in_array($smtpEncryption, ['tls', 'ssl', 'none'], true)) {
    $smtpEncryption = 'tls';
  }

  if ($smtpHost !== '' && $smtpUser !== '' && $smtpPass !== '') {
    $mailer = new SimpleMailer(
      $smtpHost,
      $smtpPort > 0 ? $smtpPort : 587,
      $smtpUser,
      $smtpPass,
      $fromAddress,
      $fromName,
      $smtpEncryption
    );
    $sent = $mailer->send($to, $subject, $htmlBody, $plainBody);
    if (!$sent) {
      error_log('SimpleMailer errors: ' . implode(' | ', $mailer->getErrors()));
    }
    if ($sent) {
      return true;
    }
  }

  // Fallback: PHP native mail()
  $headers = implode("\r\n", [
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
    'From: ' . $fromName . ' <' . $fromAddress . '>',
    'Reply-To: ' . $fromAddress,
    'X-Mailer: PHP/' . phpversion(),
  ]);
  $sent = @mail($to, $subject, $htmlBody, $headers);
  if (!$sent) {
    error_log("PHP mail() failed sending to: {$to}");
  }
  return $sent;
}

// ─────────────────────────────────────────────────────────────────────────────
// SimpleMailer – lightweight SMTP client (STARTTLS + AUTH LOGIN)
// ─────────────────────────────────────────────────────────────────────────────

class SimpleMailer
{
  private string $host;
  private int $port;
  private string $username;
  private string $password;
  private string $fromEmail;
  private string $fromName;
  private string $encryption;

  /** @var resource|null */
  private $socket = null;

  private array $errors = [];

  public function __construct(string $host, int $port, string $username, string $password, string $fromEmail, string $fromName, string $encryption = 'tls')
  {
    $this->host = $host;
    $this->port = $port;
    $this->username = $username;
    $this->password = $password;
    $this->fromEmail = $fromEmail;
    $this->fromName = $fromName;
    $this->encryption = in_array($encryption, ['tls', 'ssl', 'none'], true) ? $encryption : 'tls';
  }

  // ── Public ────────────────────────────────────────────────────────────────

  public function send(string $to, string $subject, string $htmlBody, string $plainBody = ''): bool
  {
    $this->errors = [];

    try {
      if (!$this->connect())
        return false;
      if (!$this->authenticate())
        return false;

      if (!$this->cmd("MAIL FROM: <{$this->fromEmail}>", 250))
        return false;
      if (!$this->cmd("RCPT TO: <{$to}>", 250))
        return false;
      if (!$this->cmd("DATA", 354))
        return false;

      $message = $this->buildMessage($to, $this->fromName, $this->fromEmail, $subject, $htmlBody, $plainBody);
      $this->write($message . "\r\n.");

      $resp = $this->read();
      if ((int) substr($resp, 0, 3) !== 250) {
        $this->errors[] = "Message rejected: {$resp}";
        return false;
      }

      $this->cmd("QUIT", 221);
      fclose($this->socket);
      return true;

    } catch (\Throwable $e) {
      $this->errors[] = $e->getMessage();
      if ($this->socket)
        @fclose($this->socket);
      return false;
    }
  }

  public function getErrors(): array
  {
    return $this->errors;
  }

  // ── Private helpers ───────────────────────────────────────────────────────

  private function connect(): bool
  {
    $wrapper = ($this->encryption === 'ssl' || $this->port === 465) ? 'ssl' : 'tcp';

    $ctx = stream_context_create([
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
      ],
    ]);

    $this->socket = @stream_socket_client(
      "{$wrapper}://{$this->host}:{$this->port}",
      $errno,
      $errstr,
      30,
      STREAM_CLIENT_CONNECT,
      $ctx
    );

    if (!$this->socket) {
      $this->errors[] = "Cannot connect to {$this->host}:{$this->port} – {$errstr} ({$errno}).\n"
        . "Check MAIL_SMTP_HOST / MAIL_SMTP_PORT in config.php.";
      return false;
    }

    stream_set_timeout($this->socket, 30);

    $greeting = $this->read();
    if ((int) substr($greeting, 0, 3) !== 220) {
      $this->errors[] = "Unexpected greeting: {$greeting}";
      return false;
    }

    // EHLO
    if (!$this->cmd('EHLO ' . (gethostname() ?: 'localhost'), 250))
      return false;

    if ($this->encryption === 'tls') {
      if (!$this->cmd('STARTTLS', 220))
        return false;

      if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT)) {
        if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
          $this->errors[] = 'TLS handshake failed. '
            . 'Make sure your PHP has openssl enabled and the server supports TLS.';
          return false;
        }
      }
      if (!$this->cmd('EHLO ' . (gethostname() ?: 'localhost'), 250))
        return false;
    }

    return true;
  }

  private function authenticate(): bool
  {
    if ($this->username === '' || $this->password === '') {
      return true;
    }

    if (!$this->cmd('AUTH LOGIN', 334))
      return false;
    if (!$this->cmd(base64_encode($this->username), 334)) {
      $this->errors[] = 'SMTP username rejected. Check MAIL_SMTP_USER in config.php.';
      return false;
    }
    if (!$this->cmd(base64_encode($this->password), 235)) {
      $this->errors[] = 'SMTP password rejected. Check MAIL_SMTP_PASS in config.php. '
        . 'For Gmail use an App Password, not your regular password.';
      return false;
    }
    return true;
  }

  private function buildMessage(
    string $to,
    string $fromName,
    string $fromEmail,
    string $subject,
    string $html,
    string $plain
  ): string {
    $boundary = '==mp_' . md5(microtime(true));

    $headers = implode("\r\n", [
      "From: {$fromName} <{$fromEmail}>",
      "To: {$to}",
      "Subject: {$subject}",
      "MIME-Version: 1.0",
      "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
      "Date: " . date('r'),
      "X-Mailer: SharedAdminPanel/1.0",
    ]);

    $body = "--{$boundary}\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= chunk_split(base64_encode($plain)) . "\r\n";

    $body .= "--{$boundary}\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= chunk_split(base64_encode($html)) . "\r\n";

    $body .= "--{$boundary}--";

    return $headers . "\r\n\r\n" . $body;
  }

  /** Send a command and check the expected response code. */
  private function cmd(string $command, int $expectedCode): bool
  {
    $this->write($command);
    $response = $this->read();
    $code = (int) substr($response, 0, 3);

    if ($code !== $expectedCode) {
      $this->errors[] = "CMD [{$command}] expected {$expectedCode}, got: " . trim($response);
      return false;
    }
    return true;
  }

  private function write(string $data): void
  {
    fwrite($this->socket, $data . "\r\n");
  }

  private function read(): string
  {
    $response = '';
    while ($line = fgets($this->socket, 1024)) {
      $response .= $line;
      if (isset($line[3]) && $line[3] === ' ') {
        break;
      }
    }
    return $response;
  }
}
