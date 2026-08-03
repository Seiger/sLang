<?php

declare(strict_types=1);

use EvolutionCMS\Console;
use Illuminate\Validation\ValidationException;
use Seiger\sLang\Controllers\sLangController;
use Seiger\sLang\Facades\sLang;
use Seiger\sLang\Livewire\SettingsPanel;
use Seiger\sLang\Models\sLangTranslate;
use Seiger\sLang\Tables\TranslatesTableData;

$root = dirname(__DIR__, 2);
$demoCoreArgument = $argv[1] ?? null;
$demoCore = realpath($demoCoreArgument ?? $root . '/../sArticles/demo/core');

if (!is_string($demoCore) || !is_file($demoCore . '/bootstrap.php')) {
    fail("Demo core not found. Pass /path/to/sArticles/demo/core as first argument.");
}

bootstrapDemo($demoCore);

$pdo = database($demoCore);
$table = tableExists($pdo, 'evo_s_lang_translates') ? 'evo_s_lang_translates' : 's_lang_translates';
$createdId = null;

class SlangRegressionFakeController extends sLangController
{
    /**
     * @var array<int, array{0: int, 1: string}>
     */
    public static array $calls = [];

    public function setModifyTables()
    {
        return true;
    }

    public function setAutomaticTranslate($source, $target): string
    {
        $translate = sLangTranslate::query()->find((int) $source);

        if (!$translate) {
            return '';
        }

        $result = 'Bulk ' . (int) $source . ' ' . (string) $target;
        $translate->{$target} = $result;
        $translate->save();
        self::$calls[] = [(int) $source, (string) $target];

        return $result;
    }
}

class SlangRegressionBulkTableData extends TranslatesTableData
{
    protected function controller(): sLangController
    {
        return new SlangRegressionFakeController();
    }
}

class SlangRegressionCleanupController extends sLangController
{
    /**
     * @param array<int, string> $discoveredKeys
     */
    public function __construct(protected array $discoveredKeys)
    {
    }

    /**
     * @return array<int, string>
     */
    public function discoveredTranslationKeys(): array
    {
        return $this->discoveredKeys;
    }

    public function setModifyTables()
    {
        return true;
    }

    protected function updateLangFiles(): void
    {
    }
}

try {
    assertAutoload();
    assertTableConfig($root);
    cleanupGeneratedRows($pdo, $table);
    assertDictionarySchemaAndSeed($pdo, $table);
    $createdId = assertDictionaryCrud();
    assertDictionaryDelete();
    assertBulkAutoTranslateEmptyColumn();
    assertSettingsPanel();
    assertObsoleteCleanup();
    assertChoicesRenderCleanHtml();
} finally {
    if ($createdId !== null) {
        sLangTranslate::query()->whereKey($createdId)->delete();
    }

    cleanupGeneratedRows($pdo, $table);
}

pass('sLang demo regression OK');

function bootstrapDemo(string $demoCore): void
{
    if (!defined('EVO_API_MODE')) {
        define('EVO_API_MODE', true);
    }

    if (!defined('IN_MANAGER_MODE')) {
        define('IN_MANAGER_MODE', true);
    }

    require $demoCore . '/bootstrap.php';

    if (!defined('IN_INSTALL_MODE')) {
        define('IN_INSTALL_MODE', false);
    }

    new Console(evo(), app('events'), evo()->version());
}

function assertAutoload(): void
{
    foreach ([
        SettingsPanel::class,
        TranslatesTableData::class,
        sLangTranslate::class,
    ] as $class) {
        assertTrue(class_exists($class), "Class is not autoloadable: {$class}");
    }

    pass('Autoload regression: OK');
}

