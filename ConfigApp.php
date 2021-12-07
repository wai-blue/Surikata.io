<?php

/*
 * DO NOT CHANGE THIS FILE UNLESS YOU WANT TO CONTRIBUTE TO
 * Surikata.io's CORE.
 *
 * IF YOU WANT TO BUILD YOUR ECOMMERCE PROJECT, START IN
 * THE prop/ FOLDER.
 *
 * Author: https://www.wai.blue
 */

////////////////////////////////////////////////////////
// Directories
////////////////////////////////////////////////////////

// Admin panel

define('ADMIN_PANEL_URL', "//".$_SERVER['HTTP_HOST'].REWRITE_BASE."admin");

define('UPLOADED_FILES_URL', "//".$_SERVER['HTTP_HOST'].REWRITE_BASE."upload");
define('UPLOADED_FILES_DIR', PROJECT_ROOT_DIR."/upload");

// Miscelaneous

define('DEVEL_MODE', TRUE);

if (!defined('PROP_DIR')) {
  define('PROP_DIR', __DIR__."/prop");
}

if (!defined('LOG_DIR')) {
  define('LOG_DIR', PROJECT_ROOT_DIR."/log");
}

if (!defined('DATA_DIR')) {
  define('DATA_DIR', PROJECT_ROOT_DIR."/data");
}

if (!defined('CACHE_DIR')) {
  define('CACHE_DIR', PROJECT_ROOT_DIR."/cache");
}

if (!defined('TWIG_CACHE_DIR')) {
  define('TWIG_CACHE_DIR', PROJECT_ROOT_DIR."/cache/twig");
}

// if (!defined('CONTROLLERS_DIR')) {
//   define('CONTROLLERS_DIR', __DIR__."/src/Controllers");
// }

// External libs

define('CASCADA_CORE_DIR', __DIR__."/src/Surikata/Lib/CASCADA");
define('ADIOS_CORE_DIR', __DIR__."/src/Surikata/Lib/ADIOS");
define('ADIOS_WIDGETS_DIR', __DIR__."/src/Surikata/Widgets");

////////////////////////////////////////////////////////
// Administration panel configuration
////////////////////////////////////////////////////////

if (!defined('GTP')) {
  define('GTP', 'srkt');
}

$adminPanelConfig["build"]["version"]          = "1.2";

$adminPanelConfig["session_salt"]              = "SurikataAdminPanel";
$adminPanelConfig["brand"]["title"]            = ($configEnv["brand"]["title"] ?? "Surikata.io Administration Panel");
$adminPanelConfig["brand"]["subtitle"]         = "Administration panel";
$adminPanelConfig["brand"]["favicon"]          = ADMIN_PANEL_URL."/surikata/assets/images/Surikata_logo_farebne_znak.png";
$adminPanelConfig["brand"]["login"]["splash"]  = ($configEnv["brand"]["login"]["splash"] ?? ADMIN_PANEL_URL."/surikata/assets/images/login-screen.jpg");
$adminPanelConfig["brand"]["sidebar"]["icon"]  = ADMIN_PANEL_URL."/surikata/assets/images/Surikata_logo_biele_znak.png";
$adminPanelConfig["brand"]["sidebar"]["title"] = "Surikata.io";
$adminPanelConfig["brand"]["sidebar"]["subtitle"] = "Administration panel";
$adminPanelConfig["global_table_prefix"]       = GTP;

$adminPanelConfig['devel_mode']              = DEVEL_MODE;
$adminPanelConfig['rewrite_base']            = REWRITE_BASE."admin/";

$adminPanelConfig["dir"]                     = SURIKATA_ROOT_DIR."/src/Surikata";
$adminPanelConfig["url"]                     = ADMIN_PANEL_URL;

$adminPanelConfig["log_dir"]                 = LOG_DIR;
$adminPanelConfig["cache_dir"]               = CACHE_DIR;

$adminPanelConfig["db_host"]                 = DB_HOST.(defined('DB_PORT') && is_numeric(DB_PORT) ? ":".DB_PORT : "");
$adminPanelConfig["db_login"]                = DB_LOGIN;
$adminPanelConfig["db_password"]             = DB_PASSWORD;
$adminPanelConfig["db_name"]                 = DB_NAME;

if (defined('SMTP_HOST')) {
  $adminPanelConfig["smtp_host"]               = SMTP_HOST;
  $adminPanelConfig["smtp_port"]               = SMTP_PORT;
  $adminPanelConfig["smtp_protocol"]           = SMTP_PROTOCOL;
  $adminPanelConfig["smtp_login"]              = SMTP_LOGIN;
  $adminPanelConfig["smtp_password"]           = SMTP_PASSWORD;
  $adminPanelConfig["smtp_from"]               = SMTP_FROM;
}

$adminPanelConfig['files_dir']               = UPLOADED_FILES_DIR;
$adminPanelConfig['files_url']               = UPLOADED_FILES_URL;

$adminPanelConfig["locale"]["currency"]["symbol"] = LOCALE_CURRENCY_SYMBOL;
$adminPanelConfig["locale"]["date"]["format"]     = LOCALE_DATE_FORMAT;

$adminPanelConfig['widgets'] = [];
$adminPanelConfig['widgets']['Overview']['enabled'] = TRUE;
$adminPanelConfig['widgets']['Customers']['enabled'] = TRUE;
$adminPanelConfig['widgets']['CRM']['enabled'] = TRUE;
$adminPanelConfig['widgets']['Orders']['enabled'] = TRUE;
$adminPanelConfig['widgets']['Finances']['enabled'] = TRUE;
$adminPanelConfig['widgets']['Products']['enabled'] = TRUE;
$adminPanelConfig['widgets']['Shipping']['enabled'] = TRUE;
$adminPanelConfig['widgets']['Stock']['enabled'] = TRUE;
$adminPanelConfig['widgets']['Website']['enabled'] = TRUE;
$adminPanelConfig['widgets']['Plugins']['enabled'] = TRUE;
$adminPanelConfig['widgets']['Settings']['enabled'] = TRUE;
$adminPanelConfig['widgets']['Maintenance']['enabled'] = TRUE;
$adminPanelConfig['widgets']['HelpAndSupport']['enabled'] = TRUE;

$adminPanelConfig['default_action'] = "Overview/Welcome";
$adminPanelConfig['widgets']['Website']['domains'] = $configEnv["domains"];
$adminPanelConfig['widgets']['Website']['domainLanguages'] = $configEnv["domainLanguages"];

////////////////////////////////////////////////////////
// Website renderer configuration
////////////////////////////////////////////////////////

$websiteRendererConfig = [
  "domainToRender" => defined("WEBSITE_DOMAIN_TO_RENDER") ? WEBSITE_DOMAIN_TO_RENDER : "",
  "minifyOutputHtml" => $configEnv['minifyOutputHtml'] ?? FALSE,
  "validateOutputHtml" => $configEnv['validateOutputHtml'] ?? FALSE,

  "assetCacheDir" => CACHE_DIR."/assets-".WEBSITE_DOMAIN_TO_RENDER,

  "rewriteBase" => defined("WEBSITE_REWRITE_BASE") ? WEBSITE_REWRITE_BASE : "/",
  "twigCacheDir" => TWIG_CACHE_DIR,
  "twigDebugEnabled" => TRUE,
  "connection" => [
    "driver"    => "mysql",
    "host"      => DB_HOST,
    "port"      => DB_PORT,
    "database"  => DB_NAME,
    "username"  => DB_LOGIN,
    "password"  => DB_PASSWORD,
    "charset"   => "utf8",
    "collation" => "utf8_unicode_ci",
  ]
];
