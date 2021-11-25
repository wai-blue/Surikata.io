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

ini_set("display_errors", 1);
ini_set("error_reporting", E_ALL ^ E_NOTICE ^ E_WARNING);

// SURIKATA_ROOT_DIR points to the folder where the original Surikata.io's repository files are located
if (!defined('SURIKATA_ROOT_DIR')) {
  define('SURIKATA_ROOT_DIR', __DIR__);
}

// By default, the PROJECT_ROOT_DIR is the same as SURIKATA_ROOT_DIR
// However, for multi-account hosting where one Surikata.io code repository serves multiple
// accounts, the PROJECT_ROOT_DIR points to the root folder of the account.
if (!defined('PROJECT_ROOT_DIR')) {
  define('PROJECT_ROOT_DIR', __DIR__);
}

if (
  strpos($_SERVER["SCRIPT_NAME"], "install/index.php") === FALSE
  && !file_exists(PROJECT_ROOT_DIR."/ConfigEnv.php")
) {
  echo "It looks like you did not run the installer yet.<br/>";
  echo "<a href='install'>Open the installer</a>";
  exit;
}

// load configs
require_once(PROJECT_ROOT_DIR."/ConfigEnv.php");
require_once(__DIR__."/ConfigApp.php");

// load assets from cache, if necessary
if ($configEnv["cacheAssets"] ?? FALSE) {
  require_once(__DIR__."/LoadAssetsFromCache.php");
}

// include autoloaders
require_once("vendor/autoload.php");
require_once(SURIKATA_ROOT_DIR."/src/Surikata/Autoload.php");
require_once(CASCADA_CORE_DIR."/Autoload.php");
require_once(ADIOS_CORE_DIR."/Autoload.php");

// initialize your project
require_once(__DIR__."/prop/Init.php");

// include Loader classes
require_once(SURIKATA_ROOT_DIR."/src/Surikata/Core/Web/Loader.php");

//
require_once(__DIR__."/prop/MyEcommerceProject.php");

// start PHP session
session_start();