function assertTableConfig(string $root): void
{
    $config = require $root . '/config/translates/table.php';
    assertSame('sm', (string) ($config['search']['width'] ?? ''), 'Dictionary search width must stay compact.');
    assertSame(10, (int) ($config['per_page'] ?? 0), 'Dictionary per-page default must be 10.');
    assertSame([5, 10, 20, 30, 50, 100], array_values((array) ($config['per_page_options'] ?? [])), 'Dictionary per-page options must use the standard set.');
    assertTrue(str_contains((string) ($config['wire_target'] ?? ''), 'runInlineFieldAction'), 'Dictionary table must allow inline auto-translate actions.');
    assertTrue(str_contains((string) ($config['wire_target'] ?? ''), 'runHeaderAction'), 'Dictionary table must allow column header actions.');
    assertTrue(str_contains((string) ($config['wire_target'] ?? ''), 'runTableAction'), 'Dictionary table must allow toolbar provider actions.');
    assertTrue(str_contains((string) ($config['wire_target'] ?? ''), 'openCreateModal'), 'Dictionary table must expose create modal action.');
    assertTrue(str_contains((string) ($config['wire_target'] ?? ''), 'saveModal'), 'Dictionary table must expose modal save action.');
    assertTrue(!str_contains((string) ($config['wire_target'] ?? ''), 'createInlineRow'), 'Dictionary plus button must not create random inline rows.');
    assertTrue(str_contains((string) ($config['wire_target'] ?? ''), 'openDeleteModal'), 'Dictionary table must expose delete modal action.');
    assertTrue(str_contains((string) ($config['wire_target'] ?? ''), 'deleteConfirmed'), 'Dictionary table must expose delete confirmation action.');

    $actions = array_column((array) ($config['actions'] ?? []), 'key');
    assertTrue(in_array('synchronize', $actions, true), 'Dictionary toolbar must expose synchronize action.');
    assertTrue(in_array('delete', $actions, true), 'Dictionary toolbar must expose selected-row delete action.');
    $createAction = collect((array) ($config['actions'] ?? []))->firstWhere('key', 'create');
    assertSame('openCreateModal', (string) ($createAction['method'] ?? ''), 'Dictionary create action must open the evo-ui modal.');
    assertSame(true, (bool) ($config['modal']['enabled'] ?? false), 'Dictionary create modal must be enabled.');
    assertSame('saveModal', (string) ($config['modal']['save_provider'] ?? ''), 'Dictionary create modal must save through provider.');
    assertSame(false, (bool) ($config['modal']['row_dblclick'] ?? true), 'Dictionary create modal must not bind row double-click editing.');
    assertSame(false, (bool) ($config['columns'][0]['editable'] ?? true), 'Dictionary key column must not be editable online.');
    $syncAction = collect((array) ($config['actions'] ?? []))->firstWhere('key', 'synchronize');
    assertSame('wire', (string) ($syncAction['type'] ?? ''), 'Dictionary synchronize action must be Livewire-based.');
    assertSame('synchronizeTranslations', (string) ($syncAction['provider'] ?? ''), 'Dictionary synchronize action must call provider method.');
    assertSame('controls', (string) ($syncAction['placement'] ?? ''), 'Dictionary synchronize action must be placed near search controls.');

    $rowActions = array_column((array) ($config['row_actions'] ?? []), 'key');
    assertTrue(in_array('delete', $rowActions, true), 'Dictionary rows must expose delete action.');

    pass('Dictionary config regression: OK');
}

function assertDictionarySchemaAndSeed(PDO $pdo, string $table): void
{
    $columns = tableColumns($pdo, $table);

    foreach (['tid', 'key', 'uk', 'en'] as $column) {
        assertTrue(in_array($column, $columns, true), "Dictionary column is missing: {$column}");
    }

    $seededStatement = $pdo->query("select count(*) from {$table}");
    $seededRows = $seededStatement === false ? 0 : (int) $seededStatement->fetchColumn();
    assertTrue($seededRows >= 7, 'Dictionary must contain demo seed rows.');

    $generatedStatement = $pdo->query("select count(*) from {$table} where \"key\" like 'new.translation.%'");
    $generatedRows = $generatedStatement === false ? 0 : (int) $generatedStatement->fetchColumn();
    assertSame(0, $generatedRows, 'Generated QA rows must be cleaned up.');

    pass('Dictionary schema/seed regression: OK');
}

