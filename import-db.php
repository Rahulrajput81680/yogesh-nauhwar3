<?php
/**
 * Auto-import database schema.
 * Open this file in browser to create DB + import schema.sql.
 */

header('Content-Type: text/html; charset=utf-8');

$dbHost = 'localhost';
$dbName = 'shared-admin-panel';
$dbUser = 'root';
$dbPass = 'root';
$schemaPath = __DIR__ . '/database/schema.sql';

$messages = [];
$success = true;

try {
  if (!file_exists($schemaPath)) {
    throw new RuntimeException('Schema file not found: ' . $schemaPath);
  }

  $schemaSql = file_get_contents($schemaPath);
  if ($schemaSql === false) {
    throw new RuntimeException('Unable to read schema file.');
  }

  $serverPdo = new PDO(
    'mysql:host=' . $dbHost . ';charset=utf8mb4',
    $dbUser,
    $dbPass,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ]
  );

  $serverPdo->exec('CREATE DATABASE IF NOT EXISTS `' . $dbName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
  $messages[] = 'Database created/verified: ' . $dbName;

  $pdo = new PDO(
    'mysql:host=' . $dbHost . ';dbname=' . $dbName . ';charset=utf8mb4',
    $dbUser,
    $dbPass,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
      PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]
  );

  // Execute full SQL dump directly. This avoids breaking valid SQL that contains semicolons in data.
  try {
    $pdo->exec($schemaSql);
  } catch (Throwable $directImportError) {
    // Fallback for clients that reject some versioned comments; normalize and retry once.
    $normalizedSql = preg_replace('/\/\*![0-9]+\s(.*?)\*\//s', '$1', $schemaSql);
    if (!is_string($normalizedSql)) {
      throw $directImportError;
    }

    $pdo->exec($normalizedSql);
  }

  $messages[] = 'Schema imported successfully from database/schema.sql';
} catch (Throwable $e) {
  $success = false;
  $messages[] = 'Import failed: ' . $e->getMessage();
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Database Import</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f7fb;
      padding: 24px;
    }

    .card {
      max-width: 760px;
      margin: 0 auto;
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 8px 24px rgba(16, 24, 40, .08);
    }

    .ok {
      color: #067647;
    }

    .err {
      color: #b42318;
    }

    li {
      margin-bottom: 8px;
    }
  </style>
</head>

<body>
  <div class="card">
    <h2 class="<?php echo $success ? 'ok' : 'err'; ?>">
      <?php echo $success ? 'Database Imported Successfully' : 'Database Import Failed'; ?>
    </h2>
    <ul>
      <?php foreach ($messages as $message): ?>
        <li><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</body>

</html>