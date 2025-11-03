<?php
// Minimal admin DB health check
// Outputs JSON with connection status for troubleshooting on localhost and cPanel

header('Content-Type: application/json');

try {
	require_once __DIR__ . '/../vendor/autoload.php';
	require_once __DIR__ . '/../config/session.php';
	require_once __DIR__ . '/../config/database.php';

	$resp = [
		'ok' => false,
		'dbType' => null,
		'usingFileStorage' => null,
		'error' => null,
		'metrics' => []
	];

	$db = \App\Config\Database::getInstance();
	$resp['dbType'] = $db->getDbType();
	$resp['usingFileStorage'] = $db->isUsingFileStorage();

	if ($resp['dbType'] === 'mysql' && !$resp['usingFileStorage']) {
		// Try a cheap query
		try {
			$users = $db->getCollection('users');
			$count = $users->countDocuments();
			$resp['ok'] = true;
			$resp['metrics']['usersCount'] = $count;
		} catch (\Throwable $t) {
			$resp['error'] = 'Query failed: ' . $t->getMessage();
		}
	} else {
		$resp['error'] = $resp['usingFileStorage'] ? 'Using file storage fallback' : 'Non-MySQL dbType';
	}

	// Helpful env echo (non-sensitive)
	$resp['env'] = [
		'host' => $_ENV['MYSQL_HOST'] ?? null,
		'port' => $_ENV['MYSQL_PORT'] ?? null,
		'database' => $_ENV['MYSQL_DATABASE'] ?? null,
		'username' => $_ENV['MYSQL_USERNAME'] ?? null,
		'charset' => $_ENV['MYSQL_CHARSET'] ?? null,
		'persistent' => $_ENV['MYSQL_PERSISTENT'] ?? null,
		'ssl_enabled' => $_ENV['MYSQL_SSL_ENABLED'] ?? null
	];

	echo json_encode($resp, JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
	echo json_encode([
		'ok' => false,
		'error' => $e->getMessage()
	], JSON_PRETTY_PRINT);
}