function assertDictionaryCrud(): int
{
    $table = new TranslatesTableData();
    $suffix = date('YmdHis');
    $key = 'regression.translation.create.' . $suffix;

    $modalData = ['key' => $key];
    foreach (sLang::langConfig() as $locale) {
        $modalData[$locale] = strtoupper($locale) . ' Regression ' . $suffix;
    }

    $id = $table->saveModal($modalData, null, 'create');
    assertTrue($id > 0, 'Dictionary saveModal did not return an id.');

    $columns = collect($table->columns((array) (require dirname(__DIR__, 2) . '/config/translates/table.php')['columns']));
    $defaultColumn = $columns->firstWhere('key', sLang::langDefault());
    $englishColumn = $columns->firstWhere('key', 'en');

    assertTrue(empty($defaultColumn['inline_actions'] ?? []), 'Default dictionary language must not show auto-translate action.');
    assertTrue(empty($defaultColumn['header_actions'] ?? []), 'Default dictionary language must not show bulk auto-translate action.');
    if (sLang::langDefault() !== 'en' && is_array($englishColumn)) {
        assertSame('auto_translate', (string) data_get($englishColumn, 'inline_actions.0.key'), 'Non-default dictionary language must show auto-translate action.');
        assertSame('autoTranslateInlineField', (string) data_get($englishColumn, 'inline_actions.0.provider'), 'Auto-translate action must use provider method.');
        assertSame('auto_translate_empty', (string) data_get($englishColumn, 'header_actions.0.key'), 'Non-default dictionary language must show bulk auto-translate action.');
        assertSame('autoTranslateEmptyColumn', (string) data_get($englishColumn, 'header_actions.0.provider'), 'Bulk auto-translate action must use provider method.');
    }

    $translate = sLangTranslate::find($id);
    if (!$translate instanceof sLangTranslate) {
        fail('Created dictionary row was not found.');
    }
    $originalKey = (string) $translate->key;
    assertSame($key, $originalKey, 'Dictionary create modal must persist the explicit key.');
    assertSame($originalKey, $table->updateInlineField($id, 'key', 'should.not.change.' . date('YmdHis')), 'Dictionary KEY inline update must be ignored.');
    $translate->refresh();
    assertSame($originalKey, (string) $translate->key, 'Dictionary KEY must remain unchanged after attempted inline edit.');

    foreach (sLang::langConfig() as $locale) {
        assertSame((string) $modalData[$locale], (string) $translate->{$locale}, strtoupper($locale) . ' modal create value was not persisted.');
    }

    $updatedDefault = 'Regression default edit ' . $suffix;
    $table->updateInlineField($id, sLang::langDefault(), $updatedDefault);
    $translate->refresh();
    assertSame($updatedDefault, (string) $translate->{sLang::langDefault()}, 'Default inline edit was not persisted after modal create.');
    assertSame((string) $translate->key, $table->deleteName($id), 'Delete modal must use translation key as record name.');

    $duplicateFailed = false;
    try {
        $table->saveModal($modalData, null, 'create');
    } catch (ValidationException) {
        $duplicateFailed = true;
    }
    assertTrue($duplicateFailed, 'Dictionary create modal must reject duplicate keys.');

    $emptyKeyFailed = false;
    try {
        $table->saveModal(['key' => ''], null, 'create');
    } catch (ValidationException) {
        $emptyKeyFailed = true;
    }
    assertTrue($emptyKeyFailed, 'Dictionary create modal must reject empty keys.');

    pass('Dictionary CRUD regression: OK');

    return $id;
}

