<?php namespace Seiger\sLang;
/**
 * Class SeigerLang - Seiger Lang Management Module for Evolution CMS admin panel.
 */

use EvolutionCMS\Facades\UrlProcessor;
use EvolutionCMS\Models\SiteModule;
use EvolutionCMS\Models\SiteContent;
use EvolutionCMS\Models\SiteTmplvar;
use EvolutionCMS\Models\SystemSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Seiger\sLang\Models\sLangContent;
use Seiger\sLang\Models\sLangTmplvarContentvalue;

class sLang
{
    public mixed $evo = null;

    /** @var array<int, string> */
    public array $siteContentFields = ['pagetitle', 'longtitle', 'description', 'introtext', 'content', 'menutitle', 'seotitle', 'seodescription'];

    /** @var array<string, mixed> */
    protected array $params;

    protected string $basePath = EVO_BASE_PATH . 'assets/modules/seigerlang/';
    protected string $tblSiteContent = 'site_content';

    public function __construct()
    {
        $this->params = evo()->event->params ?? [];
        $this->tblSiteContent = evo()->getDatabase()->getFullTableName($this->tblSiteContent);
    }

    /**
     * Returns an array of alternative site languages for the language switcher
     *
     * @return array<string, array<string, mixed>>
     */
    public function langSwitcher(): array
    {
        $langFront = [evo()->getConfig('manager_language', 'uk')];
        $sLangFront = $this->getConfigValue('s_lang_front');
        $sLangDefault = $this->getConfigValue('s_lang_default');
        $sLangDefaultShow = $this->getConfigValue('s_lang_default_show');
        $langList = $this->langList();
        if (trim($sLangFront)) {
            $langFront = explode(',', $sLangFront);
        }
        $request = request();
        $requestUri = $request instanceof Request ? $request->getRequestUri() : '/';
        $baseUrl = $this->stripLanguageSegmentFromUri((string) $requestUri, (string)evo()->getConfig('lang', 'uk'));
        $result = [];
        foreach ($langFront as $item) {
            $result[$item] = $langList[$item];
            $segment = $this->langSegment($item);
            if ($sLangDefault == $item && $sLangDefaultShow != 1) {
                $result[$item]['link'] = EVO_SITE_URL . ltrim($baseUrl, '/');
            } else {
                $result[$item]['link'] = EVO_SITE_URL . $segment . '/' . ltrim($baseUrl, '/');
            }
        }
        return $result;
    }

    /**
     * Generates the HTML code for the hreflang tags.
     *
     * This method generates the HTML code for the hreflang tags based on the
     * configuration settings and language switcher options.
     *
     * @return string The HTML code for the hreflang tags.
     */
    public function hrefLang(): string
    {
        $alternates = [];
        $defaultUrl = $this->defaultInUrl() ? EVO_SITE_URL . $this->langSegment($this->langDefault()) . '/' : EVO_SITE_URL;
        $alternates['x-default'] = $defaultUrl;

        foreach ($this->langSwitcher() as $lang => $item) {
            $url = trim((string)($item['link'] ?? ''));
            if ($url === '') {
                continue;
            }

            $alternates[strtolower((string)$lang)] = $url;
        }

        $lines = [];
        foreach ($alternates as $lang => $url) {
            $lines[] = '<link rel="alternate" href="' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" hreflang="' . htmlspecialchars($lang, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" />';
        }

        return implode("\n", $lines);
    }

    /**
     * Retrieves the list of languages.
     *
     * This method retrieves the list of languages from the "lang-list.php" configuration file
     * and returns it as an array.
     *
     * @return array<string, array<string, string>>
     */
    public function langList(): array
    {
        $langList = [];
        if (is_file(dirname(__DIR__).'/config/lang-list.php')) {
            $langList = require dirname(__DIR__).'/config/lang-list.php';
        }
        return $langList;
    }

