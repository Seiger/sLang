<?php namespace Seiger\sLang;
/**
 * Class SeigerLang - Seiger Lang Management Module for Evolution CMS admin panel.
 */

use EvolutionCMS\Models\SiteModule;
use EvolutionCMS\Models\SystemSetting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Seiger\sLang\Models\sLangContent;
use Seiger\sLang\Models\sLangTranslate;

class sLang
{
    public $evo;
    public $siteContentFields = ['pagetitle', 'longtitle', 'description', 'introtext', 'content', 'menutitle', 'seotitle', 'seodescription'];
    protected $params;
    protected $basePath = MODX_BASE_PATH . 'assets/modules/seigerlang/';
    protected $tblSiteContent = 'site_content';

    public function __construct()
    {
        $this->params = evo()->event->params ?? [];
        $this->tblSiteContent = evo()->getDatabase()->getFullTableName($this->tblSiteContent);

        Paginator::defaultView('sLang::partials.pagination');
    }

    /**
     * Returns an array of alternative site languages for the language switcher
     *
     * @return array
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
        $baseUrl = Str::replaceFirst(evo()->getConfig('lang', 'uk').'/', '/', request()->getRequestUri());
        $baseUrl = str_replace(['////', '///', '//'], '/', $baseUrl);
        $result = [];
        foreach ($langFront as $item) {
            $result[$item] = $langList[$item];
            if ($sLangDefault == $item && $sLangDefaultShow != 1) {
                $result[$item]['link'] = MODX_SITE_URL . ltrim($baseUrl, '/');
            } else {
                $result[$item]['link'] = MODX_SITE_URL . $item . $baseUrl;
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
        if (evo()->getConfig('s_lang_default_show', 0) == 1) {
            $hrefLangs = '<link rel="alternate" href="' . MODX_SITE_URL . $this->langDefault() . '/" hreflang="x-default" />';
        } else {
            $hrefLangs = '<link rel="alternate" href="' . MODX_SITE_URL . '" hreflang="x-default" />';
        }
        foreach ($this->langSwitcher() as $lang => $item) {
            if ($lang != evo()->getConfig('lang', 'uk')) {
                $hrefLangs .= '<link rel="alternate" href="' . $item['link'] . '" hreflang="' . $lang . '" />';
            }
        }
        return $hrefLangs;
    }

    /**
     * Retrieves the list of languages.
     *
     * This method retrieves the list of languages from the "lang-list.php" configuration file
     * and returns it as an array.
     *
     * @return array The list of languages.
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
        return evo()->getConfig("s_lang_default", 'uk');
    }

    /**
     * Retrieves the language configuration.
     *
     * This method retrieves the language configuration from the system settings
     * or the custom configuration stored in the "s_lang_config" setting.
     *
     * @return array The array containing the language configuration.
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
     * Retrieves the languages used for the frontend.
     *
     * This method retrieves the languages used for the frontend based on the configuration
     * settings and the additional language options specified in "s_lang_front" configuration
     * variable. The languages are returned as an array.
     *
     * @return array The languages used for the frontend.
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
     * Retrieves the language content for a specific resource in a given language.
     *
     * This method retrieves the language content for a specific resource identified
     * by the `$resourceId` and the language specified by the `$langKey`. It queries
     * the `sLangContent` table and returns the result as an associative array.
     * If no matching record is found, an empty array is returned.
     *
     * @param int $resourceId The ID of the resource.
     * @param string $langKey The language key.
     * @return array The language content as an associative array. If no matching record
     *   is found, an empty array is returned.
     */
    public function getLangContent(int $resourceId, string $langKey): array
    {
        $sLangContent = sLangContent::whereResource($resourceId)->whereLang($langKey)->first();
        return $sLangContent ? $sLangContent->toArray() : [];
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
    public function getAutomaticTranslate($text, $source, $target)
    {
        return $this->googleTranslate($text, $source, $target);
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
        return 'index.php?a=112&id=' . md5(__('sLang::global.slang'));
    }

    /**
     * Retrieves the site content fields.
     *
     * This method returns the site content fields as an array. The site content fields
     * contain the list of fields that are used for storing content related to the site.
     *
     * @return array The site content fields array.
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
     * @param array $data An optional associative array of data to be passed to the view.
     * @return bool Returns true upon successful rendering of the view.
     */
    public function view($tpl, $data = [])
    {
        global $_lang;
        if (is_file($this->basePath . 'lang/' . evo()->getConfig('manager_language', 'uk') . '.php')) {
            require_once $this->basePath . 'lang/' . evo()->getConfig('manager_language', 'uk') . '.php';
        }

        $data = array_merge($data, ['modx' => evo(), 'data' => $data, '_lang' => $_lang]);

        View::getFinder()->setPaths([
            $this->basePath . 'views',
            MODX_MANAGER_PATH . 'views'
        ]);
        echo View::make($tpl, $data);
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
    protected function googleTranslate($text, $source = 'uk', $target = 'en')
    {
        if ($source == $target) {
            return $text;
        }

        $out = '';

        // Google translate URL
        $url = 'https://translate.google.com/translate_a/single?client=at&dt=t&dt=ld&dt=qca&dt=rm&dt=bd&dj=1&hl=uk-RU&ie=UTF-8&oe=UTF-8&inputm=2&otf=2&iid=1dd3b944-fa62-4b55-b330-74909a99969e';
        $fields_string = 'sl=' . urlencode($source) . '&tl=' . urlencode($target) . '&q=' . urlencode($text);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 3);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, 'AndroidTranslate/5.3.0.RC02.130475354-53000263 5.1 phone TRANSLATE_OPM5_TEST_1');

        $result = curl_exec($ch);
        $result = json_decode($result, TRUE);

        if (isset($result['sentences'])) {
            foreach ($result['sentences'] as $s) {
                $out .= isset($s['trans']) ? $s['trans'] : '';
            }
        } else {
            $out = '';
        }

        if (preg_match('%^\p{Lu}%u', $text) && !preg_match('%^\p{Lu}%u', $out)) { // Если оригинал с заглавной буквы то делаем и певерод с заглавной
            $out = mb_strtoupper(mb_substr($out, 0, 1)) . mb_substr($out, 1);
        }

        return $out;
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
        $result = SystemSetting::where('setting_name', $name)->first();

        if ($result) {
            $return = $result->setting_value;
        }

        return $return;
    }
}
