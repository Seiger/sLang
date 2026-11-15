<?php

declare(strict_types=1);

namespace Seiger\sLang\Support;

use Seiger\sLang\Livewire\ModulePanel;
use Seiger\sLang\Tables\TranslatesTableData;

final class DictionaryQueryStub
{
    public static int $queries = 0;

    public static function query(): self
    {
        self::$queries++;

        return new self();
    }

    public function orderBy(string $field, string $direction): self
    {
        return $this;
    }

    public function toBase(): self
    {
        return $this;
    }

    public function getCountForPagination(): int
    {
        return 2;
    }

    public function whereKey(int $id): self
    {
        return $this;
    }

    public function exists(): bool
    {
        return false;
    }
}

function assertDictionaryDenied(callable $action): void
{
    try {
        $action();
    } catch (\RuntimeException $exception) {
        slang_assert($exception->getCode() === 403, 'Expected dictionary access to be denied with 403.');
        return;
    }

    throw new \RuntimeException('Expected dictionary access to be denied.');
}

class_alias(DictionaryQueryStub::class, 'Illuminate\Database\Eloquent\Builder');
class_alias(DictionaryQueryStub::class, 'Seiger\sLang\Models\sLangTranslate');

require slang_path('src/Tables/TranslatesTableData.php');

slang_group('dictionary-access', function (): void {
    slang_test('dictionary access separates reading, global writes, and resource translation', function (): void {
        $table = new TranslatesTableData(['moduleUrl' => 'index.php']);
        $modulePanel = new ModulePanel();
        $manager = evo();
        $manager->userId = 0;
        $manager->permissions = ['access_permissions', 'exec_module', 'settings', 'save_document'];

        assertDictionaryDenied($modulePanel->boot(...));
        assertDictionaryDenied($table->total(...));
        assertDictionaryDenied(fn () => $table->rows(1, 10));
        slang_assert(DictionaryQueryStub::$queries === 0, 'A guest must be denied before querying the dictionary.');

        $manager->userId = 42;
        $manager->modulesFromFile = [
            'module' => [
                'file' => dirname(__DIR__) . '/modules/sLangModule.php',
                'properties' => ['roles' => [5]],
            ],
        ];
        $role = new class {
            public int $access_permissions = 1;
            public int $exec_module = 1;
            public int $settings = 0;
            public int $save_document = 1;

            public function getKey(): int
            {
                return 5;
            }
        };
        SettingsRoleQueryStub::$role = $role;

        $modulePanel->boot();
        slang_assert($table->total() === 2, 'A read-only manager can read the dictionary.');
        slang_assert($table->modalFields([], [], null, 'create') === [], 'Rendering a closed modal must work without settings permission.');
        slang_assert($table->managementAttributes() === ['disabled' => true], 'Read-only managers must see disabled toolbar buttons.');
        slang_assert($table->rowActions([['key' => 'delete']]) === [], 'Read-only managers must not see row actions.');
        ManagerAccess::authorize('save_document');

        $writeActions = [
            $table->modalDefaults(...),
            fn () => $table->saveModal(['key' => 'dictionary.example'], null, 'create'),
            $table->createInlineRow(...),
            fn () => $table->deleteName(1),
            fn () => $table->deleteRow(1),
            $table->synchronizeTranslations(...),
            fn () => $table->updateInlineField(1, 'en', 'Example'),
            fn () => $table->autoTranslateInlineField(1, 'en'),
            fn () => $table->autoTranslateEmptyColumn('en'),
        ];
        foreach ($writeActions as $action) {
            assertDictionaryDenied($action);
        }
        slang_assert(DictionaryQueryStub::$queries === 1, 'A revoked settings permission must block actions before querying the dictionary.');

        $role->settings = 1;
        slang_assert($table->managementAttributes() === ['disabled' => false], 'An authorized manager can use dictionary actions.');
        slang_assert($table->autoTranslateInlineField(99, 'en') === '', 'A missing entry must return before preparing schema or calling the translator.');
        slang_assert($table->autoTranslateEmptyColumn('invalid-locale') === 0, 'Bulk translation only accepts a configured non-default locale.');

        $role->save_document = 0;
        assertDictionaryDenied(fn () => ManagerAccess::authorize('save_document'));
        $role->exec_module = 0;
        assertDictionaryDenied($table->total(...));
        $role->exec_module = 1;
        $manager->modulesFromFile['module']['properties']['roles'] = [6];
        assertDictionaryDenied($table->total(...));
        assertDictionaryDenied($table->synchronizeTranslations(...));
    });
});