function assertDictionaryDelete(): void
{
    $table = new TranslatesTableData();
    $suffix = date('YmdHis');
    $modalData = ['key' => 'delete.translation.' . $suffix];
    foreach (sLang::langConfig() as $locale) {
        $modalData[$locale] = strtoupper($locale) . ' Delete ' . $suffix;
    }
    $id = $table->saveModal($modalData, null, 'create');
    assertTrue($id > 0, 'Dictionary delete regression could not create a row.');

    $table->deleteRow($id);

    assertTrue(sLangTranslate::query()->find($id) === null, 'Dictionary deleteRow did not remove the row.');

    pass('Dictionary delete regression: OK');
}

function assertBulkAutoTranslateEmptyColumn(): void
{
    $default = sLang::langDefault();
    $target = collect(sLang::langConfig())->first(static fn (string $locale): bool => $locale !== $default);

    if (!is_string($target)) {
        pass('Bulk auto-translate regression: skipped, only default language configured');
        return;
    }

    SlangRegressionFakeController::$calls = [];
    $suffix = date('YmdHis');

    $candidateQuery = sLangTranslate::query();
    $candidateQuery->where(function (Illuminate\Database\Eloquent\Builder $query) use ($target) {
        $query->whereNull($target)->orWhere($target, '');
    });
    $candidateQuery->whereNotNull($default);
    $candidateQuery->where($default, '!=', '');
    $existingCandidates = $candidateQuery->pluck($target, 'tid')->all();

    foreach ($existingCandidates as $id => $value) {
        $translate = sLangTranslate::query()->find((int) $id);
        if (!$translate instanceof sLangTranslate) {
            continue;
        }
        $translate->{$target} = '__regression_existing__';
        $translate->save();
    }

    try {
        $filled = sLangTranslate::create(['key' => 'bulk.translation.filled.' . $suffix]);
        $filled->{$default} = 'Filled source';
        $filled->{$target} = 'Already translated';
        $filled->save();

        $empty = sLangTranslate::create(['key' => 'bulk.translation.empty.' . $suffix]);
        $empty->{$default} = 'Empty source';
        $empty->{$target} = '';
        $empty->save();

        $blankSource = sLangTranslate::create(['key' => 'bulk.translation.blank-source.' . $suffix]);
        $blankSource->{$default} = '';
        $blankSource->{$target} = '';
        $blankSource->save();

        $table = new SlangRegressionBulkTableData();
        $translated = $table->autoTranslateEmptyColumn($target);

        $filled->refresh();
        $empty->refresh();
        $blankSource->refresh();

        assertSame(1, $translated, 'Bulk auto-translate must only process empty target values with a non-empty source.');
        assertSame('Already translated', (string) $filled->{$target}, 'Bulk auto-translate must not overwrite filled target values.');
        assertSame('Bulk ' . $empty->getKey() . ' ' . $target, (string) $empty->{$target}, 'Bulk auto-translate did not persist translated empty value.');
        assertSame('', (string) $blankSource->{$target}, 'Bulk auto-translate must skip rows without source text.');
        assertSame([[(int) $empty->getKey(), $target]], SlangRegressionFakeController::$calls, 'Bulk auto-translate called unexpected rows.');
    } finally {
        foreach ($existingCandidates as $id => $value) {
            $translate = sLangTranslate::query()->find((int) $id);

            if ($translate) {
                $translate->{$target} = $value;
                $translate->save();
            }
        }
    }

    pass('Bulk auto-translate regression: OK');
}

