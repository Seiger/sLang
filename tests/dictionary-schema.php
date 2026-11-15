<?php

declare(strict_types=1);

namespace Seiger\sLang\Support;

use Closure;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Seiger\sLang\Controllers\sLangController;
use Seiger\sLang\Tables\TranslatesTableData;
use SlangDocFormSaveStub;

final class DictionarySchemaQueryFailure extends RuntimeException
{
}

final class DictionarySchemaBlueprintStub
{
    public string $locale;
    public bool $nullable = false;

    public function text(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function nullable(): void
    {
        $this->nullable = true;
    }
}

final class DictionarySchemaStub
{
    public static array $columns = [];
    public static array $attempts = [];
    public static int $reads = 0;
    public static ?string $addedConcurrently = null;
    public static ?string $denied = null;
    public static int $transactionLevel = 0;

    public static function getConnection(): object
    {
        return new class {
            public function getDriverName(): string
            {
                return 'mysql';
            }

            public function transactionLevel(): int
            {
                return DictionarySchemaStub::$transactionLevel;
            }
        };
    }

    public static function getColumnListing(string $table): array
    {
        slang_assert($table === 's_lang_translates', 'Inspect the dictionary table only.');
        self::$reads++;

        return self::$columns;
    }

    public static function table(string $table, Closure $define): void
    {
        slang_assert($table === 's_lang_translates', 'Only the dictionary table may be altered.');
        $blueprint = new DictionarySchemaBlueprintStub();
        $define($blueprint);
        slang_assert($blueprint->nullable, 'Dictionary languages must add nullable text columns.');
        self::$attempts[] = $blueprint->locale;

        if ($blueprint->locale === self::$denied) {
            throw new DictionarySchemaQueryFailure('ALTER denied');
        }

        self::$columns[] = $blueprint->locale;
        if ($blueprint->locale === self::$addedConcurrently) {
            throw new DictionarySchemaQueryFailure('Column added by another settings save');
        }
    }
}

final class DictionarySchemaControllerFixture extends sLangController
{
    public function __construct(private readonly string $siteRoot)
    {
    }