    /**
     * Returns the default language code.
     *
     * This method retrieves the default language code from the configuration
     * settings. If the default language code is not set in the configuration, it
     * will default to 'uk'.
     *
     * @return string The default language code.
     */
    public function langDefault(): string
    {
        $defaultLocale = trim($this->getConfigValue('s_lang_default'));

        return $defaultLocale !== '' ? $defaultLocale : 'uk';
    }

    /**
     * Retrieves the language configuration.
     *
     * This method retrieves the language configuration from the system settings
     * or the custom configuration stored in the "s_lang_config" setting.
     *
     * @return array<int, string>
     */
    public function langConfig(): array
    {
        $langConfig = [evo()->getConfig('manager_language', 'uk')];
        $sLangConfig = $this->getConfigValue('s_lang_config');
        if (trim($sLangConfig)) {
            $langConfig = explode(',', $sLangConfig);
        }
        return $langConfig;
    }

    /**
     * Retrieves the template variables.
     *
     * This method queries the database to retrieve all template variables.
     *
     * @return Collection<int, SiteTmplvar>
     */
    public function templateVariables(): Collection
    {
        return SiteTmplvar::query()->get();
    }

    /**
     * Gets an array of template variable IDs.
     *
     * This method retrieves an array of template variable IDs from the SiteTmplvar table.
     *
     * @return array<int, int>
     */
    public function templateVariablesId(): array
    {
        return SiteTmplvar::query()->pluck('id')->toArray();
    }

    /**
     * Retrieves the languages used for the frontend.
     *
     * This method retrieves the languages used for the frontend based on the configuration
     * settings and the additional language options specified in "s_lang_front" configuration
     * variable. The languages are returned as an array.
     *
     * @return array<int, string>
     */
    public function langFront(): array
    {
        $langFront = [evo()->getConfig('manager_language', 'uk')];
        $sLangFront = $this->getConfigValue('s_lang_front');
        if (trim($sLangFront)) {
            $langFront = explode(',', $sLangFront);
        }
        return $langFront;
    }

    /**
     * Whether the default frontend language must be present in URLs.
     */
    public function defaultInUrl(): bool
    {
        return trim($this->getConfigValue('s_lang_default_show')) === '1';
    }

    /**
     * Returns the configured URL segment map for languages.
     *
     * @return array<string, string>
     */
    public function langSegments(): array
    {
        $segments = [];
        $stored = trim((string)$this->getConfigValue('s_lang_url_map'));
        $decoded = $stored !== '' ? json_decode($stored, true) : [];

        foreach ($this->configuredLocales() as $locale) {
            $segment = is_array($decoded) ? ($decoded[$locale] ?? $locale) : $locale;
            $segment = $this->normalizeLanguageSegment((string)$segment, $locale);
            $segments[$locale] = $segment;
        }

        return $segments;
    }

    /**
     * Returns the frontend URL segment for a locale.
     */
    public function langSegment(string $locale): string
    {
        $segments = $this->langSegments();

        return $segments[$locale] ?? $this->normalizeLanguageSegment($locale, $locale);
    }

    /**
     * Resolve a locale from a request URL segment.
     */
    public function localeFromSegment(string $segment): ?string
    {
        $segment = $this->normalizeLanguageSegment($segment);
        if ($segment === '') {
            return null;
        }

        foreach ($this->langSegments() as $locale => $mappedSegment) {
            if ($mappedSegment === $segment) {
                return $locale;
            }
        }

        return null;
    }

