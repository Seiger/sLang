<?php namespace Seiger\sLang\Controllers;

use EvolutionCMS\ManagerTheme;
use EvolutionCMS\Models\SiteContent;
use EvolutionCMS\Models\SiteTmplvar;
use EvolutionCMS\Models\SiteTmplvarContentvalue;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Seiger\sLang\Facades\sLang;
use Seiger\sLang\Models\sLangContent;
use Seiger\sLang\Models\sLangTranslate;

class sLangController
{
    /**
     * Show tabs module
     *
     * @return View
     */
    public function index(): View
    {
        return $this->view('index');
    }

    /**
     * Show tabs resource
     *
     * @return View
     */
    public function tabs($params = []): View
    {
        global $_lang, $_style, $content;

        $data['theme'] = new ManagerTheme(evo(), evo()->getConfig('manager_theme', 'default'));

        $data['_style'] = [];
        if (is_file($data['theme']->getThemeDir(true) . 'style.php')) {
            include $data['theme']->getThemeDir(true) . 'style.php';
            $data['_style'] = $_style;
        }

        $data['richtexteditorIds'] = [evo()->getConfig('which_editor') => []];
        $data['richtexteditorOptions'] = [evo()->getConfig('which_editor') => []];
        $data['content'] = $content;
        $data = array_merge($data, $this->getTvsHtml($params));

        return $this->view('tabs', $data);
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

        foreach (sLang::langConfig() as $langConfig) {
            foreach (sLang::siteContentFields() as $siteContentField) {
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
            foreach (sLang::siteContentFields() as $siteContentField) {
                $contentLang[sLang::langDefault() . '_' . $siteContentField] = (string)($content[$siteContentField] ?? '');
            }
        }

        $contentMenu['menu_main'] = 0;
        $tv = SiteTmplvar::whereName('menu_main')->first();
        if ($tv) {
            $value = SiteTmplvarContentvalue::where('tmplvarid', $tv->id)->where('contentid', ($content['id'] ?? 0))->first();
            $contentMenu['menu_main'] = $value->value ?? 0;
        }

        $contentMenu['menu_footer'] = 0;
        $tv = SiteTmplvar::whereName('menu_footer')->first();
        if ($tv) {
            $value = SiteTmplvarContentvalue::where('tmplvarid', $tv->id)->where('contentid', ($content['id'] ?? 0))->first();
            $contentMenu['menu_footer'] = $value->value ?? 0;
        }

        return array_merge($content, $contentLang, $contentMenu);
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
        $tbl = evo()->getDatabase()->getFullTableName('s_lang_translates');
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
     * Get html element for TV parameters for Resource in adminpanel
     */
    protected function getTvsHtml($params)
    {
        global $_lang, $_style, $content;
        $id = (int)$params['id'];

        $group_tvs = evo()->getConfig('group_tvs');
        $templateVariablesOutput = '';
        $templateVariablesGeneral = '';
        $templateVariablesTmp = '';
        $templateVariablesLng = [];
        $templateVariablesTab = [];
        $templateVariables = '';

        if (($content['type'] == 'document' || evo()->getManagerApi()->action == '4') || ($content['type'] == 'reference' || evo()->getManagerApi()->action == 72)) {
            $template = getDefaultTemplate();
            if (isset ($_REQUEST['newtemplate'])) {
                $template = $_REQUEST['newtemplate'];
            } else {
                if (isset ($content['template'])) {
                    $template = $content['template'];
                }
            }
            $tvs = SiteTmplvar::query()->select('site_tmplvars.*', 'site_tmplvar_contentvalues.value', 'site_tmplvar_templates.rank as tvrank', 'site_tmplvar_templates.rank', 'site_tmplvars.id', 'site_tmplvars.rank')
                ->join('site_tmplvar_templates', 'site_tmplvar_templates.tmplvarid', '=', 'site_tmplvars.id')
                ->leftJoin('site_tmplvar_contentvalues', function ($join) use ($id) {
                    $join->on('site_tmplvar_contentvalues.tmplvarid', '=', 'site_tmplvars.id');
                    $join->on('site_tmplvar_contentvalues.contentid', '=', \DB::raw($id));
                })->leftJoin('site_tmplvar_access', 'site_tmplvar_access.tmplvarid', '=', 'site_tmplvars.id');

            if ($group_tvs) {
                $tvs = $tvs->select('site_tmplvars.*',
                    'site_tmplvar_contentvalues.value', 'categories.id as category_id', 'categories.category as category_name', 'categories.rank as category_rank', 'site_tmplvar_templates.rank', 'site_tmplvars.id', 'site_tmplvars.rank');
                $tvs = $tvs->leftJoin('categories', 'categories.id', '=', 'site_tmplvars.category');
                //$sort = 'category_rank,category_id,' . $sort;
                $tvs = $tvs->orderBy('category_rank', 'ASC');
                $tvs = $tvs->orderBy('category_id', 'ASC');
            }
            $tvs = $tvs->orderBy('site_tmplvar_templates.rank', 'ASC');
            $tvs = $tvs->orderBy('site_tmplvars.rank', 'ASC');
            $tvs = $tvs->orderBy('site_tmplvars.id', 'ASC');
            $tvs = $tvs->where('site_tmplvar_templates.templateid', $template);

            if ($_SESSION['mgrRole'] != 1) {
                $tvs = $tvs->leftJoin('document_groups', 'site_tmplvar_contentvalues.contentid', '=', 'document_groups.document');
                $tvs = $tvs->where(function ($query) {
                    $query->whereNull('site_tmplvar_access.documentgroup')
                        ->orWhereIn('document_groups.document_group', $_SESSION['mgrDocgroups']);
                });
            }

            $tvs = $tvs->get();
            if (count($tvs) > 0) {
                $tvsArray = $tvs->toArray();

                $i = $ii = 0;
                $tab = '';
                foreach ($tvsArray as $row) {
                    $row['category'] = $row['category_name'] ?? '';
                    if (!isset($row['category_id'])) {
                        $row['category_id'] = 0;
                        $row['category'] = $_lang['no_category'];
                        $row['category_rank'] = 0;
                    }
                    if($row['value'] == '') {
                        $row['value'] = $row['default_text'];
                    }
                    if ($group_tvs && $row['category_id'] != 0) {
                        $ii = 0;
                        if ($tab !== $row['category_id']) {
                            if ($group_tvs == 1 || $group_tvs == 3) {
                                if ($i === 0) {
                                    $templateVariablesOutput .= '
                            <div class="tab-section" id="tabTV_' . $row['category_id'] . '">
                                <div class="tab-header">' . $row['category'] . '</div>
                                <div class="tab-body tmplvars">
                                    <table>' . "\n";
                                } else {
                                    $templateVariablesOutput .= '
                                    </table>
                                </div>
                            </div>

                            <div class="tab-section" id="tabTV_' . $row['category_id'] . '">
                                <div class="tab-header">' . $row['category'] . '</div>
                                <div class="tab-body tmplvars">
                                    <table>';
                                }
                            } else if ($group_tvs == 2 || $group_tvs == 4) {
                                if ($i === 0) {
                                    $templateVariablesOutput .= '
                            <div id="tabTV_' . $row['category_id'] . '" class="tab-page tmplvars">
                                <h2 class="tab">' . $row['category'] . '</h2>
                                <script type="text/javascript">tpTemplateVariables.addTabPage(document.getElementById(\'tabTV_' . $row['category_id'] . '\'));</script>

                                <div class="tab-body tmplvars">
                                    <table>';
                                } else {
                                    $templateVariablesOutput .= '
                                    </table>
                                </div>
                            </div>

                            <div id="tabTV_' . $row['category_id'] . '" class="tab-page tmplvars">
                                <h2 class="tab">' . $row['category'] . '</h2>
                                <script type="text/javascript">tpTemplateVariables.addTabPage(document.getElementById(\'tabTV_' . $row['category_id'] . '\'));</script>

                                <div class="tab-body tmplvars">
                                    <table>';
                                }
                            } else if ($group_tvs == 5) {
                                if ($i === 0) {
                                    $templateVariablesOutput .= '
                                <div id="tabTV_' . $row['category_id'] . '" class="tab-page tmplvars">
                                    <h2 class="tab">' . $row['category'] . '</h2>
                                    <script type="text/javascript">tpSettings.addTabPage(document.getElementById(\'tabTV_' . $row['category_id'] . '\'));</script>
                                    <table>';
                                } else {
                                    $templateVariablesOutput .= '
                                    </table>
                                </div>

                                <div id="tabTV_' . $row['category_id'] . '" class="tab-page tmplvars">
                                    <h2 class="tab">' . $row['category'] . '</h2>
                                    <script type="text/javascript">tpSettings.addTabPage(document.getElementById(\'tabTV_' . $row['category_id'] . '\'));</script>

                                    <table>';
                                }
                            }
                            $split = 0;
                        } else {
                            $split = 1;
                        }
                    }

                    // Go through and display all Template Variables
                    if ($row['type'] == 'richtext' || $row['type'] == 'htmlarea') {
                        // determine TV-options
                        $tvOptions = EvolutionCMS()->parseProperties($row['elements']);
                        if (!empty($tvOptions)) {
                            // Allow different Editor with TV-option {"editor":"CKEditor4"} or &editor=Editor;text;CKEditor4
                            $editor = isset($tvOptions['editor']) ? $tvOptions['editor'] : EvolutionCMS()->getConfig('which_editor');
                        };
                        // Add richtext editor to the list
                        $richtexteditorIds[$editor][] = "tv" . $row['id'];
                        $richtexteditorOptions[$editor]["tv" . $row['id']] = $tvOptions;
                    }

                    // splitter
                    if ($group_tvs) {
                        if ((! empty($split) && $i) || $ii) {
                            $templateVariablesTmp .= '
                                            <tr><td colspan="2"><div class="split"></div></td></tr>' . "\n";
                        }
                    } elseif ($i) {
                        $templateVariablesTmp .= '
                                        <tr><td colspan="2"><div class="split"></div></td></tr>' . "\n";
                    }

                    // post back value
                    if (array_key_exists('tv' . $row['id'], $_POST)) {
                        if (is_array($_POST['tv' . $row['id']])) {
                            $tvPBV = implode('||', $_POST['tv' . $row['id']]);
                        } else {
                            $tvPBV = $_POST['tv' . $row['id']];
                        }
                    } else {
                        $tvPBV = $row['value'];
                    }

                    $tvLng = explode('_', $row['name']);
                    if (in_array(end($tvLng), sLang::langConfig())) {
                        $templateVariablesLng[end($tvLng)][] = $this->view('partials.tvResource', [
                            '_lang' => $_lang,
                            '_style' => $_style,
                            'row' => $row,
                            'tvPBV' => $tvPBV,
                            'tvsArray' => $tvsArray,
                            'content' => $content,
                        ])->render();
                    } else {
                        $templateVariablesTab[] = $this->view('partials.tvResource', [
                            '_lang' => $_lang,
                            '_style' => $_style,
                            'row' => $row,
                            'tvPBV' => $tvPBV,
                            'tvsArray' => $tvsArray,
                            'content' => $content,
                        ])->render();
                    }

                    if ($group_tvs && $row['category_id'] == 0) {
                        $templateVariablesGeneral .= $templateVariablesTmp;
                        $ii++;
                    } else {
                        $templateVariablesOutput .= $templateVariablesTmp;
                        $tab = $row['category_id'];
                        $i++;
                    }
                }

                if ($templateVariablesGeneral) {
                    echo '<table id="tabTV_0" class="tmplvars"><tbody>' . $templateVariablesGeneral . '</tbody></table>';
                }

                $templateVariables .= '<!-- Template Variables -->' . "\n";

                if (count($templateVariablesLng)) {
                    foreach ($templateVariablesLng as $lng => $item) {
                        array_unshift($templateVariablesLng[$lng], '<!-- Template Variables -->' . "\n");
                    }
                }

                if (!$group_tvs) {
                    $str = '<div class="sectionHeader" id="tv_header">' . $_lang['settings_templvars'] . '</div><div class="sectionBody tmplvars">';
                    $templateVariables .= $str;

                    if (count($templateVariablesLng)) {
                        foreach ($templateVariablesLng as $lng => $item) {
                            $templateVariablesLng[$lng][0] .= $str;
                        }
                    }
                } else if ($group_tvs == 2) {
                    $templateVariables .= '
                    <div class="tab-section">
                        <div class="tab-header" id="tv_header">' . $_lang['settings_templvars'] . '</div>
                        <div class="tab-pane" id="paneTemplateVariables">
                            <script type="text/javascript">
                                tpTemplateVariables = new WebFXTabPane(document.getElementById(\'paneTemplateVariables\'), ' . (EvolutionCMS()->getConfig('remember_last_tab') ? 'true' : 'false') . ');
                            </script>';
                } else if ($group_tvs == 3) {
                    $templateVariables .= '
                        <div id="templateVariables" class="tab-page tmplvars">
                            <h2 class="tab">' . $_lang['settings_templvars'] . '</h2>
                            <script type="text/javascript">tpSettings.addTabPage(document.getElementById(\'templateVariables\'));</script>';
                } else if ($group_tvs == 4) {
                    $templateVariables .= '
                    <div id="templateVariables" class="tab-page tmplvars">
                        <h2 class="tab">' . $_lang['settings_templvars'] . '</h2>
                        <script type="text/javascript">tpSettings.addTabPage(document.getElementById(\'templateVariables\'));</script>
                        <div class="tab-pane" id="paneTemplateVariables">
                            <script type="text/javascript">
                                tpTemplateVariables = new WebFXTabPane(document.getElementById(\'paneTemplateVariables\'), ' . (EvolutionCMS()->getConfig('remember_last_tab') ? 'true' : 'false') . ');
                            </script>';
                }

                if ($templateVariablesOutput) {
                    $templateVariables .= $templateVariablesOutput;
                    $templateVariables .= '</div>' . "\n";

                    if (count($templateVariablesLng)) {
                        foreach ($templateVariablesLng as $lng => $item) {
                            array_push($templateVariablesLng[$lng], '</div>' . "\n");
                        }
                    }
                    if ($group_tvs == 1) {
                        $templateVariables .= '
                            </div>' . "\n";
                    } else if ($group_tvs == 2 || $group_tvs == 4) {
                        $templateVariables .= '
                            </div>
                        </div>
                    </div>' . "\n";
                    } else if ($group_tvs == 3) {
                        $templateVariables .= '
                            </div>
                        </div>' . "\n";
                    }
                }
                $templateVariables .= '<!-- end Template Variables -->' . "\n";

                if (count($templateVariablesTab)) {
                    $templateVariablesTab = implode('', $templateVariablesTab);
                }
                if (count($templateVariablesLng)) {
                    foreach ($templateVariablesLng as $lng => $item) {
                        $templateVariablesLng[$lng] = implode('', $item) . '<!-- end Template Variables -->' . "\n";
                    }
                }
            }
        }

        return [
            'group_tvs' => $group_tvs,
            'templateVariablesOutput' => $templateVariablesOutput,
            'templateVariablesGeneral' => $templateVariablesGeneral,
            'templateVariablesLng' => $templateVariablesLng,
            'templateVariablesTab' => $templateVariablesTab,
            'templateVariables' => $templateVariables
        ];
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