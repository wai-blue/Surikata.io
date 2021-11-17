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

if (
  strpos($_SERVER["SCRIPT_NAME"], "install/index.php") === FALSE
  && !file_exists(__DIR__."/ConfigEnv.php")
) {
  echo "It looks like you did not run the installer yet.<br/>";
  echo "<a href='install'>Open the installer</a>";
  exit;
}

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
session_name(md5(__FILE__));
session_start();