    /**
     * Localize an internally generated Evo URL for the active non-default language.
     *
     * Keeps external URLs untouched and avoids duplicating an existing locale prefix.
     */
    public function localizeGeneratedUrl(string $url, ?string $locale = null): string
    {
        $locale = trim((string)($locale ?? evo()->getConfig('lang', '')));
        $defaultLocale = trim($this->langDefault());
        $shouldPrefixDefault = $locale === $defaultLocale && $this->defaultInUrl();
        $segment = $this->langSegment($locale);

        if ($url === '' || $locale === '') {
            return $url;
        }

        if (!in_array($locale, $this->configuredLocales(), true)) {
            return $url;
        }

        if (str_starts_with($url, '#') || str_starts_with($url, '?')) {
            return $url;
        }

        $siteUrl = (string)evo()->getConfig('site_url', EVO_SITE_URL);
        $siteHost = strtolower((string)parse_url($siteUrl, PHP_URL_HOST));
        $sitePath = '/' . trim((string)parse_url($siteUrl, PHP_URL_PATH), '/');
        $sitePath = $sitePath === '/' ? '/' : $sitePath . '/';

        $parts = parse_url($url);
        if ($parts === false) {
            return $url;
        }

        $scheme = strtolower((string)($parts['scheme'] ?? ''));
        if ($scheme !== '' && !in_array($scheme, ['http', 'https'], true)) {
            return $url;
        }

        $host = strtolower((string)($parts['host'] ?? ''));
        if ($host !== '' && $siteHost !== '' && $host !== $siteHost) {
            return $url;
        }

        $path = (string)($parts['path'] ?? '');
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        $shouldPrefix = $locale !== $defaultLocale || $shouldPrefixDefault;

        if ($path === '') {
            $localizedPath = $shouldPrefix ? $sitePath . $segment . '/' : $sitePath;
        } else {
            $normalizedPath = preg_replace('#^(?:\./)+#', '', $path) ?? $path;
            $normalizedPath = '/' . ltrim($normalizedPath, '/');

            if ($shouldPrefix && (
                $normalizedPath === '/' . $segment
                || str_starts_with($normalizedPath . '/', '/' . $segment . '/')
                || str_starts_with($normalizedPath . '/', $sitePath . $segment . '/')
            )) {
                return $url;
            }

            $relativePath = $normalizedPath;
            if ($sitePath !== '/' && str_starts_with($relativePath, $sitePath)) {
                $relativePath = substr($relativePath, strlen(rtrim($sitePath, '/')));
                $relativePath = '/' . ltrim($relativePath, '/');
            }

            $localizedPath = $shouldPrefix
                ? $sitePath . $segment . '/' . ltrim($relativePath, '/')
                : $sitePath . ltrim($relativePath, '/');
        }

        $localizedPath = preg_replace('#/+#', '/', $localizedPath);

        if ($scheme !== '' && $host !== '') {
            $authority = $host;
            if (isset($parts['port'])) {
                $authority .= ':' . $parts['port'];
            }

            return $scheme . '://' . $authority . $localizedPath . $query . $fragment;
        }

        return $localizedPath . $query . $fragment;
    }

    /**
     * Resolve a localized request URI to a document identifier.
     *
     * Uses Evolution's own friendly URL normalization so custom prefixes,
     * suffixes, and alias paths are handled consistently.
     */
    public function resolveLocalizedIdentifier(string $requestUri): ?int
    {
        if ($requestUri === '' || $requestUri === '/') {
            return (int)evo()->getConfig('site_start', 1);
        }

        $path = explode('?', trim($requestUri, '/'), 2);
        $documentMethod = 'alias';
        $query = UrlProcessor::cleanDocumentIdentifier($path[0], $documentMethod);
        $prefix = (string)evo()->getConfig('friendly_url_prefix', '');
        $documentListing = UrlProcessor::getFacadeRoot()->documentListing;

        if ($documentMethod === 'id' && preg_match('/^[1-9]\d*$/', (string)$query)) {
            return (int)$query;
        }

        if ($prefix !== '' && str_starts_with((string)$query, $prefix)) {
            $query = substr((string)$query, strlen($prefix));
        }

        if (evo()->getConfig('use_alias_path') == 1) {
            $virtualDir = UrlProcessor::getFacadeRoot()->virtualDir;
            $alias = ($virtualDir !== '' ? $virtualDir . '/' : '') . $query;

            if (isset($documentListing[$alias])) {
                return (int)$documentListing[$alias];
            }

            if (evo()->getConfig('aliaslistingfolder') == 1 || evo()->getConfig('full_aliaslisting') == 1) {
                $parent = $virtualDir ? UrlProcessor::getIdFromAlias($virtualDir) : 0;
                $docId = SiteContent::query()
                    ->where('deleted', 0)
                    ->where('parent', $parent)
                    ->where('alias', $query)
                    ->value('id');

                return is_null($docId) ? null : (int)$docId;
            }

            return null;
        }

        if (isset($documentListing[$query])) {
            return (int)$documentListing[$query];
        }

        $docId = SiteContent::query()
            ->where('deleted', 0)
            ->where('alias', $query)
            ->value('id');

        return is_null($docId) ? null : (int)$docId;
    }

