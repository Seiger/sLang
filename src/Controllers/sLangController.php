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
}