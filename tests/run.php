<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$passed = 0;
$failed = 0;
$currentGroup = 'general';

function slang_group(string $name, Closure $tests): void
{
    global $currentGroup;

    $previous = $currentGroup;
    $currentGroup = $name;
    echo "GROUP {$name}\n";
    $tests();
    $currentGroup = $previous;
}

function slang_test(string $name, Closure $test): void
{
    global $passed, $failed, $currentGroup;

    try {
        $test();
        $passed++;
        echo "PASS [{$currentGroup}] {$name}\n";
    } catch (Throwable $exception) {
        $failed++;
        echo "FAIL [{$currentGroup}] {$name}\n";
        echo '  ' . $exception->getMessage() . "\n";
    }
}

function slang_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function slang_assert_contains(string $needle, string $haystack, string $message): void
{
    slang_assert(str_contains($haystack, $needle), $message);
}

function slang_path(string $path): string
{
    global $root;

    return $root . '/' . ltrim($path, '/');
}

function slang_read(string $path): string
{
    $absolute = slang_path($path);
    slang_assert(is_file($absolute), 'Expected file to exist: ' . $path);

    return (string) file_get_contents($absolute);
}

function slang_config(string $path): array
{
    $absolute = slang_path($path);
    slang_assert(is_file($absolute), 'Expected config file to exist: ' . $path);

    $config = require $absolute;
    slang_assert(is_array($config), 'Config must return an array: ' . $path);

    return $config;
}

slang_group('package', function () use ($root): void {
    slang_test('composer declares evo-ui dependency and repeatable test script', function () use ($root): void {
        $composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);

        slang_assert(isset($composer['require']['evolution-cms/evo-ui']), 'sLang must require evolution-cms/evo-ui.');
        slang_assert(($composer['require']['evolution-cms/evo-ui'] ?? null) === '^1.0', 'sLang must pin evo-ui to the release line.');
        slang_assert(($composer['license'] ?? null) === 'GPL-3.0-or-later', 'Composer license must use a non-deprecated SPDX identifier.');
        slang_assert(($composer['scripts']['test'] ?? null) === 'php tests/run.php', 'Composer test script must run the repeatable compatibility suite.');
        slang_assert(($composer['extra']['laravel']['priority']['Seiger\\sLang\\sLangServiceProvider'] ?? null) === 20, 'sLang service provider priority must stay first for language routing.');
    });
});