    /**
     * Resolve the current request path for paginator URLs.
     *
     * Keeps the active frontend language segment in generated pagination links
     * instead of falling back to the site root.
     */
    public function resolveCurrentPath(): string
    {
        $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
        $path = (string)(parse_url($requestUri, PHP_URL_PATH) ?: '/');
        $path = '/' . ltrim($path, '/');
        $path = preg_replace('#/+#', '/', $path) ?? '/';

        if ($path === '') {
            $path = '/';
        }

        $siteUrl = (string)evo()->getConfig('site_url', EVO_SITE_URL);
        $scheme = (string)parse_url($siteUrl, PHP_URL_SCHEME);
        $host = (string)parse_url($siteUrl, PHP_URL_HOST);
        $port = parse_url($siteUrl, PHP_URL_PORT);

        if ($scheme === '' || $host === '') {
            return $path;
        }

        $origin = $scheme . '://' . $host;
        if (!is_null($port)) {
            $origin .= ':' . $port;
        }

        return $origin . $path;
    }

    /**
     * Remove the active language segment from a request URI.
     */
    public function stripLanguageSegmentFromUri(string $uri, ?string $locale = null): string
    {
        $locale = trim((string)($locale ?? evo()->getConfig('lang', '')));
        $segment = $this->langSegment($locale);

        $parts = parse_url($uri);
        if ($parts === false) {
            return '/';
        }

        $path = '/' . ltrim((string)($parts['path'] ?? '/'), '/');
        if ($segment !== '') {
            $path = preg_replace('#^/' . preg_quote($segment, '#') . '(?=/|$)#', '', $path, 1) ?? $path;
        }
        $path = preg_replace('#/+#', '/', $path);
        $path = $path === '' ? '/' : $path;

        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $path . $query . $fragment;
    }

    /**
     * Retrieves the language TVs.
     *
     * This method retrieves the language TVs from the configuration settings and
     * returns them as an array.
     *
     * @return array<int, string>
     */
    public function langTvs(): array
    {
        $langTvs = $this->getConfigValue('s_lang_tvs');
        if (trim($langTvs)) {
            $langTvs = explode(',', $langTvs);
        } else {
            $langTvs = [];
        }
        return $langTvs;
    }

    /**
     * Checks if a given TV ID is a multilingual TV.
     *
     * This method checks if the given TV ID is present in the array of multilingual TVs
     * returned by the `langTvs()` method. If the ID is found in the array, the method returns
     * `true`, otherwise it returns `false`.
     *
     * @param int $id The ID of the TV to check.
     * @return bool Whether the TV is a multilingual TV or not.
     */
    public function isMultilangTv($id): bool
    {
        return (in_array($id, $this->langTvs())) ? true : false;
    }

    /**
     * Retrieves the language content for a specific resource in a given language.
     *
     * This method retrieves the language content for a specific resource identified
     * by the `$resourceId` and the language specified by the `$langKey`. It queries
     * the `sLangContent` table and returns the result as an associative array.
     * If no matching record is found, an empty array is returned.
     *
     * @param int $resourceId The ID of the resource.
     * @param string $langKey The language key.
     * @return array<string, mixed> The language content as an associative array. If no matching record
     *   is found, an empty array is returned.
     */
    public function getLangContent(int $resourceId, string $langKey): array
    {
        $sLangContent = sLangContent::query()
            ->withoutGlobalScope('language')
            ->where('resource', $resourceId)
            ->where('lang', $langKey)
            ->first();
        return $sLangContent ? $sLangContent->toArray() : [];
    }

