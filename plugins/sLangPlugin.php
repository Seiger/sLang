<?php
/**
 * Plugin for Seiger Lang Management Module for Evolution CMS admin panel.
 */

use EvolutionCMS\Facades\UrlProcessor;
use EvolutionCMS\Models\SiteContent;
use Seiger\sLang\Facades\sLang;

/**
 * Parse custom lang placeholders
 */
Event::listen('evolution.OnParseDocument', function($params) {
    $base_url = UrlProcessor::makeUrl(evo()->getConfig('site_start', 1), '', '', 'full');

    // parse id as number
    evo()->documentOutput = str_replace('[*id*]', (evo()->documentObject['id'] ?? evo()->getConfig('site_start', 1)), evo()->documentOutput);

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
 * Filling in the fields when opening a resource in the admin panel
 */
/*if ($e->name == 'OnDocFormPrerender') {
    global $content;
    $sLang  = new sLang();
    $content = $sLang->prepareFields($content);
}

/**
 * Modifying fields before saving a resource
 */
/*if ($e->name == 'OnBeforeDocFormSave') {
    if (empty($e->params['id'])) {
        $id = collect(DB::select("
            SELECT AUTO_INCREMENT
            FROM `information_schema`.`tables`
            WHERE `table_name` = '".evo()->getDatabase()->getFullTableName('site_content')."'"))
            ->pluck('AUTO_INCREMENT')
            ->first();
        $e->params['id'] = $id;
    }

    $sLang  = new sLang();

    foreach ($sLang->langConfig() as $langConfig) {
        $fields = [];
        foreach (request()->all() as $key => $value) {
            if (str_starts_with($key, $langConfig.'_')) {
                $keyName = str_replace($langConfig.'_', '', $key);
                $fields[$keyName] = $value;
                unset($_REQUEST[$key]);
            }
        }

        if (count($fields)) {
            $sLang->setLangContent($e->params['id'], $langConfig, $fields);
        }
    }
}

/**
 * Alias generation
 */
/*if ($e->name == 'OnDocFormSave') {
    if (isset($e->params['id']) && !empty($e->params['id'])) {
        $sLang  = new sLang();
        $langDefault = $sLang->langDefault();
        $data = [];

        foreach (request()->all() as $key => $value) {
            if (str_starts_with($key, $sLang->langDefault().'_')) {
                $keyName = str_replace($sLang->langDefault().'_', '', $key);
                $data[$keyName] = evo()->getDatabase()->escape($value);
            }
        }

        if (request()->has('alias') && !trim(request('alias')) && request()->has('en_pagetitle')) {
            $alias = strtolower(evolutionCMS()->stripAlias(trim(request('en_pagetitle'))));
            if (SiteContent::withTrashed()
                    ->where('id', '<>', $id)
                    ->where('alias', $alias)->count() > 0) {
                $cnt = 1;
                $tempAlias = $alias;
                while (SiteContent::withTrashed()
                        ->where('id', '<>', $id)
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
            evo()->db->update($data, evo()->getDatabase()->getFullTableName('site_content'), 'id=' . $e->params['id']);
        }
    }
}

/**
 * Replacing standard fields with multilingual frontend
 */
/*if ($e->name == 'OnAfterLoadDocumentObject') {
    $sLang  = new sLang();
    $lang = evo()->getLocale();

    $langContentField = $sLang->getLangContent($e->params['documentObject']['id'], $lang);

    if (count($langContentField)) {
        foreach ($sLang->siteContentFields as $siteContentField) {
            $e->params['documentObject'][$siteContentField] = $langContentField[$siteContentField];
        }
    }

    evo()->documentObject = $e->params['documentObject'];
}

/**
 * Parameterization of the current language
 */
Event::listen('evolution.OnPageNotFound', function($params) {
    if (!isset($params['have-redirect'])) {
        $hash = '';
        $identifier = evo()->getConfig('error_page', 1);
        $langDefault = sLang::langDefault();

        if (isset($_SERVER['REQUEST_URI'])) {
            $url = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'), 2);

            if (trim($url[0])) {
                if ($url[0] == $langDefault && evo()->config['s_lang_default_show'] != 1) {
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
        evo()->config['lang'] = $langDefault;

        if (evo()->config['s_lang_default'] != $langDefault || evo()->config['s_lang_default_show'] == 1) {
            evo()->config['base_url'] .= $langDefault . '/';
        }

        if (!isset($_SERVER['REQUEST_URI']) || !trim($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == '/') {
            $identifier = evo()->getConfig('site_start', 1);
        } else {
            $q = trim($_SERVER['REQUEST_URI'], '/');
            $hash = '_' . md5(serialize($q));
            $path = explode('?', $q);
            $path = trim($path[0], '/');
            if (array_key_exists($path, UrlProcessor::getFacadeRoot()->documentListing)) {
                $identifier = UrlProcessor::getFacadeRoot()->documentListing[$path];
            }
        }

        evo()->systemCacheKey = $identifier . '_' . $langDefault . $hash;

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
                evo()->invokeEvent('OnPageNotFound', ['have-redirect' => 1]);
            }
        }

        evo()->invokeEvent('OnWebPageInit', ['lang' => $langDefault]);
        evo()->sendForward($identifier);
        exit();
    }
});

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
    evo()->config['lang'] = $langDefault;
});
