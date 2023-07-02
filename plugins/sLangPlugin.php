<?php
/**
 * Plugin for Seiger Lang Management Module for Evolution CMS admin panel.
 */

use EvolutionCMS\Facades\UrlProcessor;
use EvolutionCMS\Models\SiteContent;
use EvolutionCMS\Models\SiteTmplvar;
use EvolutionCMS\Models\SiteTmplvarContentvalue;
use Seiger\sLang\Controllers\sLangController;
use Seiger\sLang\Facades\sLang;

/**
 * Parse custom lang placeholders
 */
Event::listen('evolution.OnParseDocument', function($params) {
    $base_url = UrlProcessor::makeUrl(evo()->getConfig('site_start', 1), '', '', 'full');

    // parse id as number
    evo()->documentOutput = str_replace('[*id*]', (evo()->documentObject['id'] ?? evo()->getConfig('site_start', 1)), evo()->documentOutput);

    preg_match_all("/@lang\(['|\"](.*?)['|\"]\)/", evo()->documentOutput, $match);

    if (is_file($file = MODX_BASE_PATH . 'core/lang/' . evo()->getLocale() . '.json')) {
        $translates = json_decode(file_get_contents($file), true);

        foreach ($match[0] as $key => $value) {
            evo()->documentOutput = str_replace($value, $translates[$match[1][$key]], evo()->documentOutput);
        }
    }

    // parse language urls
    preg_match_all('/\[~~(\d+)~~\]/', evo()->documentOutput, $match);
    if ($match[0]) {
        foreach ($match[0] as $key => $value) {
            if ($match[1][$key] == evo()->getConfig('site_start', 1)) {
                evo()->documentOutput = str_replace($value, $base_url, evo()->documentOutput);
            } else {
                if (evo()->getConfig('lang') != sLang::langDefault()) {
                    evo()->setConfig('virtual_dir', evo()->getConfig('lang').'/');
                }
                evo()->documentOutput = str_replace($value, UrlProcessor::makeUrl($match[1][$key], '', '', 'full'), evo()->documentOutput);
            }
        }
    }
});

/**
 * Replacing standard fields with multilingual frontend
 */
Event::listen('evolution.OnAfterLoadDocumentObject', function($params) {
    $langContentField = sLang::getLangContent($params['documentObject']['id'], evo()->getLocale());

    if (count($langContentField)) {
        foreach (sLang::siteContentFields() as $siteContentField) {
            $params['documentObject'][$siteContentField] = $langContentField[$siteContentField];
        }
    }

    evo()->documentObject = $params['documentObject'];
});

/**
 * Parameterization of the current language
 */
