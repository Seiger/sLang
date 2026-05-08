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
    // parse id as number
    evo()->documentOutput = str_replace('[*id*]', (evo()->documentObject['id'] ?? evo()->getConfig('site_start', 1)), evo()->documentOutput);

    preg_match_all("/@lang\(['|\"](.*?)['|\"]\)/", evo()->documentOutput, $match);

    if (is_file($file = EVO_BASE_PATH . 'core/lang/' . evo()->getLocale() . '.json')) {
        $translates = json_decode(file_get_contents($file), true);

        foreach ($match[0] as $key => $value) {
            evo()->documentOutput = str_replace($value, $translates[$match[1][$key]], evo()->documentOutput);
        }
    }

    // parse language urls
    preg_match_all('/\[~~(\d+)~~\]/', evo()->documentOutput, $match);
    if ($match[0]) {
        foreach ($match[0] as $key => $value) {
            $documentId = (int)$match[1][$key];
            $generatedUrl = $documentId === (int)evo()->getConfig('site_start', 1)
                ? '/'
                : UrlProcessor::makeUrl($documentId);

            evo()->documentOutput = str_replace(
                $value,
                sLang::localizeGeneratedUrl((string)$generatedUrl, (string)evo()->getConfig('lang', sLang::langDefault())),
                evo()->documentOutput
            );
        }
    }
});

/**
 * Localize generated internal Evo URLs for non-default frontend languages.
 */
Event::listen('evolution.OnMakeDocUrl', function($params) {
    return sLang::localizeGeneratedUrl((string)($params['url'] ?? ''));
});

/**
 * Replacing standard fields with multilingual frontend
 */
Event::listen('evolution.OnAfterLoadDocumentObject', function($params) {
    $langContentField = sLang::getLangContent($params['documentObject']['id'], evo()->getLocale());
    $langTemplateContentvalueField = sLang::getLangTemplateContentvalue($params['documentObject']['id'], evo()->getLocale());

    if (count($langContentField)) {
        foreach (sLang::siteContentFields() as $siteContentField) {
            $params['documentObject'][$siteContentField] = $langContentField[$siteContentField];
        }
    }

    if (count($langTemplateContentvalueField)) {
        foreach ($langTemplateContentvalueField as $key => $item) {
            if (isset($params['documentObject'][$key])) {
                $params['documentObject'][$key][1] = $item['value'];
            }
        }
    }

    evo()->documentObject = $params['documentObject'];
    return $params['documentObject'];
});

/**
 * Parameterization of the current language
 */
Event::listen('evolution.OnPageNotFound', function() {
    $langDefault = sLang::langDefault();
    $defaultSegment = sLang::langSegment(sLang::langDefault());
    $resolvedLocale = null;
    $resolvedIdentifier = null;
    $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');

    if ($requestUri !== '') {
        $url = explode('/', ltrim($requestUri, '/'), 2);

        if (trim($url[0])) {
            if ($url[0] == $defaultSegment && !sLang::defaultInUrl()) {
                evo()->sendErrorPage(true);
                exit();
            }

            $resolvedLocale = sLang::localeFromSegment($url[0]);
            if (
                (!is_null($resolvedLocale) && in_array($resolvedLocale, sLang::langFront()))
                || (evo()->getLoginUserID('mgr') && !is_null($resolvedLocale) && in_array($resolvedLocale, sLang::langConfig()))
            ) {
                $langDefault = $resolvedLocale;
                $_SERVER['REQUEST_URI'] = preg_replace('/' . $url[0] . '\//', '', $requestUri, 1);
            }
        }
    }

    evo()->setLocale($langDefault);
    evo()->setConfig('lang', $langDefault);

    if (sLang::langDefault() != $langDefault || sLang::defaultInUrl()) {
        evo()->setConfig('base_url', evo()->getConfig('base_url', '/') . sLang::langSegment($langDefault) . '/');
    }

    if ($requestUri !== '') {
        if (!is_null($resolvedLocale) && isset($_REQUEST['q'])) {
            $cleanQuery = trim(sLang::stripLanguageSegmentFromUri('/' . ltrim((string)$_REQUEST['q'], '/'), $resolvedLocale), '/');
            $_REQUEST['q'] = $cleanQuery;

            if (isset($_GET['q'])) {
                $_GET['q'] = $cleanQuery;
            }
        }

        $resolvedIdentifier = sLang::resolveLocalizedIdentifier($requestUri);
    }

    if (!is_null($resolvedIdentifier)) {
        evo()->sendForward($resolvedIdentifier);
        exit();
    }

    if (!is_null($resolvedLocale) && trim(sLang::stripLanguageSegmentFromUri($requestUri, $resolvedLocale), '/') === '') {
        evo()->sendForward((int)evo()->getConfig('site_start', 1));
        exit();
    }
});

/**
 * Make Lang Config
 */