slang_group('dictionary-table', function (): void {
    slang_test('dictionary table config uses evo-ui inline editing and header actions', function (): void {
        $table = slang_config('config/translates/table.php');

        slang_assert(($table['provider'] ?? null) === \Seiger\sLang\Tables\TranslatesTableData::class, 'Dictionary table must use TranslatesTableData provider.');
        slang_assert(($table['views'] ?? []) === ['table'], 'Dictionary table must stay table-only.');
        slang_assert(($table['inline']['create_provider'] ?? null) === 'createInlineRow', 'Dictionary table must create rows inline.');
        slang_assert(($table['inline']['save_provider'] ?? null) === 'updateInlineField', 'Dictionary table must save fields inline.');
        slang_assert(str_contains((string) ($table['wire_target'] ?? ''), 'runInlineFieldAction'), 'Dictionary table must expose inline actions.');
        slang_assert(str_contains((string) ($table['wire_target'] ?? ''), 'runHeaderAction'), 'Dictionary table must expose header actions.');
        slang_assert(str_contains((string) ($table['wire_target'] ?? ''), 'runTableAction'), 'Dictionary table must expose toolbar provider actions.');
        slang_assert(str_contains((string) ($table['wire_target'] ?? ''), 'openDeleteModal'), 'Dictionary table must expose standard delete modal action.');
        slang_assert(str_contains((string) ($table['wire_target'] ?? ''), 'deleteConfirmed'), 'Dictionary table must expose standard delete confirmation action.');
        slang_assert(($table['per_page'] ?? null) === 10, 'Dictionary per-page default must be 10.');
        slang_assert(($table['per_page_options'] ?? []) === [5, 10, 20, 30, 50, 100], 'Dictionary per-page options must use the standard set.');
        slang_assert(($table['search']['width'] ?? null) === 'sm', 'Dictionary search must stay compact.');
        slang_assert(($table['columns'][0]['editable'] ?? true) === false, 'Dictionary key column must not be editable online.');
        slang_assert(in_array('synchronize', array_column((array) ($table['actions'] ?? []), 'key'), true), 'Dictionary toolbar must include synchronize action.');
        $syncAction = null;
        foreach ((array) ($table['actions'] ?? []) as $action) {
            if (is_array($action) && ($action['key'] ?? null) === 'synchronize') {
                $syncAction = $action;
                break;
            }
        }
        slang_assert(($syncAction['type'] ?? null) === 'wire', 'Dictionary synchronize action must avoid iframe reload.');
        slang_assert(($syncAction['provider'] ?? null) === 'synchronizeTranslations', 'Dictionary synchronize action must call provider through Livewire.');
        slang_assert(($syncAction['placement'] ?? null) === 'controls', 'Dictionary synchronize action must render near search controls.');
        slang_assert(in_array('delete', array_column((array) ($table['actions'] ?? []), 'key'), true), 'Dictionary toolbar must include delete action for selected row.');
        slang_assert(in_array('delete', array_column((array) ($table['row_actions'] ?? []), 'key'), true), 'Dictionary rows must include delete action.');
    });

    slang_test('dictionary provider creates dynamic language columns and saves inline translations', function (): void {
        $provider = slang_read('src/Tables/TranslatesTableData.php');

        foreach ([
            'public function columns(array $columns): array',
            'collect(sLang::langConfig())',
            "'key' => \$locale",
            "'editable' => true",
            "'inline_actions'",
            "'provider' => 'autoTranslateInlineField'",
            "'header_actions'",
            "'provider' => 'autoTranslateEmptyColumn'",
            'public function createInlineRow',
            'public function deleteName',
            'public function deleteRow',
            'public function synchronizeTranslations',
            'public function synchronizeAttributes',
            'public function updateInlineField',
            'public function autoTranslateInlineField',
            'public function autoTranslateEmptyColumn',
            'foreach (sLang::langConfig() as $locale)',
            '$this->controller()->deleteTranslate',
            '$this->controller()->updateTranslate',
            '$this->controller()->setModifyTables',
        ] as $marker) {
            slang_assert_contains($marker, $provider, 'Missing dictionary provider marker: ' . $marker);
        }

        $controller = slang_read('src/Controllers/sLangController.php');
        foreach ([
            'public function deleteTranslate',
            'public function discoveredTranslationKeys',
            'public function obsoleteTranslationKeys',
            'public function cleanupObsoleteTranslations',
            'protected function isObsoleteTranslationCandidate',
            '$phrase->delete();',
            '$this->updateLangFiles();',
            "str_starts_with(\$key, 'new.translation.')",
            "trim((string) (\$translate->{\$default} ?? '')) === \$key",
        ] as $marker) {
            slang_assert_contains($marker, $controller, 'Missing controller dictionary lifecycle marker: ' . $marker);
        }
    });
});

