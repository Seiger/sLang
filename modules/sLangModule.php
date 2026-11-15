<?php
/**
 *	Language management module
 */

use Seiger\sLang\Controllers\sLangController;
use Seiger\sLang\Facades\sLang;
use Seiger\sLang\Support\ManagerAccess;
use Seiger\sLang\Support\SettingsAccess;

if (!defined('IN_MANAGER_MODE') || !constant('IN_MANAGER_MODE')) die("No access");

ManagerAccess::authorize();

$request = request();
$sLangController = new sLangController();
$data['get'] = $request->get ?? "translates";
$data['sLangController']  = $sLangController;
$data['url'] = sLang::moduleUrl();
$data['moduleUrl'] = sLang::moduleUrl();
$data['tabs'] = [
    [
        'key' => 'translates',
        'label' => __('sLang::global.dictionary'),
        'icon' => 'language',
        'href' => sLang::moduleUrl() . '&get=translates',
    ],
    [
        'key' => 'settings',
        'label' => __('global.settings_config'),
        'icon' => 'settings',
        'href' => sLang::moduleUrl() . '&get=settings',
    ],
];

$action = $request->input('action');
if ($action === 'synchronize') {
    SettingsAccess::authorize();
    abort_unless($request->isMethod('POST'), 405);
    $sLangController->parseBlade();
} elseif ($action === 'translate-only') {
    ManagerAccess::authorize('save_document');
    abort_unless($request->isMethod('POST'), 405);
    $text = $request->post('text');
    $source = $request->post('source');
    $target = $request->post('target');
    $default = sLang::langDefault();
    abort_unless(
        is_string($text) && $source === $default
            && is_string($target) && $target !== $default && in_array($target, sLang::langConfig(), true),
        422
    );

    header('Content-Type: text/plain; charset=UTF-8');
    exit(sLang::getAutomaticTranslate($text, $source, $target));
}

echo $sLangController->view('index', $data)->render();
