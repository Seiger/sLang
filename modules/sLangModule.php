<?php
/**
 *	Language management module
 */

use Illuminate\Support\Str;
use Seiger\sLang\Controllers\sLangController;

if (!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE != 'true') die("No access");

$sLangController = new sLangController();
$data['get'] = request()->get ?? "translates";
$data['sLangController']  = $sLangController;
$data['url'] = $sLangController->url;

switch ($data['get']) {
    default:
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";
        switch ($action) {
            case "synchronize":
                // Parsing Blade Templates
                $sLang->parseBlade();
                break;
            case "translate":
                $result = $sLang->getAutomaticTranslate($_POST['source'], $_POST['target']);
                die($result);
            case "update":
                $result = $sLang->updateTranslate($_POST['source'], $_POST['target'], $_POST['value']);
                die($result);
            case "translate-only":
                $result = $sLang->getAutomaticTranslateOnly($_POST['text'], $_POST['source'], $_POST['target']);
                die($result);
            case "add-new":
                $result = $sLang->saveTranslate($_POST);
                die($result);
            default:
                break;
        }
        break;
    case "settings":
        if (count($_POST) > 0) {
            // Default language
            $sLang->setLangDefault($_POST['s_lang_default']);

            // Default language display
            $sLang->setLangDefaultShow($_POST['s_lang_default_show']);

            // List of site languages
            $sLang->setLangConfig($_POST['s_lang_config']);

            // List of languages for the frontend
            $sLang->setLangFront($_POST['s_lang_front']);

            // Table modification
            $sLang->setModifyTables();
        }
        break;
}

echo $sLangController->view('index', $data);