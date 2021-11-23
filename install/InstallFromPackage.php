<?php

$packageName = $argv[1] ?? "";
$host = $argv[2] ?? "";
$rewriteBase = $argv[3] ?? "";

if (empty($packageName) || empty($host) || empty($rewriteBase)) {
  echo "Usage: php InstallFromPackage.php <packageName> <host> <rewriteBase>";
  exit;
}

if (!is_file("packages/{$packageName}.zip")) {
  echo "Package {$packageName} not found.";
  exit;
}

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

// ConfigEnvDomains.php
$domainsToInstall = \InstallerHelperFunctions::parseDomainsToInstall($installationConfig);

file_put_contents(
  PROJECT_ROOT_DIR."/ConfigEnvDomains.php",
  \InstallerHelperFunctions::renderConfigEnvDomains($domainsToInstall)
);

// SQL data
$tsStart = _getmicrotime();

$adminPanel->db->startTransaction();
$adminPanel->db->executeBuffer($sql);
$adminPanel->db->commit();

$executionTime = _getmicrotime() - $tsStart;

echo "Installation done. [{$executionTime} sec]";