<?php
/**
 *	Language management module
 */

use Illuminate\Support\Str;
use Seiger\sLang\Controllers\sLangController;
use Seiger\sLang\Facades\sLang;

if (!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE != 'true') die("No access");

$sLangController = new sLangController();
$data['get'] = request()->get ?? "translates";
$data['sLangController']  = $sLangController;
$data['url'] = sLang::moduleUrl();

switch ($data['get']) {
    default:
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";
        switch ($action) {
            case "synchronize":
                // Parsing Blade Templates
                $sLangController->parseBlade();
                break;
            case "translate":
                $result = $sLangController->setAutomaticTranslate($_POST['source'], $_POST['target']);
                die($result);
            case "update":
                $result = $sLangController->updateTranslate($_POST['source'], $_POST['target'], $_POST['value']);
                die($result);
            case "translate-only":
                $result = sLang::getAutomaticTranslate($_POST['text'], $_POST['source'], $_POST['target']);
                die($result);
            case "add-new":
                $result = $sLangController->saveTranslate($_POST);
                die($result);
            default:
                break;
        }
        break;
    case "settings":
        // Default language
        if (request()->has('s_lang_default')) {
            $sLangController->setLangDefault(request()->input('s_lang_default'));
        }

        // Default language display
        if (request()->has('s_lang_default_show')) {
            $sLangController->setLangDefaultShow(request()->input('s_lang_default_show'));
        }

        // List of site languages
        if (request()->has('s_lang_config')) {
            $sLangController->setLangConfig(request()->input('s_lang_config'));
        }

        // List of languages for the frontend
        if (request()->has('s_lang_front')) {
            $sLangController->setLangFront(request()->input('s_lang_front'));
        }

        // List of multilang TVs
        if (request()->has('s_lang_tvs')) {
            $sLangController->setLangTvs(request()->input('s_lang_tvs'));
        }

        if (count($_POST) > 0) {
            // Table modification
            $sLangController->setModifyTables();

            // Set On the Module
            if (evo()->getConfig("s_lang_enable", 9999) == 9999) {
                $sLangController->setOnOffLangModule(1);
            }
        }
        break;
}

echo $sLangController->view('index', $data);