<?php namespace Seiger\sLang\Controllers;

use EvolutionCMS\ManagerTheme;
use EvolutionCMS\Models\SiteContent;
use EvolutionCMS\Models\SiteTmplvar;
use EvolutionCMS\Models\SiteTmplvarContentvalue;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Seiger\sLang\Facades\sLang;
use Seiger\sLang\Models\sLangContent;
use Seiger\sLang\Models\sLangTmplvarContentvalue;
use Seiger\sLang\Models\sLangTranslate;

class sLangController
{
    public $tblLang = '';

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
     * Render tabs resource
     *
     * @param array $params Additional parameters for rendering the tabs view
     * @return View The rendered tabs view
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
     * Prepare fields for content
     *
     * @param array $content Array containing the content data
     * @return array Prepared array containing the content data with language-specific fields and menu values
     */
    public function prepareFields(array $content): array
    {
        $contentLang = [];

        foreach (sLang::langConfig() as $langConfig) {
            foreach (sLang::siteContentFields() as $siteContentField) {
                $contentLang[$langConfig . '_' . $siteContentField] = '';
            }
        }

        $translates = sLangContent::withoutGlobalScope('language')
            ->whereResource($content['id'] ?? 0)
            ->get()
            ->toArray();

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
     * Set language content for a resource
     *
     * @param int $resourceId The ID of the resource
     * @param string $langKey The language key
     * @param array $fields An associative array of fields and their values to update or create
     * @return void
     */
    public function setLangContent(int $resourceId, string $langKey, array $fields): void
    {
        sLangContent::withoutGlobalScope('language')
            ->updateOrCreate(['resource' => $resourceId, 'lang' => $langKey], $fields);
    }

    /**
     * Sets the content value for multiple template variables in a specific language.
     *
     * @param int $resourceId The ID of the resource.
     * @param string $langKey The language key.
     * @param array $fields An associative array where the key is the template variable ID and the value is the content value.
     *
     * @return void
     */
    public function setLangTmplvarContentvalue(int $resourceId, string $langKey, array $fields): void
    {
        foreach ($fields as $tmplvarId => $value) {
            if ($langKey === sLang::langDefault()) {
                SiteTmplvarContentvalue::updateOrCreate(['tmplvarid' => $tmplvarId, 'contentid' => $resourceId], ['value' => $value]);
            }
            sLangTmplvarContentvalue::updateOrCreate(['tmplvarid' => $tmplvarId, 'contentid' => $resourceId, 'lang' => $langKey], ['value' => $value]);
        }
    }

    /**
     * Retrieve translations from the database
     *
     * Returns a paginated collection of translations based on the provided search keyword, if present.
     * If no search keyword is provided, all translations are retrieved.
     * Translations are ordered by descending `tid` (translation ID).
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
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
     * Set the default language
     *
     * @param string $value The value to set as the default language.
     * @return bool Returns true if the default language was successfully set; otherwise, returns false.
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
     * Set the default language show value
     *
     * @param int $value The new value for the default language show
     * @return bool Returns true if the update of the table setting is successful, false otherwise
     */
    public function setLangDefaultShow($value)
    {
        $value = (int)$value;

        return $this->updateTblSetting('s_lang_default_show', $value);
    }

    /**
     * Set the language configuration
     *
     * @param mixed $value The value to set for the language configuration
     * @return bool Returns true if the language configuration was successfully set, otherwise false
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
     * Set the frontend languages in the system configuration
     *
     * @param mixed $value An array of language codes or a single language code
     * @return bool True if the frontend languages were successfully updated, false otherwise
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
     * Sets the language TV values for the current instance.
     *
     * @param mixed $value The value(s) to set as language TV(s).
     *
     * @return bool True if the language TV values were successfully set, false otherwise.
     */
    public function setLangTvs($value)
    {
        $templateVariables = sLang::templateVariablesId();

        if ($templateVariables) {
            $multilangTvs = [];
            $templateVariables = $templateVariables->toArray();
            if (is_array($value)) {
                $multilangTvs = array_filter($value, function ($var) use ($templateVariables) {
                    return in_array($var, $templateVariables) ? true : false;
                });
            }
            $multilangTvs = implode(',', $multilangTvs);

            return $this->updateTblSetting('s_lang_tvs', $multilangTvs);
        }
        return false;
    }

    /**
     * Set the status of the language module
     *
     * @param int $value The value indicating the status of the language module (0 for off, 1 for on)
     * @return bool Whether the update to the "s_lang_enable" field was successful
     */
    public function setOnOffLangModule($value)
    {
        $value = (int)$value;

        return $this->updateTblSetting('s_lang_enable', $value);
    }

    /**
     * Modify translation tables and files
     *
     * This method modifies the translation tables and files based on the language configuration.
     * It adds missing language columns to the translation table and creates empty JSON files for missing languages.
     * After making the necessary modifications, it clears the cache.
     *
     * @return bool True if the tables and files were successfully modified, false otherwise.
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
     * Parse blade views to extract translations and add them to the database if necessary
     *
     * @return void
     */
    public function parseBlade(): void
    {
        $list = [];
        $langDefault = sLang::langDefault();
        if (is_dir(MODX_BASE_PATH . 'views')) {
            $views = Storage::disk('public')->allFiles('views');

            if (is_array($views) && count($views)) {
                foreach ($views as $view) {
                    if (Str::of($view)->contains('.blade.')) {
                        $data = file_get_contents(MODX_BASE_PATH . $view);
                        preg_match_all("/@lang\('\K.+?(?='\))/", $data, $match);

                        if (is_array($match) && is_array($match[0]) && count($match[0])) {
                            foreach ($match[0] as $item) {
                                if (!Str::of($item)->contains('::')) {
                                    if (!str_starts_with($item, 'global.')) {
                                        $list[] = str_replace(["@lang('", "')"], '', $item);
                                    }
                                }
                            }
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
     * Set automatic translation for a phrase
     *
     * @param string $source The source phrase to translate
     * @param string $target The target language to translate into
     * @return string The translated phrase
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
     * Updates the translation for a specific phrase.
     *
     * @param string $source The source phrase to update.
     * @param string $target The translation field to update.
     * @param string $value The new translation value.
     *
     * @return bool True if the translation was successfully updated, false otherwise.
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
     * Saves the translation for a specific phrase.
     *
     * @param array $data An array containing the translation data.
     *                    The array structure should be as follows:
     *                    [
     *                        'translate' => [
     *                            'key' => 'the phrase key',
     *                            'field1' => 'the translation value for field1',
     *                            'field2' => 'the translation value for field2',
     *                            ...
     *                        ]
     *                    ]
     *
     * @return array|null The updated element row if the translation was successfully saved,
     *                   null otherwise.
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
     * Renders a view with optional data.
     *
     * @param string $tpl The name of the template to render.
     * @param array $data Optional data to pass to the template.
     *
     * @return Illuminate\View\View The rendered view.
     */
    public function view(string $tpl, array $data = [])
    {
        return \View::make('sLang::'.$tpl, $data);
    }

    /**
     * Generates the HTML table row for a data element.
     *
     * @param object $data The data element to generate the row for.
     *
     * @return string The HTML table row.
     */
    protected function getElementRow($data)
    {
        global $_lang;
        if (is_file(sLang::basePath() . 'lang/' . evo()->getConfig('manager_language', 'uk') . '.php')) {
            require_once sLang::basePath() . 'lang/' . evo()->getConfig('manager_language', 'uk') . '.php';
        }

        $html = '<tr><td>'.$data->key.'</td>';
        foreach(sLang::langConfig() as $langConfig) {
            $html .= '<td data-tid="'.$data->tid.'" data-lang="'.$langConfig.'">';
            if ($langConfig == sLang::langDefault()) {
                $html .= '<input type="text" class="form-control" name="sLang['.$data->tid.']['.$langConfig.']" value="'.$data->{$langConfig}.'" />';
            } else {
                $html .= '<div class="input-group">';
                $html .= '<input type="text" class="form-control" name="sLang['.$data->tid.']['.$langConfig.']" value="'.$data->{$langConfig}.'" />';
                $html .= '<span class="input-group-btn">';
                $html .= '<button class="btn btn-light js_translate" type="button" title="'.__('auto_translate').' '.strtoupper(sLang::langDefault()).' => '.strtoupper($langConfig).'" style="padding:0 5px;color:#0057b8;">';
                $html .= '<i class="fa fa-language" style="font-size:xx-large;"></i>';
                $html .= '</button></span></div>';
            }
            $html .= '</td>';
        }
        $html .= '</tr>';

        return $html;
    }

    /**
     * Retrieves the HTML output of the template variables for a specific content.
     *
     * @param array $params The parameters for retrieving the template variables.
     *
     * @return string The HTML output of the template variables.
     */
    protected function getTvsHtml($params)
    {
        global $_lang, $_style, $content, $richtexteditorIds, $richtexteditorOptions;;
        $id = (int)$params['id'];

        $group_tvs = evo()->getConfig('group_tvs');
        $templateVariablesOutput = '';
        $templateVariablesGeneral = '';
        $templateVariablesTmp = '';
        $templateVariablesLng = [];
        $templateVariablesTab = [];
        $templateVariablesDefaultValue = [];
        $templateVariablesRichtextEditor = [];
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
            $tvs = $tvs->orderBy('site_tmplvars.rank', 'ASC');
            $tvs = $tvs->orderBy('site_tmplvar_templates.rank', 'ASC');
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
                            $templateVariablesTmp .= '<tr><td colspan="2"><div class="split"></div></td></tr>' . "\n";
                        }
                    } elseif ($i) {
                        $templateVariablesTmp .= '<tr><td colspan="2"><div class="split"></div></td></tr>' . "\n";
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
                        if (sLang::isMultilangTv($row['id'])) {
                            $tabs = sLang::langConfig();
                            foreach ($tabs as $tab) {
                                $temp_row = $row;
                                $temp_row['id'] = $temp_row['id']."_".$tab;
                                $tv_lang_value = sLangTmplvarContentvalue::query()
                                    ->where('lang', $tab)
                                    ->where('tmplvarid', $row['id'])
                                    ->where('contentid', $id)
                                    ->value('value');
                                if ($tab === sLang::langDefault()) {
                                    if (!$tv_lang_value && $temp_row['value']) {
                                        $tv_lang_value = $temp_row['value'];
                                        $templateVariablesDefaultValue[$row['id']] = $tv_lang_value;
                                    } else {
                                        $templateVariablesDefaultValue[$row['id']] = $tv_lang_value;
                                    }
                                }
                                $tvPBV = $tv_lang_value;
                                $temp_row['value'] = $tvPBV;
                                if ($row['type'] == 'richtext') {
                                    // Add richtext editor to the list
                                    $richtexteditorIds[evo()->getConfig('which_editor')][] = "tv" . $temp_row['id'];
                                    $richtexteditorOptions[evo()->getConfig('which_editor')]["tv" . $temp_row['id']] = '';
                                }
                                $templateVariablesTab[$tab][] = $this->view('partials.tvResource', [
                                    '_lang' => $_lang,
                                    '_style' => $_style,
                                    'row' => $temp_row,
                                    'tvPBV' => $tvPBV,
                                    'tvsArray' => $tvsArray,
                                    'content' => $content,
                                ])->render();
                            }
                        } else {
                            if ($row['type'] == 'richtext') {
                                // Add richtext editor to the list
                                $richtexteditorIds[evo()->getConfig('which_editor')][] = "tv" . $row['id'];
                                $richtexteditorOptions[evo()->getConfig('which_editor')]["tv" . $row['id']] = '';
                            }
                            $templateVariablesTab['default'][] = $this->view('partials.tvResource', [
                                '_lang' => $_lang,
                                '_style' => $_style,
                                'row' => $row,
                                'tvPBV' => $tvPBV,
                                'tvsArray' => $tvsArray,
                                'content' => $content,
                            ])->render();
                        }
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
                        $templateVariables .= "</div>\n";
                    } else if ($group_tvs == 2 || $group_tvs == 4) {
                        $templateVariables .= "</div></div></div>\n";
                    } else if ($group_tvs == 3) {
                        $templateVariables .= "</div></div>\n";
                    }
                }
                $templateVariables .= '<!-- end Template Variables -->' . "\n";

                if (count($templateVariablesTab)) {
                    foreach ($templateVariablesTab as $id => $tab) {
                        $templateVariablesTab[$id] = implode('', $templateVariablesTab[$id]);
                    }
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
            'templateVariablesDefaultValue' => $templateVariablesDefaultValue,
            'templateVariables' => $templateVariables
        ];
    }

    /**
     * Updates the value of a specific setting in the database table 'system_settings'.
     *
     * @param string $name The name of the setting to update.
     * @param string $value The new value to be set for the setting.
     *
     * @return bool True if the setting was successfully updated, false otherwise.
     */
    protected function updateTblSetting($name, $value)
    {
        $tbl = evo()->getDatabase()->getFullTableName('system_settings');

        return evo()->getDatabase()->query("REPLACE INTO {$tbl} (`setting_name`, `setting_value`) VALUES ('{$name}', '{$value}')");
    }

    /**
     * Updates the language files based on the current translations.
     *
     * @return void
     */
    protected function updateLangFiles(): void
    {
        foreach (sLang::langConfig() as &$lang) {
            $json = sLangTranslate::all()->pluck($lang, 'key')->toJson();
            file_put_contents(MODX_BASE_PATH . 'core/lang/' . $lang . '.json', $json);
        }
    }
}
