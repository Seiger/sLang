<?php

declare(strict_types=1);

use Seiger\sLang\Support\LanguageFileWriter;

require slang_path('src/Support/LanguageFileWriter.php');

function slang_language_file_error(string $exceptionType, Closure $operation): void
{
    try {
        $operation();
    } catch (Throwable $error) {
        slang_assert($error instanceof $exceptionType, 'Unexpected dictionary error: ' . $error->getMessage());
        return;
    }

    throw new RuntimeException('Expected the dictionary operation to be rejected.');
}

function slang_remove_language_fixture(string $directory): void
{
    foreach (new FilesystemIterator($directory) as $entry) {
        if (!$entry->isLink() && $entry->isDir()) {
            slang_remove_language_fixture($entry->getPathname());
        } else {
            unlink($entry->getPathname());
        }
    }
    rmdir($directory);
}

function slang_language_fixture(Closure $test): void
{
    $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'slang-files-' . bin2hex(random_bytes(12));
    slang_assert(mkdir($root, 0700), 'Unable to create the temporary site directory.');
    try {
        $directory = $root . '/core/lang';
        slang_assert(mkdir($directory, 0700, true), 'Unable to create the temporary core/lang directory.');
        $test($root, $directory);
    } finally {
        slang_remove_language_fixture($root);
    }
}

slang_group('language-files', function (): void {
    slang_test('supported locale dictionaries are seeded and replaced in core/lang', function (): void {
        slang_language_fixture(function (string $root, string $directory): void {
            $allowed = array_keys(slang_config('config/lang-list.php'));
            $writer = new LanguageFileWriter($root, $allowed);
            $languages = $writer->validatedLocales(['en', 'pt-br', 'zh-hant', 'en']);
            slang_assert($languages === ['en', 'pt-br', 'zh-hant'], 'Configured locale names must retain their canonical spelling.');

            foreach ($languages as $locale) {
                $writer->ensureExists($locale);
                slang_assert(file_get_contents($directory . '/' . $locale . '.json') === '{}', 'Seed JSON must contain an empty object.');
            }

            $writer->replace('en', '{"title":"English"}');
            $initializer = new LanguageFileWriter($root, $allowed);
            $initializer->ensureExists('en');
            slang_assert(file_get_contents($directory . '/en.json') === '{"title":"English"}', 'Initialization must preserve a published dictionary.');

            $writer->replace('pt-br', '{"title":"Português"}');
            slang_assert(json_decode(file_get_contents($directory . '/pt-br.json'), true, 512, JSON_THROW_ON_ERROR) === ['title' => 'Português'], 'A replacement must publish the complete JSON.');
            slang_assert(scandir($directory) === ['.', '..', 'en.json', 'pt-br.json', 'zh-hant.json'], 'Publication must remove its staging files.');
        });
    });

    slang_test('unsupported and malformed locale values are rejected before publication', function (): void {
        slang_language_fixture(function (string $root, string $directory): void {
            $writer = new LanguageFileWriter($root, ['en', 'locale/name']);
            foreach (['', 'xx', 'en.json', 'locale/name', 'locale\\name', '/locale', '.', null] as $locale) {
                slang_language_file_error(InvalidArgumentException::class, fn () => $writer->validatedLocales(['en', $locale]));
            }
            slang_language_file_error(InvalidArgumentException::class, fn () => $writer->ensureExists('xx'));
            slang_language_file_error(InvalidArgumentException::class, fn () => $writer->replace('locale/name', '{}'));
            slang_assert(scandir($directory) === ['.', '..'], 'Invalid configurations must not leave dictionaries or staging files.');
        });
    });

    slang_test('filesystem errors are visible and preserve existing destinations', function (): void {
        slang_language_fixture(function (string $root, string $directory): void {
            $writer = new LanguageFileWriter($root, ['en']);
            chmod($directory, 0500);
            clearstatcache(true, $directory);
            try {
                if (!is_writable($directory)) {
                    slang_language_file_error(RuntimeException::class, fn () => $writer->ensureExists('en'));
                    slang_assert(scandir($directory) === ['.', '..'], 'An unwritable directory must not contain a partial dictionary.');
                }
            } finally {
                chmod($directory, 0700);
            }

            mkdir($directory . '/en.json');
            slang_language_file_error(RuntimeException::class, fn () => $writer->replace('en', '{}'));
            slang_assert(is_dir($directory . '/en.json'), 'A non-regular destination must be preserved.');
            rmdir($directory . '/en.json');
            rmdir($directory);
            slang_language_file_error(RuntimeException::class, fn () => $writer->ensureExists('en'));
            slang_assert(!file_exists($directory), 'A missing core/lang directory must be reported.');
        });
    });

    slang_test('linked dictionaries and redirected core/lang directories are rejected', function (): void {
        slang_language_fixture(function (string $root, string $directory): void {
            $alternate = $root . '/alternate';
            mkdir($alternate);
            file_put_contents($alternate . '/en.json', '{"title":"Fixture"}');

            // Windows workers without symlink privileges still run the regular-file tests.
            if (!@symlink($alternate . '/en.json', $directory . '/en.json')) {
                return;
            }

            $writer = new LanguageFileWriter($root, ['en']);
            slang_language_file_error(RuntimeException::class, fn () => $writer->ensureExists('en'));
            slang_language_file_error(RuntimeException::class, fn () => $writer->replace('en', '{}'));
            slang_assert(file_get_contents($alternate . '/en.json') === '{"title":"Fixture"}', 'A linked file must not be modified.');

            unlink($directory . '/en.json');
            rmdir($directory);
            slang_assert(symlink($alternate, $directory), 'Unable to link the temporary language directory.');
            slang_language_file_error(RuntimeException::class, fn () => $writer->replace('en', '{}'));
            slang_assert(file_get_contents($alternate . '/en.json') === '{"title":"Fixture"}', 'A redirected directory must not receive writes.');
        });
    });
});
