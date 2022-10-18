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
     * List of frontend document languages
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
     * List of alternative site languages
     *
     * @return string
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
     * List of languages
     *
     * @return array
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
     * Default language
     *
     * @return string
     */
    public function langDefault(): string
    {
        return evo()->getConfig("s_lang_default", 'uk');
    }

    /**
     * List of site languages
     *
     * @return array
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
     * List of frontend languages
     *
     * @return array
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
     * Getting a translation of a resource
     *
     * @param int $resourceId
     * @param string $langKey
     * @return array
     */
    public function getLangContent(int $resourceId, string $langKey): array
    {
        $sLangContent = sLangContent::whereResource($resourceId)->whereLang($langKey)->first();
        return $sLangContent ? $sLangContent->toArray() : [];
    }

    /**
     * Get automatic translation
     *
     * @param $text
     * @param $source
     * @param $target
     * @return string
     */
    public function getAutomaticTranslate($text, $source, $target)
    {
        return $this->googleTranslate($text, $source, $target);
    }

    /**
     * Module url
     *
     * @return string
     */
    public function moduleUrl(): string
    {
        return 'index.php?a=112&id=' . md5(__('sLang::global.slang'));
    }

    /**
     * Fields list on content
     *
     * @return array
     */
    public function siteContentFields(): array
    {
        return $this->siteContentFields;
    }

    /**
     * Display render
     *
     * @param $tpl
     * @param array $data
     * @return bool
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
     * Get Google Translations
     *
     * @param $text
     * @param string $source
     * @param string $target
     * @return string
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
            $out = 'No result';
        }

        if (preg_match('%^\p{Lu}%u', $text) && !preg_match('%^\p{Lu}%u', $out)) { // Если оригинал с заглавной буквы то делаем и певерод с заглавной
            $out = mb_strtoupper(mb_substr($out, 0, 1)) . mb_substr($out, 1);
        }

        return $out;
    }

    /**
     * Get system setting value bypassing cache
     *
     * @param $name string
     * @return string
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