slang_group('settings', function (): void {
    slang_test('settings panel uses evo-ui choices and dirty-state form contract', function (): void {
        $component = slang_read('src/Livewire/SettingsPanel.php');
        $view = slang_read('views/livewire/settings-panel.blade.php');
        $styles = slang_read('assets/css/manager.css');

        foreach ([
            'public bool $dirty = false;',
            'public function updatedData',
            'public function toggleChoice',
            'public function removeChoice',
            'public function toggleTv',
            'public function removeTv',
            'public function cleanupObsoleteTranslations',
            'obsoleteTranslationKeys',
            "dispatch('evo-ui:form.saved'",
            'normalizeLanguageSelections',
            'ensureDefaultLanguage',
        ] as $marker) {
            slang_assert_contains($marker, $component, 'Missing settings component marker: ' . $marker);
        }

        foreach ([
            'data-evo-form',
            'data-evo-form-dirty',
            '<x-evo::choices',
            'field="s_lang_config"',
            'field="s_lang_front"',
            'toggle-method="toggleTv"',
            'remove-method="removeTv"',
            ':disabled="!$dirty"',
            'wire:click="cleanupObsoleteTranslations"',
            'wire:confirm="@lang(\'sLang::global.cleanup_obsolete_confirm\')"',
            'cleanup_obsolete_count',
            'slang-settings__segments',
            'slang-settings__maintenance',
        ] as $marker) {
            slang_assert_contains($marker, $view, 'Missing settings view marker: ' . $marker);
        }

        foreach ([
            'justify-content: flex-end;',
            'justify-self: end;',
            'text-align: right;',
            'justify-content: flex-start;',
            'slang-settings__segments',
            'slang-settings__maintenance',
            'justify-content: space-between;',
            'justify-items: start;',
        ] as $marker) {
            slang_assert_contains($marker, $styles, 'Missing settings stylesheet marker: ' . $marker);
        }

        slang_assert(!str_contains($view, '<style>'), 'Settings panel must load package CSS instead of inline styles.');

        foreach (['en', 'uk', 'ru', 'fr'] as $locale) {
            $translations = slang_config('lang/' . $locale . '/global.php');

            foreach ([
                'cleanup_obsolete',
                'cleanup_obsolete_help',
                'cleanup_obsolete_action',
                'cleanup_obsolete_confirm',
                'cleanup_obsolete_count',
                'cleanup_obsolete_done',
                'cleanup_obsolete_empty',
            ] as $key) {
                slang_assert(isset($translations[$key]), 'Missing cleanup translation key for ' . $locale . ': ' . $key);
            }
        }
    });
});

slang_group('module-shell', function (): void {
    slang_test('module panel uses evo-ui table and dirty navigation guard', function (): void {
        $panel = slang_read('views/livewire/module-panel.blade.php');

        foreach ([
            '<x-evo::table.livewire',
            'preset="slang.translates"',
            '<livewire:slang.settings-panel',
            'window.EvoUI.form.isDirty()',
            'data-evo-form-dirty',
            'x-on:evo-ui:form.saved.window',
            'data-evo-tab-panel',
        ] as $marker) {
            slang_assert_contains($marker, $panel, 'Missing module shell marker: ' . $marker);
        }
    });

    slang_test('manager chrome keeps module title and standard module icon stable', function (): void {
        $shell = slang_read('views/index.blade.php');
        $managerScript = slang_read('assets/js/manager.js');

        foreach ([
            "\$moduleTitle = __('sLang::global.module_title')",
            'css/manager.css',
            'js/manager.js',
            'data-slang-module-title',
        ] as $marker) {
            slang_assert_contains($marker, $shell, 'Missing manager chrome marker: ' . $marker);
        }

        foreach ([
            "document.querySelector('[data-slang-module-title]')",
            'const syncManagerChrome = () => {',
            'doc.title = moduleTitle;',
            'new MutationObserver(syncManagerChrome)',
        ] as $marker) {
            slang_assert_contains($marker, $managerScript, 'Missing manager asset marker: ' . $marker);
        }

        slang_assert(!str_contains($shell, '<script>'), 'Module shell must load manager JS from package assets.');

        foreach ([
            'slang-manager-menu-icon',
            'normalizeModuleIcon',
            'holder.dataset.slangManagerIconSynced',
            "svg.style.width = '1em';",
        ] as $marker) {
            slang_assert(!str_contains($shell, $marker), 'sLang shell must not override manager module icon sizing: ' . $marker);
        }

        foreach (['en', 'uk', 'ru', 'fr'] as $locale) {
            $translations = slang_config('lang/' . $locale . '/global.php');

            slang_assert(($translations['module_icon'] ?? null) === 'tabler-world', 'Module icon must match standard tabler-world for ' . $locale . '.');
            slang_assert(($translations['slang_icon'] ?? null) === 'tabler-world', 'Legacy icon fallback must match standard tabler-world for ' . $locale . '.');
            slang_assert(trim((string) ($translations['module_title'] ?? '')) !== '', 'Module title must be translated for ' . $locale . '.');
        }
    });
});

