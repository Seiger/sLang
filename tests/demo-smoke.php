<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$demoCoreArgument = null;
$url = null;

foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '--url=')) {
        $url = rtrim(substr($argument, 6), '/');
        continue;
    }

    $demoCoreArgument = $argument;
}

$demoCore = realpath($demoCoreArgument ?? $root . '/../sArticles/demo/core');

if (!is_string($demoCore) || !is_file($demoCore . '/artisan')) {
    fwrite(STDERR, "Demo core not found. Pass /path/to/sArticles/demo/core as first argument.\n");
    exit(1);
}

run('Package discovery', PHP_BINARY . ' ' . escapeshellarg($demoCore . '/artisan') . ' package:discover');
run('Clear Blade views', PHP_BINARY . ' ' . escapeshellarg($demoCore . '/artisan') . ' view:clear');
run('Run migrations', PHP_BINARY . ' ' . escapeshellarg($demoCore . '/artisan') . ' migrate --force');
smokeAssertAutoload($demoCore);
smokeAssertDatabase($demoCore);
smokeAssertDictionaryCrud($demoCore);

if ($url !== null) {
    smokeHttp($demoCore, $url);
    smokeSettingsHttp($demoCore, $url);
    smokeResourceEdit($demoCore, $url);
}

echo "sLang demo smoke OK.\n";

function run(string $label, string $command): void
{
    $output = [];
    $code = 0;
    exec($command . ' 2>&1', $output, $code);

    if ($code !== 0) {
        fwrite(STDERR, "{$label} failed:\n" . implode("\n", $output) . "\n");
        exit($code);
    }

    echo "{$label}: OK\n";
}

function smokeAssertAutoload(string $demoCore): void
{
    require $demoCore . '/vendor/autoload.php';

    foreach ([
        \Seiger\sLang\sLangServiceProvider::class,
        \Seiger\sLang\Livewire\ModulePanel::class,
        \Seiger\sLang\Livewire\SettingsPanel::class,
        \Seiger\sLang\Tables\TranslatesTableData::class,
        \Seiger\sLang\Support\LanguageBridge::class,
    ] as $class) {
        if (!class_exists($class)) {
            fwrite(STDERR, "Autoload failed for {$class}\n");
            exit(1);
        }
    }

    echo "Autoload classes: OK\n";
}

function smokeAssertDatabase(string $demoCore): void
{
    $database = $demoCore . '/database/database.sqlite';

    if (!is_file($database)) {
        fwrite(STDERR, "Demo database not found: {$database}\n");
        exit(1);
    }

    $pdo = new PDO('sqlite:' . $database);
    $prefix = tableExists($pdo, 'evo_s_lang_translates') ? 'evo_' : '';

    foreach (['s_lang_translates', 's_lang_content'] as $table) {
        if (!tableExists($pdo, $prefix . $table)) {
            fwrite(STDERR, "Required table missing: {$prefix}{$table}\n");
            exit(1);
        }
    }

    echo "sLang database tables: OK\n";
}

function smokeAssertDictionaryCrud(string $demoCore): void
{
    $database = $demoCore . '/database/database.sqlite';
    $pdo = new PDO('sqlite:' . $database);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $prefix = tableExists($pdo, 'evo_s_lang_translates') ? 'evo_' : '';
    $table = $prefix . 's_lang_translates';
    $columns = smokeTableColumns($pdo, $table);

    foreach (['key', 'uk', 'en'] as $column) {
        if (!in_array($column, $columns, true)) {
            fwrite(STDERR, "Dictionary CRUD smoke failed: missing {$column} column\n");
            exit(1);
        }
    }

    $countStatement = $pdo->query("select count(*) from {$table}");
    $rows = $countStatement instanceof PDOStatement ? (int) $countStatement->fetchColumn() : 0;
    if ($rows < 3) {
        fwrite(STDERR, "Dictionary CRUD smoke failed: expected seeded demo rows\n");
        exit(1);
    }

    $key = 'smoke.translation.' . date('YmdHis');
    $insert = $pdo->prepare("insert into {$table} (\"key\", \"uk\", \"en\") values (:key, :uk, :en)");
    $insert->execute([
        'key' => $key,
        'uk' => 'Smoke UA',
        'en' => 'Smoke EN',
    ]);

    $id = (int) $pdo->lastInsertId();
    $update = $pdo->prepare("update {$table} set \"uk\" = :uk where \"tid\" = :id");
    $update->execute([
        'uk' => 'Smoke UA updated',
        'id' => $id,
    ]);

    $select = $pdo->prepare("select \"uk\" from {$table} where \"tid\" = :id");
    $select->execute(['id' => $id]);

    if ($select->fetchColumn() !== 'Smoke UA updated') {
        fwrite(STDERR, "Dictionary CRUD smoke failed: update was not persisted\n");
        exit(1);
    }

    $delete = $pdo->prepare("delete from {$table} where \"tid\" = :id");
    $delete->execute(['id' => $id]);

    echo "Dictionary CRUD smoke: OK\n";
}

function smokeHttp(string $demoCore, string $baseUrl): void
{
    $database = $demoCore . '/database/database.sqlite';
    $session = activeSession($database);
    $moduleId = md5('Мультимова');
    $url = $baseUrl . '/manager/index.php?a=112&id=' . $moduleId;
    $headers = ['Accept-Language: uk-UA,uk;q=0.9,en;q=0.8'];

    if ($session !== '') {
        $headers[] = 'Cookie: evo_session=' . $session;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", $headers),
            'ignore_errors' => true,
            'timeout' => 10,
        ],
    ]);
    $html = @file_get_contents($url, false, $context);

    if (!is_string($html) || $html === '') {
        fwrite(STDERR, "HTTP smoke failed: empty response from {$url}\n");
        exit(1);
    }

    foreach (['Evolution CMS Parse Error', 'SQLSTATE', 'No record found', 'Fatal error'] as $marker) {
        if (str_contains($html, $marker)) {
            fwrite(STDERR, "HTTP smoke failed: found {$marker}\n");
            exit(1);
        }
    }

    foreach (['data-evo-ui-root', 'slang.module-panel', 'slang-translates', 'evo-ui-table-toolbar', 'runTableAction', 'synchronize'] as $marker) {
        if (!str_contains($html, $marker)) {
            fwrite(STDERR, "HTTP smoke failed: missing {$marker}\n");
            exit(1);
        }
    }

    echo "HTTP manager render: OK\n";
}

