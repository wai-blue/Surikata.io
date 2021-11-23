<?php

if (php_sapi_name() !== 'cli') {
  echo "Script is available only for CLI.";
}

$packageName = $argv[1] ?? "";
$host = $argv[2] ?? "";
$rewriteBase = $argv[3] ?? "";

if (empty($packageName) || empty($host) || empty($rewriteBase)) {
  echo "Usage: php InstallFromPackage.php <packageName> <host> <rewriteBase>\n";
  echo "Example: php InstallFromPackage.php basic-sk localhost /projects/Surikata.io/";
  exit;
}

if (!is_file("packages/{$packageName}.zip")) {
  echo "Package {$packageName} not found.";
  exit;
}

if (substr($rewriteBase, 0, 1) != "/" || substr($rewriteBase, -1) != "/") {
  echo "Warning: RewriteBase should start and end with a slash (/).\n";
}

echo "Warning: Assets are not copied to upload/ folder when installing from a package.\n";

$zip = new ZipArchive;
$zip->open(__DIR__."/packages/{$packageName}.zip");
$zip->extractTo(__DIR__."/packages");
$zip->close();

$sql = file_get_contents(__DIR__."/packages/surikata-installation.sql");
unlink(__DIR__."/packages/surikata-installation.sql");

$installationConfig = json_decode(file_get_contents(__DIR__."/packages/installation-config.json"), TRUE);
unlink(__DIR__."/packages/installation-config.json");

$sql = str_replace("{% SERVER_HTTP_HOST %}", $host, $sql);
$sql = str_replace("{% REWRITE_BASE %}", $rewriteBase, $sql);

require(__DIR__."/../Init.php");

include("Lib/InstallerHelperFunctions.php");

$websiteRenderer = new \MyEcommerceProject\Web($websiteRendererConfig);
$adminPanel = new \MyEcommerceProject\AdminPanel($adminPanelConfig, ADIOS_MODE_FULL, $websiteRenderer);

$tsStart = _getmicrotime();

echo "Installing Surikata.io package {$packageName}.\n";

// ConfigEnvDomains.php
$domainsToInstall = \InstallerHelperFunctions::parseDomainsToInstall($installationConfig);

file_put_contents(
  PROJECT_ROOT_DIR."/ConfigEnvDomains.php",
  \InstallerHelperFunctions::renderConfigEnvDomains($domainsToInstall)
);

// SQL data

$adminPanel->db->startTransaction();
$adminPanel->db->executeBuffer($sql);
$adminPanel->db->commit();

// SiteMap

foreach ($domainsToInstall as $domainIndex => $domain) {
  $adminPanel->widgets["Website"]->rebuildSitemap($domainsToInstall[$domainIndex]['name']);
}

// Done

$executionTime = _getmicrotime() - $tsStart;

echo "Installation done. [{$executionTime} sec]\n";