slang_group('release-cleanup', function (): void {
    slang_test('migrated manager legacy views and endpoints are removed', function (): void {
        foreach ([
            'views/translatesTab.blade.php',
            'views/settingsTab.blade.php',
            'views/partials/pagination.blade.php',
        ] as $path) {
            slang_assert(!is_file(slang_path($path)), 'Migrated manager legacy file must stay removed: ' . $path);
        }

        $module = slang_read('modules/sLangModule.php');
        $controller = slang_read('src/Controllers/sLangController.php');
        $bootstrap = slang_read('src/sLang.php');

        foreach ([
            'case "translate"',
            'case "update"',
            'case "add-new"',
            'case "settings"',
            'saveTranslate',
        ] as $marker) {
            slang_assert(!str_contains($module, $marker), 'Manager module must not expose migrated legacy action: ' . $marker);
        }

        slang_assert_contains('case "synchronize"', $module, 'Dictionary synchronize action must remain available for template key scanning.');
        slang_assert_contains('parseBlade', $module, 'Dictionary synchronize action must call the legacy backend parser.');

        foreach ([
            'case "translate-only"',
            'getAutomaticTranslate',
        ] as $marker) {
            slang_assert_contains($marker, $module, 'Resource translation endpoint must stay available: ' . $marker);
        }

        foreach ([
            'public function dictionary',
            'public function saveTranslate',
            'protected function getElementRow',
            'Illuminate\\Pagination\\Paginator',
        ] as $marker) {
            slang_assert(!str_contains($controller, $marker), 'Controller must not keep migrated legacy manager code: ' . $marker);
        }

        slang_assert(!str_contains($bootstrap, 'Paginator::defaultView'), 'sLang bootstrap must not register removed custom paginator view.');
    });
});

