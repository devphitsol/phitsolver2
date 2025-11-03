<?php
/**
 * Simple MongoDB migration runner
 * Usage:
 *  php bin/migrate.php up
 *  php bin/migrate.php down
 *  php bin/migrate.php status
 *  php bin/migrate.php make CreateProductsIndexes
 */

declare(strict_types=1);

// Add custom PHP include path
ini_set("include_path", '/home/qiimy7odbu3s/php:' . ini_get("include_path"));

require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;

$command = $argv[1] ?? 'status';
$argName = $argv[2] ?? '';

$rootDir = realpath(__DIR__ . '/..');
$migrationsDir = $rootDir . DIRECTORY_SEPARATOR . 'migrations';
if (!is_dir($migrationsDir)) {
    mkdir($migrationsDir, 0755, true);
}

$dbManager = Database::getInstance();
$db = $dbManager->getDatabase();
$migrationsCollection = $dbManager->getCollection('migrations');

function listMigrationFiles(string $dir): array {
    $files = glob($dir . DIRECTORY_SEPARATOR . '*.php') ?: [];
    sort($files, SORT_STRING);
    return $files;
}

function loadAppliedMigrations($migrationsCollection): array {
    $applied = [];
    foreach ($migrationsCollection->find([], ['sort' => ['applied_at' => 1]])->toArray() as $doc) {
        $applied[(string)($doc['filename'] ?? '')] = $doc;
    }
    return $applied;
}

function nowBsonOrIso() {
    $utcDateTimeClass = '\\MongoDB\\BSON\\UTCDateTime';
    if (class_exists($utcDateTimeClass)) {
        return new $utcDateTimeClass();
    }
    return date('c');
}

switch ($command) {
    case 'status':
        $files = listMigrationFiles($migrationsDir);
        $applied = loadAppliedMigrations($migrationsCollection);
        echo "Migrations status (" . count($files) . ")\n";
        foreach ($files as $file) {
            $name = basename($file);
            $isApplied = isset($applied[$name]);
            echo sprintf("%s %s\n", $isApplied ? '[x]' : '[ ]', $name);
        }
        exit(0);

    case 'up':
        $files = listMigrationFiles($migrationsDir);
        $applied = loadAppliedMigrations($migrationsCollection);
        $appliedCount = 0;
        foreach ($files as $file) {
            $name = basename($file);
            if (isset($applied[$name])) {
                continue; // already applied
            }
            /** @var object $migration */
            $migration = include $file;
            if (!is_object($migration) || !method_exists($migration, 'up')) {
                fwrite(STDERR, "Invalid migration file: {$name}\n");
                exit(1);
            }
            $migration->up($db);
            $migrationsCollection->insertOne([
                'filename' => $name,
                'description' => property_exists($migration, 'description') ? $migration->description : null,
                'applied_at' => nowBsonOrIso(),
            ]);
            echo "Applied: {$name}\n";
            $appliedCount++;
        }
        if ($appliedCount === 0) {
            echo "No pending migrations.\n";
        }
        exit(0);

    case 'down':
        $appliedDocs = $migrationsCollection->find([], ['sort' => ['applied_at' => -1]])->toArray();
        if (empty($appliedDocs)) {
            echo "No applied migrations to roll back.\n";
            exit(0);
        }
        $last = $appliedDocs[0];
        $name = (string)($last['filename'] ?? '');
        $file = $migrationsDir . DIRECTORY_SEPARATOR . $name;
        if (!is_file($file)) {
            fwrite(STDERR, "Migration file missing: {$name}\n");
            exit(1);
        }
        /** @var object $migration */
        $migration = include $file;
        if (is_object($migration) && method_exists($migration, 'down')) {
            $migration->down($db);
        }
        $migrationsCollection->deleteOne(['filename' => $name]);
        echo "Rolled back: {$name}\n";
        exit(0);

    case 'make':
        if ($argName === '') {
            fwrite(STDERR, "Usage: php bin/migrate.php make DescriptiveName\n");
            exit(1);
        }
        $ts = date('Ymd_His');
        $safe = preg_replace('/[^A-Za-z0-9_\-]/', '', $argName);
        $filename = $migrationsDir . DIRECTORY_SEPARATOR . "{$ts}_" . strtolower($safe) . '.php';
        $template = <<<'PHP'
<?php
// Migration: describe purpose in $description and implement up/down
return new class {
    public string $description = 'Describe this migration';

    public function up($db): void
    {
        // $db is either MongoDB\Database or App\Config\FileDatabase (fallback). Handle accordingly.
        if ($db instanceof App\Config\FileDatabase) {
            // Skipping index creation in file fallback
            return;
        }
        // Example: create an index
        // $db->selectCollection('your_collection')->createIndex(['field' => 1]);
    }

    public function down($db): void
    {
        if ($db instanceof App\Config\FileDatabase) {
            return;
        }
        // Example: drop an index by name
        // $db->selectCollection('your_collection')->dropIndex('field_1');
    }
};
PHP;
        file_put_contents($filename, $template);
        echo "Created: " . basename($filename) . "\n";
        exit(0);
}

fwrite(STDERR, "Unknown command: {$command}\n");
exit(1);


