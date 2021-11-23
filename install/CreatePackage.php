<?php

// http://127.0.0.1/github/Surikata.io/install/?do_install=1
// &product-catalog=yes
// &random_products_count=10
// &customers=yes
// &delivery-and-payment-services=yes
// &orders=yes
// &domain_1_slug=hello-world
// &domain_1_description=Developer%60s+Hello+World+example
// &domain_1_language_index=1
// &domain_1_theme_name=HelloWorld
// &domain_2_slug=en
// &domain_2_description=English+version
// &domain_2_language_index=1
// &domain_2_theme_name=Abelo
// &domain_3_slug=sk
// &domain_3_description=Slovenská+verzia
// &domain_3_language_index=2
// &domain_3_theme_name=Abelo
// &domain_4_slug=
// &domain_4_description=
// &domain_4_language_index=3
// &domain_4_theme_name=Abelo
// &slideshow_image_set=books

$arguments = getopt(
  "T:L:",
  ["template:", "languages:"],
  $restIndex
);

$template = $arguments["T"] ?? $arguments["template"] ?? "Basic";
$languages = $arguments["L"] ?? $arguments["languages"] ?? "en,sk";
$packageName = $argv[$restIndex] ?? "";

$availableLanguages = ["sk", "en", "cz"];
$availableTemplates = ["Basic", "Abelo"];

if (empty($packageName) || empty($languages) || empty($template)) {
  echo "Surikata.io installer package creator.

Usage: php CreatePackage.php [OPTIONS] <packageName>

Required options:

-L, --languages    Comma separated value with list of languages to install.
                    Available languages: ".join(", ", $availableLanguages)."
-T, --template     Name of the design template to use.
                    Available templates: ".join(", ", $availableTemplates)."
";
  exit;
}

foreach (explode(",", $languages) as $language) {
  if (!in_array($language, $availableLanguages)) {
    echo "Unknown language.";
    exit;
  }
}

if (!in_array($template, $availableTemplates)) {
  echo "Unknown template.";
  exit;
}





$installationConfig = [
  "slideshow_image_set" => "books",
  "domain_1_slug" => "en",
  "domain_1_description" => "English",
  "domain_1_language_index" => "1",
  "domain_1_theme_name" => "Abelo",
  "domain_2_slug" => "sk",
  "domain_2_description" => "Slovenská",
  "domain_2_language_index" => "2",
  "domain_2_theme_name" => "Abelo",
  "random_products_count" => 50,
  "product-catalog" => "yes",
  "delivery-and-payment-services" => "yes",
  "customers" => "yes",
  "orders" => "yes",
  "rewrite_base_is_correct" => "1",
  "http_host" => "{% SERVER_HTTP_HOST %}",
  "rewrite_base" => "{% REWRITE_BASE %}",
  "create_package" => $packageName ?? date("Ymdhis"),
];

include(__DIR__."/index.php");