slang_group('resource-tabs', function (): void {
    slang_test('legacy resource tabs stay isolated from evo-ui module screens', function (): void {
        $tabs = slang_read('views/tabs.blade.php');
        $general = slang_read('views/resourceGeneralTab.blade.php');
        $templateVariables = slang_read('views/resourceTemplateVariablesTab.blade.php');

        foreach ([
            "@include('sLang::resourceGeneralTab')",
            "@include('sLang::resourceTemplateVariablesTab')",
            "@include('sLang::resourceSettingsTab')",
            'form#mutate',
            'syncTaProxy',
            'window.sLangResourceTabs',
            'data-slang-default-content',
            'data-slang-codemirror-target',
            'data-slang-editor-key',
            'data-slang-translate',
            'data-slang-dirty',
            'data-slang-resource-action',
            "sLang::partials.translate-button",
            "sLang::partials.resource-field-label",
        ] as $marker) {
            slang_assert_contains($marker, $tabs . "\n" . $general, 'Missing legacy resource tab marker: ' . $marker);
        }

        slang_assert_contains('templateVariables_', $templateVariables, 'Template variables must still render per-language tabs.');
        slang_assert_contains('data-slang-tv-surface', $templateVariables, 'Template variable tabs must expose the sLang TV adapter surface marker.');
        slang_assert_contains('data-slang-tv-row', slang_read('views/partials/tvResource.blade.php'), 'TV rows must expose the sLang TV adapter row marker.');
        slang_assert_contains('slang-resource-tab-page', $general, 'General resource tabs must expose a local style scope.');
        slang_assert_contains('slang-resource-tab-page', $templateVariables, 'Template variable resource tabs must expose a local style scope.');
        slang_assert_contains('slang-resource-tab-page', slang_read('views/resourceSettingsTab.blade.php'), 'Settings resource tab must expose a local style scope.');
        slang_assert_contains('<h2 class="tab">@lang(\'global.settings_general\')', $general, 'General tab title must keep exact class="tab" for legacy TabPane.');
        slang_assert_contains('<h2 class="tab">@lang(\'global.settings_templvars\')', $templateVariables, 'Template variable tab title must keep exact class="tab" for legacy TabPane.');
        slang_assert_contains('.slang-resource-tab-page .form-row .row-col', $tabs, 'Resource tab form-row styles must be scoped.');
        slang_assert_contains('.tab-row .slang-lang-badge', $tabs, 'Moved TabPane language badge styles must survive legacy tab title relocation.');
        slang_assert_contains('.slang-resource-tab-page .form-row {margin-bottom:0.25rem;}', $tabs, 'Resource tab form-row rhythm must match legacy custom.css.');
        slang_assert_contains('.slang-resource-tab-page .evo-ui-btn', $tabs, 'Resource tab buttons must have scoped local styles.');
        slang_assert_contains('.slang-resource-tab-page .evo-ui-btn--icon', $tabs, 'Resource tab icon buttons must have scoped local styles.');
        slang_assert((bool) preg_match('/\\.slang-resource-tab-page\\s+input\\[type=checkbox\\]/', $tabs), 'Checkbox styling must be scoped to sLang resource tabs.');
        slang_assert(!preg_match('/(^|\\n)\\s*input\\[type=checkbox\\]/', $tabs), 'Resource tabs must not style all manager checkboxes globally.');
        slang_assert(!preg_match('/(^|\\n)\\s*\\.evo-ui-btn\\s*\\{/', $tabs), 'Resource tabs must not style all evo-ui buttons globally.');
        slang_assert(!str_contains($tabs, 'form#mutate input[name="menuindex"]'), 'Resource tabs must not style the mutate form menu index globally.');
        slang_assert(!preg_match('/(^|\\n)\\s*\\.form-row\\s+\\.row-col/', $tabs), 'Resource tabs must not style all manager form rows globally.');
        slang_assert(!preg_match('/(^|\\n)\\s*\\.badge\\.bg-seigerit/', $tabs), 'Resource tabs must not style all manager language badges globally.');
        slang_assert(!str_contains($general, 'btn btn-light js_translate'), 'Resource translate buttons must not use legacy Bootstrap/fontawesome button markup.');
        slang_assert(!str_contains($general, 'style="width: calc(100% - 52px);"'), 'Resource General translated fields must not use inline width hacks.');
        slang_assert(!str_contains($general, 'onclick='), 'Resource General must route avoidable click handlers through data attributes.');
        slang_assert(!str_contains(slang_read('views/resourceSettingsTab.blade.php'), 'onclick='), 'Resource Settings must route avoidable click handlers through data attributes.');
        slang_assert(!str_contains($tabs, '<x-evo::table.livewire'), 'Legacy resource tab boundary must not instantiate evo-ui module tables.');
        slang_assert(!str_contains($general, 'data-evo-form'), 'Legacy resource tabs must not masquerade as evo-ui module forms.');
        slang_assert_contains('form#mutate', slang_read('docs/en/resource-bridge.md'), 'Resource bridge docs must document the Evolution form boundary.');
        slang_assert_contains('tpSettings.addTabPage', slang_read('docs/en/resource-bridge.md'), 'Resource bridge docs must document the TabPane boundary.');
        slang_assert_contains('window.sLangResourceTabs', slang_read('docs/en/resource-bridge.md'), 'Resource bridge docs must document the sLang resource adapter.');
    });
});

