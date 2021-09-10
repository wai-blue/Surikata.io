<?php

ini_set("display_errors", 1);
ini_set("error_reporting", E_ALL ^ E_NOTICE ^ E_WARNING);

// load configs
require_once("ConfigEnv.php");
require_once("ConfigApp.php");

// include autoloaders
require_once("vendor/autoload.php");
require_once(ADMIN_PANEL_SRC_DIR."/Autoload.php");
require_once(CASCADA_CORE_DIR."/Autoload.php");
require_once(ADIOS_CORE_DIR."/Autoload.php");

// initialize your project
require_once(__DIR__."/prop/Init.php");

// include Loader classes
require_once(ADMIN_PANEL_SRC_DIR."/Core/Web/Loader.php");

//
require_once(__DIR__."/prop/MyEcommerceProject.php");

// start PHP session
session_start();