    /**
     * Retrieves the language-specific template content values for a given resource.
     *
     * This method queries the database for the language-specific template content values
     * of the specified resource and language key. It returns an array of the retrieved
     * values, keyed by the name of the template variable.
     *
     * @param int $resourceId The ID of the resource.
     * @param string $langKey The language key.
     * @return array<string, array<string, mixed>> An array of language-specific template content values.
     */
    public function getLangTemplateContentvalue(int $resourceId, string $langKey): array
    {
        $multilang_tvs_id = $this->langTvs();
        if ($multilang_tvs_id) {
            $sTemplateContentvalue = sLangTmplvarContentvalue::query()
                ->select('s_lang_tmplvar_contentvalues.*')
                ->addSelect('site_tmplvars.name as name')
                ->leftJoin('site_tmplvars', 's_lang_tmplvar_contentvalues.tmplvarid', '=', 'site_tmplvars.id')
                ->where('lang', $langKey)
                ->whereIn('tmplvarid', $multilang_tvs_id)
                ->where('contentid', $resourceId)
                ->get()
                ->keyBy('name');
        }
        return ($sTemplateContentvalue ?? false) ? $sTemplateContentvalue->toArray() : [];
    }

    /**
     * Translates a given text automatically using the Google Translate API.
     *
     * This method translates the provided text from the specified source language
     * to the target language using the Google Translate API. The translation is performed
     * automatically without any additional processing.
     *
     * @param string $text The text to be translated.
     * @param string $source The source language code.
     * @param string $target The target language code.
     *
     * @return string The translated text.
     */
    public function getAutomaticTranslate(string $text, string $source, string $target): string
    {
        return $this->googleTranslate($text, $source, $target);
    }

    /**
     * Normalize a language URL segment and fall back to the locale when empty.
     */
    protected function normalizeLanguageSegment(string $segment, ?string $fallback = null): string
    {
        $segment = trim(Str::lower($segment), " \t\n\r\0\x0B/");
        $segment = preg_replace('/[^a-z0-9_-]+/', '-', $segment) ?? '';
        $segment = trim($segment, '-');

        if ($segment === '' && !is_null($fallback)) {
            return $this->normalizeLanguageSegment($fallback);
        }

        return $segment;
    }

    /**
     * Returns every locale that can participate in sLang routing.
     *
     * Frontend routing should keep working even when the manager-only language
     * config has not been explicitly saved yet.
     *
     * @return array<int, string>
     */
    protected function configuredLocales(): array
    {
        $locales = array_merge(
            [(string)evo()->getConfig('manager_language', 'uk')],
            $this->langConfig(),
            $this->langFront(),
            [$this->langDefault()]
        );

        $locales = array_filter(array_map(function ($locale) {
            return trim((string)$locale);
        }, $locales), static function ($locale) {
            return $locale !== '';
        });

        return array_values(array_unique($locales));
    }

    /**
     * Path where files root this module
     *
     * This method returns the base path of the module.
     *
     * @return string The base path of the module.
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * Generates the URL for the module.
     *
     * This method generates the URL for the module by appending the necessary query parameters.
     *
     * @return string The URL for the module.
     */
    public function moduleUrl(): string
    {
        $title = __('sLang::global.slang');

        return 'index.php?a=112&id=' . md5(is_string($title) ? $title : 'sLang');
    }

    /**
     * Retrieves the site content fields.
     *
     * This method returns the site content fields as an array. The site content fields
     * contain the list of fields that are used for storing content related to the site.
     *
     * @return array<int, string> The site content fields array.
     */
    public function siteContentFields(): array
    {
        return $this->siteContentFields;
    }