function assertSettingsPanel(): void
{
    $panel = new SettingsPanel();
    $panel->mount();
    assertSame(false, $panel->dirty, 'Settings save button must start disabled because the form is clean.');

    $default = (string) $panel->data['s_lang_default'];
    assertTrue(in_array($default, $panel->data['s_lang_config'], true), 'Default language must be selected for site languages.');
    assertTrue(in_array($default, $panel->data['s_lang_front'], true), 'Default language must be selected for frontend languages.');

    $panel->removeChoice('s_lang_config', $default);
    assertTrue(in_array($default, $panel->data['s_lang_config'], true), 'Default language must not be removable from site languages.');

    $availableLocales = array_keys(sLang::langList());
    $alternateLocale = collect($availableLocales)->first(static fn (string $locale): bool => $locale !== $default);

    if (is_string($alternateLocale)) {
        $panel->data['s_lang_default'] = $alternateLocale;
        $panel->updatedData($alternateLocale, 's_lang_default');
        assertSame(true, $panel->dirty, 'Settings save button must become enabled after a settings change.');

        assertTrue(in_array($alternateLocale, $panel->data['s_lang_config'], true), 'New default language must be selected for site languages.');
        assertTrue(in_array($alternateLocale, $panel->data['s_lang_front'], true), 'New default language must be selected for frontend languages.');
        assertTrue(array_key_exists($alternateLocale, $panel->data['s_lang_url_map']), 'URL segment map must include the selected default language.');
    }

    $viewData = $panel->render()->getData();
    foreach (['frontendLanguages', 'selectedSiteLanguages', 'selectedFrontendLanguages', 'selectedTemplateVariables', 'templateVariableOptions'] as $key) {
        assertTrue(array_key_exists($key, $viewData), "Settings render data is missing: {$key}");
    }

    $panel = new SettingsPanel();
    $panel->mount();
    $default = (string) $panel->data['s_lang_default'];
    $excludedLocale = collect(array_keys(sLang::langList()))->first(static fn (string $locale): bool => $locale !== $default);

    if (is_string($excludedLocale)) {
        $panel->data['s_lang_config'] = [$default];
        $panel->data['s_lang_front'] = [$default, $excludedLocale];
        $panel->updatedData();

        assertTrue(!in_array($excludedLocale, $panel->data['s_lang_front'], true), 'Frontend languages must be limited to selected site languages.');

        $filteredViewData = $panel->render()->getData();
        $frontendOptions = array_map(
            static fn (array $option): string => (string) $option['value'],
            (array) ($filteredViewData['frontendLanguages'] ?? [])
        );

        assertSame([$default], $frontendOptions, 'Frontend language choices must only contain selected site languages.');
    }

    pass('Settings panel regression: OK');
}

function assertObsoleteCleanup(): void
{
    $default = sLang::langDefault();
    $suffix = date('YmdHis');
    $staleKey = 'cleanup.translation.stale.' . $suffix;
    $manualKey = 'new.translation.cleanup.' . $suffix;
    $editedKey = 'cleanup.translation.edited.' . $suffix;
    $currentKey = 'cleanup.translation.current.' . $suffix;

    $protectedDiscoveredKeys = [];
    foreach (sLangTranslate::query()->get() as $translate) {
        if (trim((string) ($translate->{$default} ?? '')) === (string) $translate->key) {
            $protectedDiscoveredKeys[] = (string) $translate->key;
        }
    }
    $protectedDiscoveredKeys[] = $currentKey;

    $rows = [];

    try {
        foreach ([
            $staleKey => $staleKey,
            $manualKey => $manualKey,
            $editedKey => 'Edited default text',
            $currentKey => $currentKey,
        ] as $key => $defaultValue) {
            $row = sLangTranslate::create(['key' => $key]);
            $row->{$default} = $defaultValue;
            $row->save();
            $rows[] = $row;
        }

        $controller = new SlangRegressionCleanupController($protectedDiscoveredKeys);
        $preview = $controller->obsoleteTranslationKeys();

        assertTrue(in_array($staleKey, $preview, true), 'Obsolete cleanup preview must include stale parser-managed key.');
        assertTrue(!in_array($manualKey, $preview, true), 'Obsolete cleanup preview must protect manual new.translation keys.');
        assertTrue(!in_array($editedKey, $preview, true), 'Obsolete cleanup preview must protect keys with edited default text.');
        assertTrue(!in_array($currentKey, $preview, true), 'Obsolete cleanup preview must keep discovered keys.');

        $deleted = $controller->cleanupObsoleteTranslations();
        assertSame(1, $deleted, 'Obsolete cleanup must delete only the stale parser-managed key.');
        assertTrue(sLangTranslate::query()->where('key', $staleKey)->doesntExist(), 'Stale parser-managed key was not deleted.');
        assertTrue(sLangTranslate::query()->where('key', $manualKey)->exists(), 'Manual new.translation key must not be deleted.');
        assertTrue(sLangTranslate::query()->where('key', $editedKey)->exists(), 'Edited default key must not be deleted.');
        assertTrue(sLangTranslate::query()->where('key', $currentKey)->exists(), 'Discovered key must not be deleted.');
    } finally {
        foreach ([$staleKey, $manualKey, $editedKey, $currentKey] as $key) {
            sLangTranslate::query()->where('key', $key)->delete();
        }
    }

    pass('Obsolete cleanup regression: OK');
}

