<?php
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
    public $url;
    public $baseUrl = MODX_BASE_URL . 'assets/modules/seigerlang/';
    protected $params;
    protected $basePath = MODX_BASE_PATH . 'assets/modules/seigerlang/';
    protected $tblSsystemSettings = 'system_settings';
    protected $tblSiteContent = 'site_content';
    protected $tblLang = 's_lang_translates';

    public function __construct()
    {
        $this->params = evo()->event->params ?? [];
        $this->url = $this->moduleUrl();

        $this->tblSsystemSettings = evo()->getDatabase()->getFullTableName($this->tblSsystemSettings);
        $this->tblSiteContent = evo()->getDatabase()->getFullTableName($this->tblSiteContent);
        $this->tblLang = evo()->getDatabase()->getFullTableName($this->tblLang);

        Paginator::defaultView('pagination');
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
        if (is_file($this->basePath . 'lang_list.php')) {
            $langList = require $this->basePath . 'lang_list.php';
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
     * List of DB translations
     *
     * @return array
     */
    public function dictionary()
    {
        if (request()->has('search')) {
            $where[] = '`key` LIKE \'%'.request()->search.'%\'';
            foreach ($this->langConfig() as $item) {
                $where[] = '`'.$item.'` LIKE \'%'.request()->search.'%\'';
            }
            $translates = sLangTranslate::whereRaw(implode(' OR ', $where))->orderByDesc('tid')->paginate(17);
            $translates->withPath($this->url.'&search='.request()->search);
        } else {
            $translates = sLangTranslate::orderByDesc('tid')->paginate(17);
            $translates->withPath($this->url);
        }

        return $translates;
    }

    /**
     * Set default language
     *
     * @param $value string
     * @return mixed
     */
    public function setLangDefault($value)
    {
        $langs = array_keys($this->langList());
        $lang_default = $this->langDefault();
        if (trim($value) && in_array($value, $langs)) {
            $lang_default = trim($value);
        }

        return $this->updateTblSetting('s_lang_default', $lang_default);
    }

    /**
     * Set default language visibility
     *
     * @param $value string
     * @return mixed
     */
    public function setLangDefaultShow($value)
    {
        $value = (int)$value;

        return $this->updateTblSetting('s_lang_default_show', $value);
    }

    /**
     * Set site language list
     *
     * @param $value array
     * @return mixed
     */
    public function setLangConfig($value)
    {
        $langList = array_keys($this->langList());
        $langConfig = $this->langConfig();

        if (is_array($value)) {
            $langConfig = array_filter($value, function ($var) use ($langList) {
                return in_array($var, $langList) ? true : false;
            });
        }

        $langConfig = implode(',', $langConfig);

        return $this->updateTblSetting('s_lang_config', $langConfig);
    }

    /**
     * Set list of languages for frontend
     *
     * @param $value array
     * @return mixed
     */
    public function setLangFront($value)
    {
        $langConfig = $this->langConfig();
        $langFront = $this->langFront();

        if (is_array($value)) {
            $langFront = array_filter($value, function ($var) use ($langConfig) {
                return in_array($var, $langConfig) ? true : false;
            });
        }

        $langFront = implode(',', $langFront);

        return $this->updateTblSetting('s_lang_front', $langFront);
    }

    /**
     * Modifying table fields
     */
    public function setModifyTables()
    {
        $langConfig = $this->langConfig();

        /**
         * Translation table modification
         */
        $columns = [];
        $needs = [];
        $query = evo()->getDatabase()->query("DESCRIBE {$this->tblLang}");

        if ($query) {
            $fields = evo()->getDatabase()->makeArray($query);

            foreach ($fields as $field) {
                $columns[$field['Field']] = $field;
            }

            foreach ($langConfig as $lang) {
                if (!isset($columns[$lang])) {
                    $needs[] = "ADD `{$lang}` text COMMENT '" . strtoupper($lang) . " sLang version'";
                }
            }
        }

        if (count($needs)) {
            $need = implode(', ', $needs);
            $query = "ALTER TABLE `{$this->tblLang}` {$need}";
            evo()->getDatabase()->query($query);
        }

        /**
         * Setting up admin tabs
         */
        $tabs = [];

        foreach ($langConfig as $lang) {
            $tabs['General' . $lang] = [
                'title' => 'Tab ' . strtoupper($lang),
                'col:0:12' => [
                    'fields:0' => [
                        $lang . '_pagetitle' => [],
                        $lang . '_longtitle' => [],
                        $lang . '_description' => [
                            'type' => 'textareamini'
                        ],
                        $lang . '_introtext' => [
                            'type' => 'textarea'
                        ],
                        $lang . '_content' => [
                            'type' => 'richtext'
                        ],
                        $lang . '_menutitle' => [],
                        $lang . '_seotitle' => [],
                        $lang . '_seodescription' => []
                    ]
                ]
            ];
        }

        $tabs['Settings'] = [
            'title' => '$_lang[\'settings_page_settings\']',
            'col:0:6' => [
                'fields:0' => [
                    'template' => [],
                    'parent' => [],
                    'published' => [],
                    'hidemenu' => [],
                    'alias_visible' => [],
                    'searchable' => [],
                    'cacheable' => [],
                    'syncsite' => [],
                    'isfolder' => [],
                    'richtext' => []
                ]
            ],
            'col:1:6' => [
                'fields:0' => [
                    'alias' => [],
                    'link_attributes' => [],
                    'menuindex' => [],
                    'type' => [],
                    'contentType' => [],
                    'content_dispo' => [],
                    'createdon' => [],
                    'editedon' => [],
                    'pub_date' => [],
                    'unpub_date' => []
                ]
            ]
        ];

        $tabs['#Static'] = [
            'title' => 'Static',
            'col:0:12' => []
        ];

        $f = fopen(MODX_BASE_PATH . 'assets/plugins/templatesedit/configs/template__default.php', "w");
        fwrite($f, '<?php global $_lang; ' . "\r\n" . 'return [' . "\r\n");
        foreach ($tabs as $key => $item) {
            fwrite($f, "\t'" . $key . "' => [\r\n");
            foreach ($item as $name => $value) {
                if (is_array($value)) {
                    if (count($value)) {
                        fwrite($f, "\t\t'" . $name . "' => [" . "\r\n");
                        foreach ($value as $kkk => $vvv) {
                            if (is_array($vvv)) {
                                if (count($vvv)) {
                                    fwrite($f, "\t\t\t'" . $kkk . "' => [" . "\r\n");
                                    foreach ($vvv as $kk => $vv) {
                                        if (is_array($vv)) {
                                            if (count($vv)) {
                                                fwrite($f, "\t\t\t\t'" . $kk . "' => [" . "\r\n");
                                                foreach ($vv as $k => $v) {
                                                    if (mb_substr($v, 0, 1) != '$' && !is_numeric($v) && !is_bool($v)) {
                                                        $v = "'" . $v . "'";
                                                    }
                                                    fwrite($f, "\t\t\t\t\t'" . $k . "' => " . $v . ",\r\n");
                                                }
                                                fwrite($f, "\t\t\t\t" . "]" . ",\r\n");
                                            } else {
                                                fwrite($f, "\t\t\t\t'" . $kk . "' => []" . ",\r\n");
                                            }
                                        } else {
                                            if (mb_substr($vv, 0, 1) != '$' && !is_numeric($vv) && !is_bool($vv)) {
                                                $vv = "'" . $vv . "'";
                                            }
                                            fwrite($f, "\t\t\t\t'" . $kk . "' => " . $vv . ",\r\n");
                                        }
                                    }
                                    fwrite($f, "\t\t\t" . "]" . ",\r\n");
                                } else {
                                    fwrite($f, "\t\t\t'" . $kkk . "' => []" . ",\r\n");
                                }
                            } else {
                                if (mb_substr($vvv, 0, 1) != '$' && !is_numeric($vvv) && !is_bool($vvv)) {
                                    $vvv = "'" . $vvv . "'";
                                }
                                fwrite($f, "\t\t\t'" . $kkk . "' => " . $vvv . ",\r\n");
                            }
                        }
                        fwrite($f, "\t\t" . "]" . ",\r\n");
                    } else {
                        fwrite($f, "\t\t'" . $name . "' => []" . ",\r\n");
                    }
                } else {
                    if (mb_substr($value, 0, 1) != '$' && !is_numeric($value) && !is_bool($value)) {
                        $value = "'" . $value . "'";
                    }
                    fwrite($f, "\t\t'" . $name . "' => " . $value . ",\r\n");
                }
            }
            fwrite($f, "\t],\r\n");
        }
        fwrite($f, "];");
        fclose($f);

        /**
         * Setting the language fields of the template
         */
        if (is_file(MODX_BASE_PATH . 'assets/plugins/templatesedit/configs/custom_fields.example.php')
            && !is_file(MODX_BASE_PATH . 'assets/plugins/templatesedit/configs/custom_fields.php')) {
            copy(MODX_BASE_PATH . 'assets/plugins/templatesedit/configs/custom_fields.example.php',
                MODX_BASE_PATH . 'assets/plugins/templatesedit/configs/custom_fields.php');
        }
        if (is_file(MODX_BASE_PATH . 'assets/plugins/templatesedit/configs/custom_fields.php')) {
            $custom_fields = [];
            $custom_fields = include MODX_BASE_PATH . 'assets/plugins/templatesedit/configs/custom_fields.php';
            if (count($custom_fields)) {
                foreach ($custom_fields as $key => $value) {
                    $fName = explode('_', $key);
                    array_shift($fName);
                    $fName = implode('', $fName);

                    if (in_array($fName, $this->siteContentFields)) {
                        unset($custom_fields[$key]);
                    }
                }
            }

            if (isset($custom_fields['createdon'])) {
                unset($custom_fields['createdon']);
            }

            foreach ($langConfig as $lang) {
                $custom_fields[$lang . '_pagetitle'] = [
                    'title' => '$_lang[\'resource_title\'].\' (' . strtoupper($lang) . ')\'',
                    'help' => '$_lang[\'resource_title_help\']',
                    'default' => "",
                    'save' => ""
                ];
                $custom_fields[$lang . '_longtitle'] = [
                    'title' => '$_lang[\'long_title\'].\' (' . strtoupper($lang) . ')\'',
                    'help' => '$_lang[\'resource_long_title_help\']',
                    'default' => "",
                    'save' => ""
                ];
                $custom_fields[$lang . '_description'] = [
                    'title' => '$_lang[\'resource_description\'].\' (' . strtoupper($lang) . ')\'',
                    'help' => '$_lang[\'resource_description_help\']',
                    'default' => "",
                    'save' => ""
                ];
                $custom_fields[$lang . '_introtext'] = [
                    'title' => '$_lang[\'resource_summary\'].\' (' . strtoupper($lang) . ')\'',
                    'help' => '$_lang[\'resource_summary_help\']',
                    'default' => "",
                    'save' => ""
                ];
                $custom_fields[$lang . '_content'] = [
                    'title' => '$_lang[\'resource_content\'].\' (' . strtoupper($lang) . ')\'',
                    'help' => "",
                    'default' => "",
                    'save' => ""
                ];
                $custom_fields[$lang . '_menutitle'] = [
                    'title' => '$_lang[\'resource_opt_menu_title\'].\' (' . strtoupper($lang) . ')\'',
                    'help' => '$_lang[\'resource_opt_menu_title_help\']',
                    'default' => "",
                    'save' => ""
                ];
                $custom_fields[$lang . '_seotitle'] = [
                    'title' => '$_lang[\'resource_title\'].\' SEO (' . strtoupper($lang) . ')\'',
                    'help' => "",
                    'default' => "",
                    'save' => ""
                ];
                $custom_fields[$lang . '_seodescription'] = [
                    'title' => '$_lang[\'resource_description\'].\' SEO (' . strtoupper($lang) . ')\'',
                    'help' => "",
                    'default' => "",
                    'save' => ""
                ];
            }

            $f = fopen(MODX_BASE_PATH . 'assets/plugins/templatesedit/configs/custom_fields.php', "w");
            fwrite($f, '<?php global $_lang, $modx; ' . "\r\n" . 'return [' . "\r\n");
            foreach ($custom_fields as $key => $item) {
                fwrite($f, "\t'" . $key . "' => [\r\n");
                foreach ($item as $name => $value) {
                    if (mb_substr($value, 0, 1) != '$' && !is_numeric($value) && !is_bool($value)) {
                        $value = "'" . $value . "'";
                    }
                    fwrite($f, "\t\t'" . $name . "' => " . $value . ",\r\n");
                }
                fwrite($f, "\t],\r\n");
            }
            fwrite($f, "];");
            fclose($f);
        }

        /**
         * Translation files configuration
         */
        foreach ($langConfig as $lang) {
            if (!is_file(MODX_BASE_PATH . 'core/lang/' . $lang . '.json')) {
                file_put_contents(MODX_BASE_PATH . 'core/lang/' . $lang . '.json', '{}');
            }
        }

        /**
         * Clearing the cache
         */
        return evo()->clearCache('full');
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
     * Update data in system settings table
     *
     * @param $name string
     * @param $value string
     * @return mixed
     */
    protected function updateTblSetting($name, $value)
    {
        return evo()->getDatabase()->query("REPLACE INTO {$this->tblSsystemSettings} (`setting_name`, `setting_value`) VALUES ('{$name}', '{$value}')");
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

    protected function moduleUrl()
    {
        $module = SiteModule::whereName('sLang')->first();
        return 'index.php?a=112&id=' . $module->id;
    }
}