Event::listen('evolution.OnPageNotFound', function($params) {
    if (!isset($params['isRedirected'])) {
        $identifier = evo()->getConfig('error_page', 1);
        $langDefault = sLang::langDefault();

        if (isset($_SERVER['REQUEST_URI'])) {
            $url = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'), 2);

            if (trim($url[0])) {
                if ($url[0] == sLang::langDefault() && evo()->getConfig('s_lang_default_show', 0) != 1) {
                    evo()->sendRedirect(str_replace($url[0] . '/', '', $_SERVER['REQUEST_URI']));
                    die;
                }

                if (in_array($url[0], sLang::langFront()) || (evo()->getLoginUserID('mgr') && in_array($url[0], sLang::langConfig()))) {
                    $langDefault = $url[0];
                    $_SERVER['REQUEST_URI'] = str_replace($url[0] . '/', '', $_SERVER['REQUEST_URI']);
                }
            }
        }

        evo()->setLocale($langDefault);
        evo()->setConfig('lang', $langDefault);

        if (sLang::langDefault() != $langDefault || evo()->getConfig('s_lang_default_show', 0) == 1) {
            evo()->setConfig('base_url', evo()->getConfig('base_url', '/') . $langDefault . '/');
        }

        if (!isset($_SERVER['REQUEST_URI']) || !trim($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == '/') {
            $identifier = evo()->getConfig('site_start', 1);
        } else {
            $q = trim($_SERVER['REQUEST_URI'], '/');
            $path = explode('?', $q);
            $path = trim($path[0], '/');
            if (array_key_exists($path, UrlProcessor::getFacadeRoot()->documentListing)) {
                $identifier = UrlProcessor::getFacadeRoot()->documentListing[$path];
            }
        }

        if ($identifier == evo()->getConfig('error_page', 1) && $identifier != evo()->getConfig('site_start', 1)) {
            if (request()->is('api/*')) {
                $response = [
                    'status_code' => 404,
                    'status' => 'error',
                    'message' => 'Route not found.',
                ];
                header('HTTP/1.0 404 Not Found');
                die(json_encode($response));
            } else {
                Event::until('evolution.OnPageNotFound', [['isRedirected' => true]]);
            }
        }

        Event::until('evolution.OnWebPageInit', [['lang' => $langDefault]]);
        evo()->sendForward($identifier);
        exit();
    }
});

/**
 * Make Lang Config
 */
Event::listen('evolution.OnLoadSettings', function($params) {
    if (isset($params['lang'])) {
        $langDefault = $params['lang'];
    } else {
        $langDefault = sLang::langDefault();

        if (isset($_SERVER['REQUEST_URI'])) {
            $url = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'), 2);

            if (trim($url[0])) {
                if (in_array($url[0], sLang::langFront())) {
                    $langDefault = $url[0];
                }
            }
        }
    }

    evo()->setLocale($langDefault);
    evo()->setConfig('lang', $langDefault);
});
/**
 * @deprecated
 */
Event::listen('evolution.OnWebPageInit', function($params) {
    if (isset($params['lang'])) {
        $langDefault = $params['lang'];
    } else {
        $langDefault = sLang::langDefault();

        if (isset($_SERVER['REQUEST_URI'])) {
            $url = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'), 2);

            if (trim($url[0])) {
                if (in_array($url[0], sLang::langFront())) {
                    $langDefault = $url[0];
                }
            }
        }
    }

    evo()->setLocale($langDefault);
    evo()->setConfig('lang', $langDefault);
});

/**
 * Make page cache ID
 */
Event::listen('evolution.OnMakePageCacheKey', function($params) {
    $q = trim($_SERVER['REQUEST_URI'], '/');
    return (int)$params['id'] . '_' . evo()->getConfig('lang', sLang::langDefault()) . '_' . md5(serialize($q));
});

/**
 * Filling in the fields when opening a resource in the admin panel
 */
Event::listen('evolution.OnDocFormTemplateRender', function($params) {
    global $content;
    $content['parent'] = $content['parent'] ?? 0;
    $sLangController = new sLangController();
    $content = $sLangController->prepareFields($content);
    return $sLangController->tabs($params);
});

/**
 * Modifying fields before saving a resource
 */
Event::listen('evolution.OnBeforeDocFormSave', function($params) {
    if (empty($params['id'])) {
        $id = collect(DB::select("
            SELECT AUTO_INCREMENT
            FROM `information_schema`.`tables`
            WHERE `table_name` = '".evo()->getDatabase()->getFullTableName('site_content')."'"))
            ->pluck('AUTO_INCREMENT')
            ->first();
        $params['id'] = $id;
    }

    $sLangController = new sLangController();

    foreach (sLang::langConfig() as $langConfig) {
        $fields = [];
        foreach (request()->all() as $key => $value) {
            if (str_starts_with($key, $langConfig.'_')) {
                $keyName = str_replace($langConfig.'_', '', $key);
                $fields[$keyName] = $value;
                unset($_REQUEST[$key]);
            }
        }

        if (count($fields)) {
            $sLangController->setLangContent($params['id'], $langConfig, $fields);
        }
    }
});

/**
 * Alias generation and Menu areas
 */
Event::listen('evolution.OnDocFormSave', function($params) {
    if (isset($params['id']) && !empty($params['id'])) {
        $sLangController = new sLangController();
        $data = [];

        foreach (request()->all() as $key => $value) {
            if (str_starts_with($key, sLang::langDefault().'_')) {
                $keyName = str_replace(sLang::langDefault().'_', '', $key);
                $data[$keyName] = evo()->getDatabase()->escape($value);
            }
        }

        if (request()->has('alias') && !trim(request('alias')) && request()->has('en_pagetitle')) {
            $alias = strtolower(evo()->stripAlias(trim(request('en_pagetitle'))));
            if (SiteContent::withTrashed()
                    ->where('id', '<>', $params['id'])
                    ->where('alias', $alias)->count() > 0) {
                $cnt = 1;
                $tempAlias = $alias;
                while (SiteContent::withTrashed()
                        ->where('id', '<>', $params['id'])
                        ->where('alias', $tempAlias)->count() > 0) {
                    $tempAlias = $alias;
                    $tempAlias .= $cnt;
                    $cnt++;
                }
                $alias = $tempAlias;
            }
            $data['alias'] = $alias;
        }

        if (!empty($data)) {
            unset($data['seotitle'], $data['seodescription']);
            evo()->db->update($data, evo()->getDatabase()->getFullTableName('site_content'), 'id=' . $params['id']);
        }

        if (request()->has('menu_main')) {
            $tv = SiteTmplvar::whereName('menu_main')->first();
            if (!$tv) {
                $tv = new SiteTmplvar();
                $tv->type = 'checkbox';
                $tv->name = 'menu_main';
                $tv->caption = 'menu_main';
                $tv->description = 'menu_main';
                $tv->editor_type = '0';
                $tv->category = '1';
                $tv->locked = '1';
                $tv->elements = '==1';
                $tv->default_text = '0';
                $tv->save();
            }

            $value = SiteTmplvarContentvalue::where('tmplvarid', $tv->id)->where('contentid', $params['id'])->firstOrNew();
            $value->tmplvarid = $tv->id;
            $value->contentid = $params['id'];
            $value->value = (int)request()->menu_main;
            $value->save();
        }

        if (request()->has('menu_footer')) {
            $tv = SiteTmplvar::whereName('menu_footer')->first();
            if (!$tv) {
                $tv = new SiteTmplvar();
                $tv->type = 'checkbox';
                $tv->name = 'menu_footer';
                $tv->caption = 'menu_footer';
                $tv->description = 'menu_footer';
                $tv->editor_type = '0';
                $tv->category = '1';
                $tv->locked = '1';
                $tv->elements = '==1';
                $tv->default_text = '0';
                $tv->save();
            }

            $value = SiteTmplvarContentvalue::where('tmplvarid', $tv->id)->where('contentid', $params['id'])->firstOrNew();
            $value->tmplvarid = $tv->id;
            $value->contentid = $params['id'];
            $value->value = (int)request()->menu_footer;
            $value->save();
        }
    }
});
