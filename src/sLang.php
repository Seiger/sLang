<?php namespace Seiger\sLang;
/**
 * Class SeigerLang - Seiger Lang Management Module for Evolution CMS admin panel.
 */

use EvolutionCMS\Models\SiteModule;
use EvolutionCMS\Models\SystemSetting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use sLang\Models\sLangContent;
use sLang\Models\sLangTranslate;

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
     * Preparing Resource Fields
     *
     * @param $content array
     * @return array
     */
    public function prepareFields(array $content): array
    {
        $contentLang = [];

        foreach ($this->langConfig() as $langConfig) {
            foreach ($this->siteContentFields as $siteContentField) {
                $contentLang[$langConfig . '_' . $siteContentField] = '';
            }
        }

        $translates = sLangContent::whereResource($content['id'] ?? 0)->get()->toArray();

        if (is_array($translates) && count($translates)) {
            foreach ($translates as $translate) {
                $currentLang = $translate['lang'];
                unset($translate['id'], $translate['resource'], $translate['lang'], $translate['created_at'], $translate['updated_at']);

                foreach ($translate as $key => $value) {
                    if (is_null($value)) {
                        $value = '';
                    }
                    $contentLang[$currentLang . '_' . $key] = $value;
                }
            }
        } else {
            foreach ($this->siteContentFields as $siteContentField) {
                $contentLang[$this->langDefault() . '_' . $siteContentField] = (string)($content[$siteContentField] ?? '');
            }
        }

        return array_merge($content, $contentLang);
    }

    /**
     * Recording resource translations
     *
     * @param int $resourceId
     * @param string $langKey
     * @param array $fields
     * @return void
     */
    public function setLangContent(int $resourceId, string $langKey, array $fields): void
    {
        sLangContent::updateOrCreate(['resource' => $resourceId, 'lang' => $langKey], $fields);
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
     * Parsing Translations in Blade Templates
     *
     * @return bool
     */
    public function parseBlade(): bool
    {
        $list = [];
        $langDefault = $this->langDefault();
        if (is_dir(MODX_BASE_PATH . 'views')) {
            $views = array_merge(glob(MODX_BASE_PATH . 'views/*.blade.php'), glob(MODX_BASE_PATH . 'views/*/*.blade.php'));

            if (is_array($views) && count($views)) {
                foreach ($views as $view) {
                    $data = file_get_contents($view);
                    preg_match_all("/@lang\('\K.+?(?='\))/", $data, $match);

                    if (is_array($match) && is_array($match[0]) && count($match[0])) {
                        foreach ($match[0] as $item) {
                            $list[] = str_replace(["@lang('", "')"], '', $item);
                        }
                    }
                }
            }
        }
        $list = array_unique($list);

        $sLangs = sLangTranslate::all()->pluck('key')->toArray();

        $needs = array_diff($list, $sLangs);
        if (count($needs)) {
            foreach ($needs as &$need) {
                $key = Str::limit($need, 125, '...');
                if (!in_array($key, $sLangs)) {
                    $sLangTranslate = new sLangTranslate();
                    $sLangTranslate->key = $key;
                    $sLangTranslate->{$langDefault} = $need;
                    $sLangTranslate->save();
                }
            }
        }

        $this->updateLangFiles();

        return true;
    }

    /**
     * Get automatic translation
     *
     * @param $source
     * @param $target
     * @return string
     */
    public function getAutomaticTranslate($source, $target): string
    {
        $result = '';
        $langDefault = $this->langDefault();
        $phrase = sLangTranslate::find($source);

        if ($phrase) {
            $text = $phrase[$langDefault];
            $result = $this->googleTranslate($text, $langDefault, $target);
        }

        if (trim($result)) {
            $phrase->{$target} = $result;
            $phrase->save();
        }

        $this->updateLangFiles();

        return $result;
    }

    /**
     * Update translation field
     *
     * @param $source
     * @param $target
     * @param $value
     * @return bool
     */
    public function updateTranslate($source, $target, $value): bool
    {
        $result = false;
        $phrase = sLangTranslate::find($source);

        if ($phrase) {
            $phrase->{$target} = $value;
            $phrase->update();

            $this->updateLangFiles();

            $result = true;
        }

        return $result;
    }

    /**
     * Save new translate and return HTML
     *
     * @param array $data
     * @return string|void
     */
    public function saveTranslate(array $data)
    {
        if (isset($data['translate']) && count($data['translate'])) {
            $phrase = sLangTranslate::firstOrCreate(['key' => $data['translate']['key']]);
            foreach ($data['translate'] as $field => $translate) {
                $phrase->{$field} = $translate;
            }
            $phrase->save();

            $this->updateLangFiles();

            return $this->getElementRow($phrase);
        }
    }

    /**
     * Get automatic translation without save
     *
     * @param $text
     * @param $source
     * @param $target
     * @return string
     */
    public function getAutomaticTranslateOnly($text, $source, $target)
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
     * Get html element for row table
     *
     * @param $data
     * @return string
     */
    protected function getElementRow($data)
    {
        global $_lang;
        if (is_file($this->basePath . 'lang/' . evo()->getConfig('manager_language', 'uk') . '.php')) {
            require_once $this->basePath . 'lang/' . evo()->getConfig('manager_language', 'uk') . '.php';
        }

        $html = '<tr><td>'.$data->key.'</td>';
        foreach($this->langConfig() as $langConfig) {
            $html .= '<td data-tid="'.$data->tid.'" data-lang="'.$langConfig.'">';
            if ($langConfig == $this->langDefault()) {
                $html .= '<input type="text" class="form-control" name="sLang['.$data->tid.']['.$langConfig.']" value="'.$data->{$langConfig}.'" />';
            } else {
                $html .= '<div class="input-group">';
                $html .= '<input type="text" class="form-control" name="sLang['.$data->tid.']['.$langConfig.']" value="'.$data->{$langConfig}.'" />';
                $html .= '<span class="input-group-btn">';
                $html .= '<button class="btn btn-light js_translate" type="button" title="'.$_lang['slang_auto_translate'].' '.strtoupper($this->langDefault()).' => '.strtoupper($langConfig).'" style="padding:0 5px;color:#0275d8;">';
                $html .= '<i class="fa fa-language" style="font-size:xx-large;"></i>';
                $html .= '</button></span></div>';
            }
            $html .= '</td>';
        }
        $html .= '</tr>';

        return $html;
    }

    /**
     * Update translation files
     */
    protected function updateLangFiles(): void
    {
        foreach ($this->langConfig() as &$lang) {
            $json = sLangTranslate::all()->pluck($lang, 'key')->toJson();

            file_put_contents(MODX_BASE_PATH . 'core/lang/' . $lang . '.json', $json);
        }
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