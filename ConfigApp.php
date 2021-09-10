<?php

////////////////////////////////////////////////////////
// Directories
////////////////////////////////////////////////////////

// Admin panel

define('ADMIN_PANEL_URL', "//".$_SERVER['HTTP_HOST'].REWRITE_BASE."admin");
define('ADMIN_PANEL_REWRITE_BASE', REWRITE_BASE."admin/");

define('ADMIN_PANEL_SRC_URL', "//".$_SERVER['HTTP_HOST'].REWRITE_BASE."src");

define('ADMIN_PANEL_SRC_DIR', __DIR__."/src/Surikata");
define('ADMIN_PANEL_LOG_DIR', __DIR__."/log");
define('ADMIN_PANEL_TMP_DIR', __DIR__."/tmp");

define('UPLOADED_FILES_URL', "//".$_SERVER['HTTP_HOST'].REWRITE_BASE."upload");
define('UPLOADED_FILES_DIR', __DIR__."/upload");

// Miscelaneous

define('DEVEL_MODE', TRUE);
define('PROJECT_ROOT_DIR', __DIR__);
define('CONTROLLERS_DIR', __DIR__."/src/Controllers");
define('LOG_DIR', __DIR__."/log");
define('DATA_DIR', __DIR__."/data");
define('PROP_DIR', __DIR__."/prop");

define('TWIG_CACHE_DIR', FALSE); // disable cache


// External libs

define('CASCADA_CORE_DIR', __DIR__."/src/Surikata/Lib/CASCADA");
define('ADIOS_CORE_DIR', __DIR__."/src/Surikata/Lib/ADIOS");
define('ADIOS_WIDGETS_DIR', __DIR__."/src/Surikata/Widgets");

////////////////////////////////////////////////////////
// Administration panel configuration
////////////////////////////////////////////////////////

define('GTP', 'srkt');

$adminPanelConfig["build"]["version"]          = "1.2";

$adminPanelConfig["session_salt"]              = "SurikataAdminPanel";
$adminPanelConfig["brand"]["title"]            = ($configEnv["brand"]["title"] ?? "Surikata");
$adminPanelConfig["brand"]["subtitle"]         = "Administration panel";
$adminPanelConfig["brand"]["favicon"]          = ADMIN_PANEL_URL."/surikata/assets/images/Surikata_logo_farebne_znak.png";
$adminPanelConfig["brand"]["login"]["splash"]  = ($configEnv["brand"]["login"]["splash"] ?? ADMIN_PANEL_URL."/surikata/assets/images/login-screen.jpg");
$adminPanelConfig["brand"]["sidebar"]["icon"]  = ADMIN_PANEL_URL."/surikata/assets/images/Surikata_logo_biele_znak.png";
$adminPanelConfig["brand"]["sidebar"]["title"] = "Surikata";
$adminPanelConfig["brand"]["sidebar"]["subtitle"] = "Your e-commerce platform";
$adminPanelConfig["global_table_prefix"]       = GTP;

$adminPanelConfig['devel_mode']              = DEVEL_MODE;
$adminPanelConfig['rewrite_base']            = ADMIN_PANEL_REWRITE_BASE;

$adminPanelConfig["dir"]                     = ADMIN_PANEL_SRC_DIR;
$adminPanelConfig["url"]                     = ADMIN_PANEL_URL;

$adminPanelConfig["log_dir"]                 = ADMIN_PANEL_LOG_DIR;
$adminPanelConfig["tmp_dir"]                 = ADMIN_PANEL_TMP_DIR;

$adminPanelConfig["console"]["log_file"]     = ADMIN_PANEL_LOG_DIR."/admin-".date("Ymd").".log";

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
$adminPanelConfig['widgets']['Prices']['enabled'] = TRUE;
$adminPanelConfig['widgets']['Shipping']['enabled'] = TRUE;
$adminPanelConfig['widgets']['Stock']['enabled'] = TRUE;
$adminPanelConfig['widgets']['Website']['enabled'] = TRUE;
$adminPanelConfig['widgets']['Plugins']['enabled'] = TRUE;
$adminPanelConfig['widgets']['Settings']['enabled'] = TRUE;

$adminPanelConfig['default_action'] = "Overview/Welcome";
$adminPanelConfig['widgets']['Website']['domains'] = $configEnv["domains"];
$adminPanelConfig['widgets']['Website']['domainLanguages'] = $configEnv["domainLanguages"];

////////////////////////////////////////////////////////
// Website renderer configuration
////////////////////////////////////////////////////////

$websiteRendererConfig = [
  "domainToRender" => WEBSITE_DOMAIN_TO_RENDER,
  "minifyOutputHtml" => $configEnv['minifyOutputHtml'] ?? FALSE,
  "validateOutputHtml" => $configEnv['validateOutputHtml'] ?? FALSE,

  "rewriteBase" => WEBSITE_REWRITE_BASE,
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