Event::listen('evolution.OnLoadSettings', function($params) {
    $resolvedLocale = null;
    $defaultLocale = sLang::langDefault();

    if (isset($params['lang'])) {
        $langDefault = $params['lang'];
    } else {
        $langDefault = $defaultLocale;

        if (isset($_SERVER['REQUEST_URI'])) {
            $url = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'), 2);

            if (trim($url[0])) {
                $resolvedLocale = sLang::localeFromSegment($url[0]);
                if (
                    !is_null($resolvedLocale)
                    && in_array($resolvedLocale, sLang::langFront())
                    && ($resolvedLocale !== $defaultLocale || sLang::defaultInUrl())
                ) {
                    $langDefault = $resolvedLocale;
                } else {
                    $resolvedLocale = null;
                }
            }
        }
    }

    evo()->setLocale($langDefault);
    evo()->setConfig('lang', $langDefault);

    if (sLang::langDefault() != $langDefault || sLang::defaultInUrl()) {
        evo()->setConfig('base_url', evo()->getConfig('base_url', '/') . sLang::langSegment($langDefault) . '/');
    }

    if (!is_null($resolvedLocale) && isset($_REQUEST['q'])) {
        $cleanQuery = trim(sLang::stripLanguageSegmentFromUri('/' . ltrim((string)$_REQUEST['q'], '/'), $resolvedLocale), '/');
        $_REQUEST['q'] = $cleanQuery;

        if (isset($_GET['q'])) {
            $_GET['q'] = $cleanQuery;
        }
    }

    if (!is_null($resolvedLocale)) {
        // Evolution's core seostrict canonicalization is not locale-aware and
        // strips custom language segments from nested frontend routes.
        evo()->setConfig('seostrict', 0);
    }
});

/**
 * Enforce one canonical frontend route per localized page.
 */
Event::listen('evolution.OnWebPageInit', function() {
    if (evo()->getLoginUserID('mgr')) {
        return;
    }

    $sendCanonical404 = static function() {
        header('HTTP/1.0 404 Not Found', true, 404);
        evo()->documentIdentifier = (int)evo()->getConfig('error_page', 1);
        evo()->documentMethod = 'id';
        evo()->prepareResponse();
        exit();
    };

    $path = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    $path = '/' . ltrim($path, '/');
    $segments = array_values(array_filter(array_map('trim', explode('/', trim($path, '/')))));
    $firstSegment = $segments[0] ?? '';
    $resolvedLocale = $firstSegment !== '' ? sLang::localeFromSegment($firstSegment) : null;
    $defaultLocale = sLang::langDefault();
    $defaultInUrl = sLang::defaultInUrl();
    $siteStart = (int)evo()->getConfig('site_start', 1);
    $documentIdentifier = (int)evo()->documentIdentifier;
    $configuredLocales = array_values(array_unique(array_filter(array_merge(
        sLang::langConfig(),
        sLang::langFront(),
        [$defaultLocale]
    ))));

    if ($firstSegment === '') {
        if ($defaultInUrl) {
            $sendCanonical404();
        }

        return;
    }

    if (!is_null($resolvedLocale)) {
        if ($resolvedLocale === $defaultLocale && !$defaultInUrl) {
            $sendCanonical404();
        }

        return;
    }

    if (in_array($firstSegment, $configuredLocales, true)) {
        $sendCanonical404();
    }

    if ($defaultInUrl || $documentIdentifier === $siteStart) {
        $sendCanonical404();
    }
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
        $params['id'] = \DB::table('site_content')->max('id') + 1;
    }

    $sLangController = new sLangController();
    foreach (sLang::langConfig() as $langConfig) {
        $fields = [];
        //$tvs = [];
        foreach (request()->all() as $key => $value) {
            $matches = [];
            if (str_starts_with($key, $langConfig.'_')) {
                $keyName = str_replace($langConfig.'_', '', $key);
                $fields[$keyName] = $value;
                unset($_REQUEST[$key]);
            } /*elseif(preg_match_all('/tv([0-9]*)_'.$langConfig.'$/', $key, $matches)) {
                $keyName = $matches[1][0];
                $tvs[$keyName] = $value;
                unset($_REQUEST[$key]);
            }*/
        }

        if (count($fields)) {
            $sLangController->setLangContent($params['id'], $langConfig, $fields);
        }
        /*if (count($tvs)) {
            $sLangController->setLangTmplvarContentvalue($params['id'], $langConfig, $tvs);
        }*/

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

        $defaultLang = sLang::langDefault();
        if (request()->has('alias') && !trim(request('alias')) && request()->has($defaultLang . '_pagetitle')) {
            $alias = strtolower(evo()->stripAlias(trim(request($defaultLang . '_pagetitle'))));
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

        foreach (sLang::langConfig() as $langConfig) {
            $tvs = [];
            foreach (request()->all() as $key => $value) {
                $matches = [];
                if(preg_match_all('/tv([0-9]*)_'.$langConfig.'$/', $key, $matches)) {
                    $keyName = $matches[1][0];
                    $tvs[$keyName] = $value;
                    unset($_REQUEST[$key]);
                }
            }
            if (count($tvs)) {
                $sLangController->setLangTmplvarContentvalue($params['id'], $langConfig, $tvs);
            }
        }
    }
});