function smokeSettingsHttp(string $demoCore, string $baseUrl): void
{
    $database = $demoCore . '/database/database.sqlite';
    $session = activeSession($database);
    $moduleId = md5('Мультимова');
    $url = $baseUrl . '/manager/index.php?a=112&id=' . $moduleId . '&get=settings';
    $headers = ['Accept-Language: uk-UA,uk;q=0.9,en;q=0.8'];

    if ($session !== '') {
        $headers[] = 'Cookie: evo_session=' . $session;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", $headers),
            'ignore_errors' => true,
            'timeout' => 10,
        ],
    ]);
    $html = @file_get_contents($url, false, $context);

    if (!is_string($html) || $html === '') {
        fwrite(STDERR, "HTTP settings smoke failed: empty response from {$url}\n");
        exit(1);
    }

    foreach (['Evolution CMS Parse Error', 'SQLSTATE', 'No record found', 'Fatal error'] as $marker) {
        if (str_contains($html, $marker)) {
            fwrite(STDERR, "HTTP settings smoke failed: found {$marker}\n");
            exit(1);
        }
    }

    foreach (['{{ $', '@if($', '@empty', '@endforelse', '@foreach'] as $marker) {
        if (str_contains($html, $marker)) {
            fwrite(STDERR, "HTTP settings smoke failed: rendered raw Blade token {$marker}\n");
            exit(1);
        }
    }

    foreach (['evo-ui-form-surface--layout-settings', 'evo-ui-choices', 'data-evo-form-dirty'] as $marker) {
        if (!str_contains($html, $marker)) {
            fwrite(STDERR, "HTTP settings smoke failed: missing {$marker}\n");
            exit(1);
        }
    }

    echo "HTTP settings render: OK\n";
}

function smokeResourceEdit(string $demoCore, string $baseUrl): void
{
    $database = $demoCore . '/database/database.sqlite';
    $session = activeSession($database);
    $url = $baseUrl . '/manager/index.php?a=27&id=1';
    $headers = ['Accept-Language: uk-UA,uk;q=0.9,en;q=0.8'];

    if ($session !== '') {
        $headers[] = 'Cookie: evo_session=' . $session;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", $headers),
            'ignore_errors' => true,
            'timeout' => 10,
        ],
    ]);
    $html = @file_get_contents($url, false, $context);

    if (!is_string($html) || $html === '') {
        fwrite(STDERR, "Resource edit smoke failed: empty response from {$url}\n");
        exit(1);
    }

    foreach (['Evolution CMS Parse Error', 'SQLSTATE', 'No record found', 'Fatal error'] as $marker) {
        if (str_contains($html, $marker)) {
            fwrite(STDERR, "Resource edit smoke failed: found {$marker}\n");
            exit(1);
        }
    }

    foreach (['data-slang-default-content', 'tabGeneral_', 'data-slang-translate', 'translate-only', 'tabSettings', 'slang-resource-tab-page', 'slang-lang-badge'] as $marker) {
        if (!str_contains($html, $marker)) {
            fwrite(STDERR, "Resource edit smoke failed: missing {$marker}\n");
            exit(1);
        }
    }

    foreach (['<style>' . "\n" . '    input[type=checkbox]', 'form#mutate input[name="menuindex"]', "\n" . '    .form-row .row-col', "\n" . '    .badge.bg-seigerit'] as $marker) {
        if (str_contains($html, $marker)) {
            fwrite(STDERR, "Resource edit smoke failed: found unscoped resource CSS {$marker}\n");
            exit(1);
        }
    }

    foreach (['assets/modules/evo-ui/evo-ui.css', 'assets/modules/evo-ui/evo-ui.js'] as $marker) {
        if (str_contains($html, $marker)) {
            fwrite(STDERR, "Resource edit smoke failed: resource editor must not load full evo-ui asset {$marker}\n");
            exit(1);
        }
    }

    echo "HTTP resource edit tabs: OK\n";
}

function activeSession(string $database): string
{
    $pdo = new PDO('sqlite:' . $database);
    $table = tableExists($pdo, 'evo_active_user_sessions') ? 'evo_active_user_sessions' : 'active_user_sessions';

    if (!tableExists($pdo, $table)) {
        return '';
    }

    $statement = $pdo->query("select sid from {$table} order by lasthit desc limit 1");

    return $statement instanceof PDOStatement ? (string) ($statement->fetchColumn() ?: '') : '';
}

function tableExists(PDO $pdo, string $table): bool
{
    $statement = $pdo->prepare("select name from sqlite_master where type = 'table' and name = :table");
    $statement->execute(['table' => $table]);

    return (bool) $statement->fetchColumn();
}

/**
 * @return array<int, string>
 */
function smokeTableColumns(PDO $pdo, string $table): array
{
    $statement = $pdo->query("pragma table_info({$table})");

    if (!$statement instanceof PDOStatement) {
        return [];
    }

    return array_map(
        static fn (array $row): string => (string) $row['name'],
        $statement->fetchAll(PDO::FETCH_ASSOC)
    );
}
