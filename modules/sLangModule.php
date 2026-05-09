<?php
/**
 *	Language management module
 */

use Seiger\sLang\Controllers\sLangController;
use Seiger\sLang\Facades\sLang;

if (!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE != 'true') die("No access");

$sLangController = new sLangController();
$data['get'] = request()->get ?? "translates";
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

switch ($data['get']) {
    default:
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";
        switch ($action) {
            case "translate-only":
                $result = sLang::getAutomaticTranslate($_POST['text'], $_POST['source'], $_POST['target']);
                die($result);
            default:
                break;
        }
        break;
}

echo $sLangController->view('index', $data);
