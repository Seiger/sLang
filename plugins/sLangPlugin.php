<?php
/**
 * Plugin for Seiger Lang Management Module for Evolution CMS admin panel.
 */

use EvolutionCMS\Facades\UrlProcessor;
use EvolutionCMS\Models\SiteContent;
use Illuminate\Http\Request;
use Seiger\sLang\Controllers\sLangController;
use Seiger\sLang\Facades\sLang;
use Seiger\sLang\Support\DocumentTranslations;
use Seiger\sLang\Support\ResourceTranslationAccess;

/**
 * Parse legacy resource IDs and localized URLs.
 */
Event::listen('evolution.OnParseDocument', function($params) {
    // parse id as number
    evo()->documentOutput = str_replace('[*id*]', (evo()->documentObject['id'] ?? evo()->getConfig('site_start', 1)), evo()->documentOutput);

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
 * Render legacy dictionary markers after Evolution has finished processing template tags.
 */
Event::listen('evolution.OnWebPagePrerender', function () {
    $evo = evo();
    $contentType = $evo->contentTypes[$evo->documentIdentifier] ?? 'text/html';
    if (strtolower(trim(explode(';', $contentType, 2)[0])) !== 'text/html'
        || !str_contains($evo->documentOutput, '@lang(')) {
        return;
    }

    $file = EVO_BASE_PATH . 'core/lang/' . $evo->getLocale() . '.json';
    if (!is_file($file)) {
        return;
    }

    $json = file_get_contents($file);
    $dictionary = is_string($json) ? json_decode($json, true) : null;
    $evo->documentOutput = DocumentTranslations::replace($evo->documentOutput, is_array($dictionary) ? $dictionary : []);
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
 * Resolve a localized fallback route using a confirmed first path segment.
 */
Event::listen('evolution.OnPageNotFound', function() {
    $langDefault = sLang::langDefault();
    $resolvedIdentifier = null;
    $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $cleanUri = $requestUri;
    $path = substr($requestUri, 0, strcspn($requestUri, '?#'));
    $firstSegment = explode('/', ltrim($path, '/'), 2)[0];
    $resolvedLocale = $firstSegment !== '' ? sLang::localeFromSegment($firstSegment) : null;

    if ($resolvedLocale === $langDefault && !sLang::defaultInUrl()) {
        evo()->sendErrorPage(true);
        exit();
    }

    if ($resolvedLocale !== null && (
        in_array($resolvedLocale, sLang::langFront(), true)
        || (evo()->getLoginUserID('mgr') && in_array($resolvedLocale, sLang::langConfig(), true))
    )) {
        $langDefault = $resolvedLocale;
        $cleanUri = sLang::stripLeadingUriSegment($requestUri, $firstSegment);
        $_SERVER['REQUEST_URI'] = $cleanUri;
    } else {
        $resolvedLocale = null;
    }

    evo()->setLocale($langDefault);
    evo()->setConfig('lang', $langDefault);

    if (sLang::langDefault() != $langDefault || sLang::defaultInUrl()) {
        evo()->setConfig(
            'base_url',
            sLang::localizeGeneratedUrl((string)evo()->getConfig('base_url', '/'), $langDefault)
        );
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

    if ($resolvedLocale !== null && trim(substr($cleanUri, 0, strcspn($cleanUri, '?#')), '/') === '') {
        evo()->sendForward((int)evo()->getConfig('site_start', 1));
        exit();
    }
});

/**
 * Set the frontend locale from the request path or an explicit locale.
 */
Event::listen('evolution.OnLoadSettings', function($params) {
    $resolvedLocale = null;
    $defaultLocale = sLang::langDefault();

    if (isset($params['lang'])) {
        $langDefault = $params['lang'];
    } else {
        $langDefault = $defaultLocale;

        if (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = (string)$_SERVER['REQUEST_URI'];
            $path = substr($requestUri, 0, strcspn($requestUri, '?#'));
            $firstSegment = explode('/', ltrim($path, '/'), 2)[0];

            if ($firstSegment !== '') {
                $resolvedLocale = sLang::localeFromSegment($firstSegment);
                if (
                    !is_null($resolvedLocale)
                    && in_array($resolvedLocale, sLang::langFront(), true)
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

    if ($defaultLocale != $langDefault || sLang::defaultInUrl()) {
        evo()->setConfig(
            'base_url',
            sLang::localizeGeneratedUrl((string)evo()->getConfig('base_url', '/'), $langDefault)
        );
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
    // Service endpoints such as CAPTCHA do not use frontend routing.
    if (defined('EVO_API_MODE') && EVO_API_MODE) {
        return;
    }

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
 * Remember whether the manager could edit the resource before the core save changed its groups.
 */
Event::listen('evolution.OnBeforeDocFormSave', function ($params) {
    $request = request();
    if (!$request instanceof Request || !$request->isMethod('POST')) {
        return;
    }

    $request->attributes->remove('slang.resourceTranslation.editId');
    if (($params['mode'] ?? null) === 'upd' && isset($params['id'])) {
        $resource = ResourceTranslationAccess::savedResource($params['id'], 'upd', checkStoredPrivacy: true);
        $request->attributes->set('slang.resourceTranslation.editId', $resource ? (int) $resource->id : false);
    }
});

/**
 * Save translations only after the core persisted the resource and supplied its actual ID.
 */
Event::listen('evolution.OnDocFormSave', function ($params) {
    $request = request();
    if (!$request instanceof Request || !$request->isMethod('POST')) {
        return;
    }

    $mode = $params['mode'] ?? null;
    $beforeId = $request->attributes->get('slang.resourceTranslation.editId');
    $request->attributes->remove('slang.resourceTranslation.editId');
    $resource = ResourceTranslationAccess::savedResource(
        $params['id'] ?? null,
        $mode,
        checkStoredPrivacy: $mode === 'upd' && $beforeId === null
    );
    if (!$resource || ($mode === 'upd' && $beforeId !== null && $beforeId !== (int) $resource->id)) {
        return;
    }

    $id = (int) $resource->id;
    $input = $request->post();
    $controller = new sLangController();
    $defaultLang = sLang::langDefault();
    $locales = array_unique([$defaultLang, ...sLang::langConfig()]);
    $defaultFields = [];

    foreach ($locales as $locale) {
        $fields = [];
        foreach (sLang::siteContentFields() as $field) {
            $value = $input[$locale . '_' . $field] ?? null;
            if (is_string($value) && ResourceTranslationAccess::allowsValue($value)) {
                $fields[$field] = $value;
            }
        }
        if ($fields) {
            $controller->setLangContent($id, $locale, $fields);
            if ($locale === $defaultLang) {
                $defaultFields = $fields;
            }
        }
    }

    $data = [];
    foreach (['pagetitle', 'longtitle', 'description', 'introtext', 'content', 'menutitle'] as $field) {
        if (isset($defaultFields[$field])) {
            $data[$field] = $defaultFields[$field];
        }
    }

    if (is_string($input['alias'] ?? null) && trim($input['alias']) === '' && isset($defaultFields['pagetitle'])) {
        $baseAlias = strtolower(evo()->stripAlias(trim($defaultFields['pagetitle'])));
        $alias = $baseAlias;
        $suffix = 1;
        while (SiteContent::withTrashed()->where('id', '<>', $id)->where('alias', $alias)->exists()) {
            $alias = $baseAlias . $suffix++;
        }
        $data['alias'] = $alias;
    }

    if ($data) {
        SiteContent::withTrashed()->whereKey($id)->toBase()->update($data);
    }

    $controller->storeTranslatedTemplateVariables($id, (int) $resource->template, $locales, $input);
    $controller->saveMenuFlags($id, $input);
});
