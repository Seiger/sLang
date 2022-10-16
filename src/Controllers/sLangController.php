<?php namespace Seiger\sLang\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Seiger\sLang\Facades\sLang;
use Seiger\sLang\Models\sLangTranslate;

class sLangController
{
    /**
     * Show tab page with sOffer files
     *
     * @return View
     */
    public function index(): View
    {
        return $this->view('index');
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
            foreach (sLang::langConfig() as $item) {
                $where[] = '`'.$item.'` LIKE \'%'.request()->search.'%\'';
            }
            $translates = sLangTranslate::whereRaw(implode(' OR ', $where))->orderByDesc('tid')->paginate(17);
            $translates->withPath(sLang::moduleUrl().'&search='.request()->search);
        } else {
            $translates = sLangTranslate::orderByDesc('tid')->paginate(17);
            $translates->withPath(sLang::moduleUrl());
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
        $langs = array_keys(sLang::langList());
        $lang_default = sLang::langDefault();
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
        $langList = array_keys(sLang::langList());
        $langConfig = sLang::langConfig();
        $lang_default = sLang::langDefault();

        if (is_array($value)) {
            $langConfig = array_filter($value, function ($var) use ($langList) {
                return in_array($var, $langList) ? true : false;
            });
        }

        $langConfig = array_flip($langConfig);
        unset($langConfig[$lang_default]);
        $langConfig = array_flip($langConfig);
        array_unshift($langConfig, $lang_default);

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
        $langConfig = sLang::langConfig();
        $langFront = sLang::langFront();
        $lang_default = sLang::langDefault();

        if (is_array($value)) {
            $langFront = array_filter($value, function ($var) use ($langConfig) {
                return in_array($var, $langConfig) ? true : false;
            });
        }

        $langFront = array_flip($langFront);
        unset($langFront[$lang_default]);
        $langFront = array_flip($langFront);
        array_unshift($langFront, $lang_default);

        $langFront = implode(',', $langFront);

        return $this->updateTblSetting('s_lang_front', $langFront);
    }

    /**
     * Set on/off language module
     *
     * @param $value string
     * @return mixed
     */
    public function setOnOffLangModule($value)
    {
        $value = (int)$value;

        return $this->updateTblSetting('s_lang_enable', $value);
    }

    /**
     * Modifying table fields
     */
    public function setModifyTables()
    {
        $tbl = $this->tblLang = evo()->getDatabase()->getFullTableName('s_lang_translates');
        $langConfig = sLang::langConfig();

        /**
         * Translation table modification
         */
        $columns = [];
        $needs = [];
        $query = evo()->getDatabase()->query("DESCRIBE {$tbl}");

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
            $query = "ALTER TABLE `{$tbl}` {$need}";
            evo()->getDatabase()->query($query);
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
     * Parsing Translations in Blade Templates
     *
     * @return bool
     */
    public function parseBlade(): void
    {
        $list = [];
        $langDefault = sLang::langDefault();
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
                $key = Str::limit($need, 252, '...');
                if (!in_array($key, $sLangs)) {
                    $sLangTranslate = new sLangTranslate();
                    $sLangTranslate->key = $key;
                    $sLangTranslate->{$langDefault} = $need;
                    $sLangTranslate->save();
                }
            }
        }

        $this->updateLangFiles();
    }

    /**
     * Get automatic translation
     *
     * @param $source
     * @param $target
     * @return string
     */
    public function setAutomaticTranslate($source, $target): string
    {
        $result = '';
        $langDefault = sLang::langDefault();
        $phrase = sLangTranslate::find($source);

        if ($phrase) {
            $text = $phrase[$langDefault];
            $result = sLang::getAutomaticTranslate($text, $langDefault, $target);
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
     * Display render
     *
     * @param string $tpl
     * @param array $data
     * @return bool
     */
    public function view(string $tpl, array $data = [])
    {
        return \View::make('sLang::'.$tpl, $data);
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
     * Update data in system settings table
     *
     * @param $name string
     * @param $value string
     * @return mixed
     */
    protected function updateTblSetting($name, $value)
    {
        $tbl = evo()->getDatabase()->getFullTableName('system_settings');

        return evo()->getDatabase()->query("REPLACE INTO {$tbl} (`setting_name`, `setting_value`) VALUES ('{$name}', '{$value}')");
    }

    /**
     * Update translation files
     */
    protected function updateLangFiles(): void
    {
        foreach (sLang::langConfig() as &$lang) {
            $json = sLangTranslate::all()->pluck($lang, 'key')->toJson();
            file_put_contents(MODX_BASE_PATH . 'core/lang/' . $lang . '.json', $json);
        }
    }
}