    protected function languageFileWriter(): LanguageFileWriter
    {
        return new LanguageFileWriter($this->siteRoot, ['uk', 'en', 'pt-br', 'zh-hant']);
    }
}

class_alias(DictionarySchemaStub::class, 'Illuminate\Support\Facades\Schema');
class_alias(DictionarySchemaBlueprintStub::class, 'Illuminate\Database\Schema\Blueprint');
class_alias(DictionarySchemaQueryFailure::class, 'Illuminate\Database\QueryException');

function slang_schema_fixture(Closure $test): void
{
    $manager = evo();
    $manager->userId = 42;
    $manager->permissions = ['access_permissions', 'exec_module', 'settings'];
    $manager->modulesFromFile = [
        ['file' => dirname(__DIR__) . '/modules/sLangModule.php', 'properties' => ['roles' => [5]]],
    ];
    SettingsRoleQueryStub::$role = new class {
        public int $access_permissions = 1;
        public int $exec_module = 1;
        public int $settings = 1;

        public function getKey(): int
        {
            return 5;
        }
    };
    DictionarySchemaStub::$columns = ['tid', 'key', 'uk', 'en'];
    DictionarySchemaStub::$attempts = [];
    DictionarySchemaStub::$reads = 0;
    DictionarySchemaStub::$transactionLevel = 0;
    DictionarySchemaStub::$addedConcurrently = DictionarySchemaStub::$denied = null;
    SlangDocFormSaveStub::$langConfig = ['uk', 'en'];

    slang_language_fixture(static function (string $root, string $directory) use ($test): void {
        $test(new DictionarySchemaControllerFixture($root), $directory);
    });
}

slang_group('dictionary-schema', function (): void {
    slang_test('schema preparation requires settings access and validates all proposed locales before inspecting columns', function (): void {
        slang_schema_fixture(function (sLangController $controller, string $directory): void {
            SettingsRoleQueryStub::$role->settings = 0;
            assertDictionaryDenied(fn () => $controller->prepareTranslationSchema(['uk', 'pt-br']));
            SettingsRoleQueryStub::$role->settings = 1;

            slang_language_file_error(InvalidArgumentException::class, fn () => $controller->prepareTranslationSchema(['uk', 'pt-br', 'unknown']));
            slang_assert(DictionarySchemaStub::$reads === 0 && DictionarySchemaStub::$attempts === [], 'Denial must happen before inspecting or attempting ALTER.');
            slang_assert(scandir($directory) === ['.', '..'], 'Denial must not initialize new dictionary files.');
        });
    });

    slang_test('settings add only missing languages and retain columns for removed locales', function (): void {
        slang_schema_fixture(function (sLangController $controller, string $directory): void {
            $controller->prepareTranslationSchema(['uk', 'pt-br', 'pt-br']);
            $controller->prepareTranslationSchema(['uk', 'pt-br']);
            $controller->prepareTranslationSchema(['uk']);

            slang_assert(DictionarySchemaStub::$attempts === ['pt-br'], 'Repeated saves and language removal must not issue another ALTER.');
            slang_assert(DictionarySchemaStub::$columns === ['tid', 'key', 'uk', 'en', 'pt-br'], 'Retain existing columns when the locale selection changes.');
            slang_assert(file_get_contents($directory . '/pt-br.json') === '{}', 'Seed the new locale before activating it.');
        });
    });

    slang_test('schema races succeed only when the requested column now exists', function (): void {
        slang_schema_fixture(function (sLangController $controller, string $directory): void {
            DictionarySchemaStub::$addedConcurrently = 'pt-br';
            $controller->prepareTranslationSchema(['pt-br']);
            slang_assert(is_file($directory . '/pt-br.json'), 'The competing save left the requested column ready for use.');

            DictionarySchemaStub::$denied = 'zh-hant';
            slang_language_file_error(DictionarySchemaQueryFailure::class, fn () => $controller->prepareTranslationSchema(['zh-hant']));
            slang_assert(!is_file($directory . '/zh-hant.json'), 'A failed ALTER must be reported before initializing the new dictionary.');

            DictionarySchemaStub::$denied = null;
            $controller->prepareTranslationSchema(['pt-br', 'zh-hant']);
            slang_assert(DictionarySchemaStub::$attempts === ['pt-br', 'zh-hant', 'zh-hant'], 'A retry must add only the column that was still missing.');
        });
    });

    slang_test('MySQL schema preparation refuses an ALTER that would commit a caller transaction', function (): void {
        slang_schema_fixture(function (sLangController $controller, string $directory): void {
            DictionarySchemaStub::$transactionLevel = 1;
            slang_language_file_error(LogicException::class, fn () => $controller->prepareTranslationSchema(['uk', 'pt-br']));
            slang_assert(DictionarySchemaStub::$attempts === [] && scandir($directory) === ['.', '..'], 'Do not alter or seed when a write would implicitly commit another transaction.');
        });
    });

    slang_test('dictionary actions report missing columns without attempting to repair them', function (): void {
        slang_schema_fixture(function (sLangController $controller): void {
            SlangDocFormSaveStub::$langConfig = ['uk', 'pt-br'];
            $provider = new class($controller) extends TranslatesTableData {
                public function __construct(private readonly sLangController $fixture)
                {
                    parent::__construct(['moduleUrl' => 'index.php']);
                }

                protected function controller(): sLangController
                {
                    return $this->fixture;
                }
            };

            slang_language_file_error(RuntimeException::class, $provider->createInlineRow(...));
            slang_language_file_error(RuntimeException::class, $controller->parseBlade(...));
            slang_assert(DictionarySchemaStub::$reads === 2 && DictionarySchemaStub::$attempts === [], 'Dictionary work must verify the columns without using ALTER.');
        });
    });
});
