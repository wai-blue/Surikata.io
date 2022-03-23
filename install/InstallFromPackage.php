<?php

use \Surikata\Installer\HelperFunctions;

///////////////////////////////////////////////////////////////
// initialize

if (php_sapi_name() !== 'cli') {
  echo "Script is available only for CLI.";
}

if (!class_exists("\\ZipArchive")) {
  throw new \Exception("Cannot install package. ZipArchive class not found.");
}

require_once __DIR__."/Lib/Autoload.php";

///////////////////////////////////////////////////////////////
// parse and validate arguments

if (isset($arguments) && is_array($arguments)) {
  // script has been included in this case
} else {
  $arguments = $argv;
}

$packageName = $arguments[1] ?? "";

if (empty($packageName)) {
  echo "Usage: php InstallFromPackage.php <packageName>\n";
  echo "Example: php InstallFromPackage.php basic-sk";
  exit;
}

if (!is_file(__DIR__."/packages/{$packageName}.zip")) {
  echo "Package {$packageName} not found.";
  exit;
}

///////////////////////////////////////////////////////////////
// load $adminPanelConfig and $websiteRendererConfig

require_once __DIR__."/../Init.php";

///////////////////////////////////////////////////////////////
// install the project

$tmpPackageFolder = __DIR__."/~~tmp~~".rand(100, 999)."~~".rand(100, 999)."~~";
mkdir($tmpPackageFolder);

$zip = new \ZipArchive;
$zip->open(__DIR__."/packages/{$packageName}.zip");
$zip->extractTo($tmpPackageFolder);
$zip->close();

$sql = file_get_contents("{$tmpPackageFolder}/package.sql");
$installationConfig = json_decode(file_get_contents("{$tmpPackageFolder}/installation-config.json"), TRUE);

$websiteRenderer = new \MyEcommerceProject\Web($websiteRendererConfig);
$adminPanel = new \MyEcommerceProject\AdminPanel(
  $adminPanelConfig,
  \ADIOS\Core\Loader::ADIOS_MODE_FULL,
  $websiteRenderer
);

$adminPanel->createMissingFolders();

$tsStart = _getmicrotime();

echo "Installing Surikata.io package {$packageName}.\n";

// ConfigEnvDomains.php
$domainsToInstall = HelperFunctions::parseDomainsToInstall($installationConfig);

file_put_contents(
  PROJECT_ROOT_DIR."/ConfigEnvDomains.php",
  HelperFunctions::renderConfigEnvDomains($domainsToInstall)
);

// Assets & Upload folder

$wsg = new \Surikata\Installer\WebsiteContentGenerator($adminPanel, $domainsToInstall, $installationConfig);

HelperFunctions::recursiveRmDir($adminPanel->config["files_dir"], [".htaccess"]);
HelperFunctions::recursiveCopy("{$tmpPackageFolder}/upload", $adminPanel->config["files_dir"]);

// SQL

$adminPanel->db->startTransaction();
$adminPanel->db->executeBuffer($sql);
$adminPanel->db->commit();

// SiteMap

foreach ($domainsToInstall as $domainIndex => $domain) {
  $adminPanel->widgets["Website"]->rebuildSitemap($domainsToInstall[$domainIndex]['name']);
}

// Done

HelperFunctions::recursiveRmDir($tmpPackageFolder);

$executionTime = _getmicrotime() - $tsStart;

echo "Installation done. [{$executionTime} sec]\n";