<?php
/**
 *	Language management module
 */

if(!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE != 'true') die("No access");

require_once MODX_BASE_PATH . 'assets/modules/seigerlang/sLang.class.php';
require_once MODX_BASE_PATH . 'assets/modules/seigerlang/models/sLangTranslate.php';

$sLang  = new sLang();
$data['get']    = isset($_REQUEST['get']) ? $_REQUEST['get'] : "translates";
$data['sLang']  = $sLang;
$data['url']    = $sLang->url;
$tbl_system_settings  = evo()->getDatabase()->getFullTableName('system_settings');
$tbl_site_content     = evo()->getDatabase()->getFullTableName('site_content');
$tbl_a_lang           = evo()->getDatabase()->getFullTableName('s_lang');

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

$sLang->view('index', $data);