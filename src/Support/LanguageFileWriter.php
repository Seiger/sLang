<?php namespace Seiger\sLang\Support;

use InvalidArgumentException;
use RuntimeException;

/**
 * Publish generated dictionaries inside the site's core/lang directory.
 *
 * @since 2.1.0
 */
final class LanguageFileWriter
{
    private readonly string $directory;

    /**
     * Anchor language files to the canonical site root supplied by Evolution.
     *
     * @param list<string> $supportedLocales Locale codes from the sLang language list.
     * @since 2.1.0
     */
    public function __construct(string $siteRoot, private readonly array $supportedLocales)
    {
        $root = $siteRoot === '' ? false : realpath($siteRoot);
        if ($root === false) {
            throw new RuntimeException('Unable to resolve the sLang site root.');
        }

        $this->directory = rtrim($root, '/\\') . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'lang';
        $this->assertDirectory();
    }

    /**
     * Validate every configured locale and destination before modifying any dictionaries.
     *
     * @param array<mixed> $locales Configured locale codes, including any legacy values.
     * @return list<string>
     * @since 2.1.0
     */
    public function validatedLocales(array $locales): array
    {
        $this->assertDirectory();
        $validated = [];
        foreach ($locales as $locale) {
            $this->existingFile($this->path($locale));
            $validated[$locale] = true;
        }

        return array_keys($validated);
    }

    /**
     * Add an empty JSON dictionary when no other writer has published one.
     *
     * @since 2.1.0
     */
    public function ensureExists(string $locale): void
    {
        $this->write($locale, '{}', replace: false);
    }

    /**
     * Replace a dictionary in one filesystem operation after encoding it successfully.
     *
     * @since 2.1.0
     */
    public function replace(string $locale, string $json): void
    {
        $this->write($locale, $json, replace: true);
    }

    /**
     * Recheck core/lang and its parent without following links inside the resolved site root.
     *
     * @since 2.1.0
     */
    private function assertDirectory(): void
    {
        clearstatcache(true);
        $resolved = realpath($this->directory);
        if ($resolved === false || !is_dir($this->directory)
            || is_link(dirname($this->directory)) || is_link($this->directory)
            || (DIRECTORY_SEPARATOR === '\\'
                ? strcasecmp($resolved, $this->directory) !== 0
                : $resolved !== $this->directory)) {
            throw new RuntimeException('sLang requires a local core/lang directory within the site root.');
        }
    }

    /**
     * Build a file path only from an exact supported locale with a safe filename.
     *
     * @since 2.1.0
     */
    private function path(mixed $locale): string
    {
        if (!is_string($locale) || !preg_match('/\A[a-z]{2,3}(?:[-_][a-z0-9]{2,8})*\z/', $locale)
            || !in_array($locale, $this->supportedLocales, true)) {
            throw new InvalidArgumentException('Invalid or unsupported locale for an sLang dictionary.');
        }

        return $this->directory . DIRECTORY_SEPARATOR . $locale . '.json';
    }

    /**
     * Reject symbolic links, directories and special files at a dictionary filename.
     *
     * @since 2.1.0
     */
    private function existingFile(string $path): bool
    {
        clearstatcache(true, $path);
        if (is_link($path) || (file_exists($path) && !is_file($path))) {
            throw new RuntimeException('sLang dictionary must be a regular file: ' . basename($path));
        }

        return is_file($path);
    }

    /**
     * Stage a complete file beside its destination before publishing it under the locale name.
     *
     * A hard link creates a missing dictionary atomically without overwriting another writer's file.
     * Replacement uses a same-directory rename so readers always see a complete dictionary.
     *
     * @since 2.1.0
     */
    private function write(string $locale, string $json, bool $replace): void
    {
        $path = $this->path($locale);
        $this->assertDirectory();
        $exists = $this->existingFile($path);
        if ($exists && !$replace) {
            return;
        }

        $mode = $exists ? fileperms($path) : null;
        if ($exists && ($mode === false || !is_writable($path))) {
            throw new RuntimeException('sLang dictionary is not writable: ' . basename($path));
        }

        $temporary = $this->directory . DIRECTORY_SEPARATOR . '.slang-' . bin2hex(random_bytes(12)) . '.tmp';
        $stream = @fopen($temporary, 'xb');
        if ($stream === false) {
            throw new RuntimeException('Unable to stage an sLang dictionary in core/lang.');
        }

        try {
            if ($mode !== null && !@chmod($temporary, $mode & 0777)) {
                throw new RuntimeException('Unable to preserve permissions for the sLang dictionary: ' . basename($path));
            }
            if (@fwrite($stream, $json) !== strlen($json) || !@fflush($stream) || !@fclose($stream)) {
                throw new RuntimeException('Unable to write the complete sLang dictionary: ' . basename($path));
            }

            $this->assertDirectory();
            $exists = $this->existingFile($path);
            if ($replace) {
                if (!@rename($temporary, $path)) {
                    throw new RuntimeException('Unable to replace the sLang dictionary: ' . basename($path));
                }
            } elseif (!$exists && !@link($temporary, $path) && !$this->existingFile($path)) {
                throw new RuntimeException('Unable to create the sLang dictionary: ' . basename($path));
            }
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
            @unlink($temporary);
        }
    }
}