    /**
     * Renders a view template with the given data.
     *
     * This method renders a view template using the Laravel View class. It allows passing
     * additional data to the view in the form of an associative array. The view file name or path
     * is provided as the first parameter, and the data array is optional.
     *
     * @param string $tpl The view template file name or path.
     * @param array<string, mixed> $data An optional associative array of data to be passed to the view.
     * @return bool Returns true upon successful rendering of the view.
     */
    public function view(string $tpl, array $data = []): bool
    {
        global $_lang;
        if (is_file($this->basePath . 'lang/' . evo()->getConfig('manager_language', 'uk') . '.php')) {
            require_once $this->basePath . 'lang/' . evo()->getConfig('manager_language', 'uk') . '.php';
        }

        $data = array_merge($data, ['modx' => evo(), 'data' => $data, '_lang' => $_lang]);

        $finder = View::getFinder();
        if ($finder instanceof \Illuminate\View\FileViewFinder) {
            $finder->setPaths([
                $this->basePath . 'views',
                EVO_MANAGER_PATH . 'views'
            ]);
        }
        echo View::make($tpl, $data)->render();
        return true;
    }

    /**
     * Translates the given text using Google Translate.
     *
     * This method sends a request to the Google Translate API and retrieves
     * the translated text for the given input text. The source and target
     * languages can be specified. If the source and target languages are the
     * same, the method returns the input text as is.
     *
     * @param string $text The text to be translated.
     * @param string $source The source (input) language. Default is 'uk'.
     * @param string $target The target (output) language. Default is 'en'.
     * @return string The translated text.
     */
    protected function googleTranslate(string $text, string $source = 'uk', string $target = 'en'): string
    {
        if ($source == $target) {
            return $text;
        }

        $out = '';

        $primaryUrl = 'https://translate.' . 'googleapis' . '.com/translate_a/single?client=gtx&sl='
            . urlencode($source)
            . '&tl=' . urlencode($target)
            . '&dt=t&q=' . urlencode($text);
        $primaryResponse = $this->fetchTranslationResponse($primaryUrl);
        $primary = json_decode($primaryResponse, true);

        if (is_array($primary[0] ?? null)) {
            foreach ($primary[0] as $sentence) {
                if (is_array($sentence) && isset($sentence[0])) {
                    $out .= (string) $sentence[0];
                }
            }
        }

        if (trim($out) === '') {
            // Legacy endpoint kept as a fallback for compatibility with the original module flow.
            $url = 'https://translate.google.com/translate_a/single?client=at&dt=t&dt=ld&dt=qca&dt=rm&dt=bd&dj=1&hl=uk-RU&ie=UTF-8&oe=UTF-8&inputm=2&otf=2&iid=1dd3b944-fa62-4b55-b330-74909a99969e';
            $fields_string = 'sl=' . urlencode($source) . '&tl=' . urlencode($target) . '&q=' . urlencode($text);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_USERAGENT, 'AndroidTranslate/5.3.0.RC02.130475354-53000263 5.1 phone TRANSLATE_OPM5_TEST_1');

            $response = curl_exec($ch);
            curl_close($ch);
            $result = is_string($response) ? json_decode($response, true) : [];

            if (isset($result['sentences'])) {
                foreach ($result['sentences'] as $s) {
                    $out .= isset($s['trans']) ? $s['trans'] : '';
                }
            }
        }

        if (preg_match('%^\p{Lu}%u', $text) && !preg_match('%^\p{Lu}%u', $out)) { // Если оригинал с заглавной буквы то делаем и певерод с заглавной
            $out = mb_strtoupper(mb_substr($out, 0, 1)) . mb_substr($out, 1);
        }

        return $out;
    }

    protected function fetchTranslationResponse(string $url): string
    {
        if ($url === '') {
            return '';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 EvolutionCMS sLang');

        $response = curl_exec($ch);
        curl_close($ch);

        return is_string($response) ? $response : '';
    }

    /**
     * Get system setting value bypassing cache
     *
     * This method retrieves the value of the configuration setting with the given name.
     *
     * @param string $name The name of the configuration setting.
     * @return string The value of the configuration setting, or an empty string if the setting does not exist.
     */
    protected function getConfigValue($name): string
    {
        $return = '';
        $result = SystemSetting::query()->where('setting_name', $name)->first();

        if ($result) {
            $return = $result->setting_value;
        }

        return $return;
    }
}