function assertChoicesRenderCleanHtml(): void
{
    $view = view('evo::components.choices', [
        'field' => 's_lang_config',
        'options' => [
            ['value' => 'uk', 'label' => 'Українська (Українська)'],
            ['value' => 'en', 'label' => 'English (Англійська)'],
        ],
        'selectedOptions' => [
            ['value' => 'uk', 'label' => 'Українська (Українська)', 'locked' => true],
            ['value' => 'en', 'label' => 'English (Англійська)'],
        ],
        'selectedValues' => ['uk', 'en'],
        'placeholder' => 'Select languages',
        'searchPlaceholder' => 'Select languages',
    ]);

    if (!$view instanceof Illuminate\Contracts\View\View) {
        fail('Choices component did not return a renderable view.');
    }

    $html = $view->render();

    foreach (['{{', '@if', '@empty', '@endforelse', '@foreach', '@php'] as $token) {
        assertTrue(!str_contains($html, $token), "Choices component rendered raw Blade token: {$token}");
    }

    assertTrue(str_contains($html, 'Українська (Українська)'), 'Choices component did not render the locked selected language.');
    assertTrue(str_contains($html, 'English (Англійська)'), 'Choices component did not render the selected language.');
    assertTrue(str_contains($html, 'wire:click.stop="removeChoice'), 'Choices component must keep Livewire remove action for removable chips.');

    pass('Choices render regression: OK');
}

function database(string $demoCore): PDO
{
    $database = $demoCore . '/database/database.sqlite';
    assertTrue(is_file($database), "Demo database not found: {$database}");

    $pdo = new PDO('sqlite:' . $database);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
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
function tableColumns(PDO $pdo, string $table): array
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

function cleanupGeneratedRows(PDO $pdo, string $table): void
{
    $pdo->exec("delete from {$table} where \"key\" like 'new.translation.%'");
    $pdo->exec("delete from {$table} where \"key\" like 'smoke.translation.%'");
    $pdo->exec("delete from {$table} where \"key\" like 'regression.translation.%'");
    $pdo->exec("delete from {$table} where \"key\" like 'bulk.translation.%'");
    $pdo->exec("delete from {$table} where \"key\" like 'delete.translation.%'");
    $pdo->exec("delete from {$table} where \"key\" like 'cleanup.translation.%'");
    $pdo->exec("delete from {$table} where \"key\" like 'new.translation.cleanup.%'");
}

function assertSame(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fail($message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true) . '.');
    }
}

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        fail($message);
    }
}

function pass(string $message): void
{
    echo $message . PHP_EOL;
}

function fail(string $message): never
{
    fwrite(STDERR, 'sLang regression failed: ' . $message . PHP_EOL);
    exit(1);
}
