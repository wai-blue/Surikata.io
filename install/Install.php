<?php

///////////////////////////////////////////////////////////////
// initialize

require_once __DIR__."/Lib/Autoload.php";

if (php_sapi_name() !== 'cli') {
  echo "Script is available only for CLI.";
}

///////////////////////////////////////////////////////////////
// parse and validate arguments

if (isset($arguments) && is_array($arguments)) {
  // script has been included in this case
} else {
  $arguments = getopt(
    "T:L:P:",
    ["theme:", "languages:", "package:"],
    $restIndex
  );
}

$theme = $arguments["T"] ?? $arguments["theme"] ?? "Basic";
$languages = $arguments["L"] ?? $arguments["languages"] ?? "en,sk";
$package = $arguments["P"] ?? $arguments["package"] ?? "";

$availableLanguages = [1 => "en", 2 => "sk", 3 => "cz"]; // kluc pola = languageIndex
$availableThemes = ["Basic", "Abelo"];

if (empty($languages) || empty($theme)) {
  echo "Surikata.io installer package creator.

Usage: php Install.php [-L languages] [-T theme] [-P package]
Example: php Install.php -L sk,en -T Basic -P package-basic-sk

Options:

-L, --languages    Required. Comma separated value with list of languages to install.
                   Available languages: ".join(", ", $availableLanguages)."
-T, --theme        Required. Name of the design theme to use.
                   Available themes: ".join(", ", $availableThemess)."
-P, --package      Optional.Name of the package to create.
                   If provided, the installation package will be created.
                   Otherwise, Surikata.io will be installed directly do the database.
";
  exit;
}

foreach (explode(",", $languages) as $language) {
  if (!in_array($language, $availableLanguages)) {
    echo "Unknown language.";
    exit;
  }
}

if (!in_array($theme, $availableThemes)) {
  echo "Unknown theme.";
  exit;
}

///////////////////////////////////////////////////////////////
// load $adminPanelConfig and $websiteRendererConfig

require_once __DIR__."/../Init.php";

///////////////////////////////////////////////////////////////
// prepare $installationConfig

$installationConfig = [
  "slideshow-image-set" => "books",
  "random-products-count" => 50,
  "product-catalog" => "yes",
  "delivery-and-payment-services" => "yes",
  "customers" => "yes",
  "orders" => "yes",
];

if (!empty($package)) {
  $installationConfig["create_package"] = $package;
}

$i = 1;
foreach (explode(",", $languages) as $language) {
  $language = strtolower(trim($language));

  $languageIndex = 0;

  foreach ($availableLanguages as $tmpLanguageIndex => $tmpLanguage) {
    if ($tmpLanguage == $language) {
      $languageIndex = $tmpLanguageIndex;
    }
  }

  if ($languageIndex == 0) continue;

  $installationConfig["domain_{$i}_slug"] = $language;
  $installationConfig["domain_{$i}_description"] = "Language ".strtoupper($language);
  $installationConfig["domain_{$i}_language_index"] = $languageIndex;
  $installationConfig["domain_{$i}_theme_name"] = $theme;

  $i++;
}

///////////////////////////////////////////////////////////////
// install the project

$installationLog = \Surikata\Installer\Installer::installSurikataProject(
  $adminPanelConfig,
  $websiteRendererConfig,
  $installationConfig
);