slang_group('docs', function (): void {
    slang_test('old GitHub Pages docs are archived and standard multilingual docs are valid', function (): void {
        foreach ([
            'old_docs/pages/index.md',
            'old_docs/pages/getting-started.md',
            'old_docs/pages/management.md',
            'old_docs/pages/use-in-blade.md',
            'old_docs/_config.yml',
            'old_docs/assets/img/logo.svg',
            'docs/Images',
            '.dissues/tasks',
        ] as $file) {
            slang_assert(file_exists(slang_path($file)), 'Missing documentation archive item: ' . $file);
        }

        slang_assert(!is_dir(slang_path('ddox')), 'Custom ddox directory must not exist.');
        slang_assert(!is_dir(slang_path('docs/tasks')), 'Internal dIssues task artifacts must not live under docs.');
        slang_assert(!is_dir(slang_path('docs/ua')), 'Ukrainian docs must use uk, not ua.');

        $languages = ['uk', 'en', 'de', 'fr', 'pl'];
        $pages = ['README.md', 'getting-started.md', 'management-tabs.md', 'use-in-blade.md', 'resource-bridge.md'];

        foreach ($languages as $language) {
            foreach ($pages as $page) {
                slang_assert(is_file(slang_path('docs/' . $language . '/' . $page)), 'Missing localized docs file: docs/' . $language . '/' . $page);
            }
        }

        foreach (['user-guide.md', 'developer-guide.md'] as $generatedFile) {
            foreach ($languages as $language) {
                slang_assert(!is_file(slang_path('docs/' . $language . '/' . $generatedFile)), 'Generated guide file must not return: docs/' . $language . '/' . $generatedFile);
            }
        }

        foreach ($languages as $language) {
            foreach ($pages as $page) {
                $file = 'docs/' . $language . '/' . $page;
                $path = slang_path($file);
                $content = (string) file_get_contents($path);
                slang_assert(!str_contains($content, '{% raw %}'), 'dDocs copy must not contain Jekyll raw marker: ' . $file);
                slang_assert(!str_contains($content, '{% endraw %}'), 'dDocs copy must not contain Jekyll endraw marker: ' . $file);
                preg_match_all('/\[[^\]]+\]\(([^)]+)\)/', $content, $matches);

                foreach ($matches[1] as $target) {
                    if (
                        str_starts_with($target, 'http://')
                        || str_starts_with($target, 'https://')
                        || str_starts_with($target, 'mailto:')
                        || str_starts_with($target, '#')
                    ) {
                        continue;
                    }

                    $targetPath = strtok($target, '#') ?: '';
                    slang_assert($targetPath !== '', 'Empty Markdown link target in ' . $file);
                    slang_assert(
                        is_file(dirname($path) . '/' . $targetPath),
                        'Broken local Markdown link in ' . $file . ': ' . $target
                    );
                }
            }
        }
    });
});

slang_group('regression-entrypoint', function (): void {
    slang_test('demo regression script remains available for database-backed smoke', function (): void {
        $script = slang_read('tests/regression/slang-demo-regression.php');

        foreach ([
            'assertDictionaryCrud',
            'assertBulkAutoTranslateEmptyColumn',
            'assertSettingsPanel',
            'assertObsoleteCleanup',
            'assertChoicesRenderCleanHtml',
            'SlangRegressionBulkTableData',
            'SlangRegressionCleanupController',
        ] as $marker) {
            slang_assert_contains($marker, $script, 'Missing regression script marker: ' . $marker);
        }

        slang_assert(is_file(slang_path('tests/demo-smoke.php')), 'Demo smoke runner must live with tests.');
        slang_assert(!is_file(slang_path('scripts/demo-smoke.php')), 'Demo smoke runner must not live in production scripts.');
    });
});

if ($failed > 0) {
    exit(1);
}

echo "OK {$passed} tests\n";
