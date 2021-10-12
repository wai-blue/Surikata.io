<?php

$configEnv["domainLanguages"] = [1 => "English", 2 => "Slovensky", 3 => "Česky"];

$configEnv["domains"] = [
  [
    'name' => 'english-version',
    'description' => 'English version',
    'slug' => 'en',
    'rootUrl' => $_SERVER['HTTP_HOST'].REWRITE_BASE.'en',
    'languageIndex' => 1,
  ],
  [
    'name' => 'slovenska-verzia',
    'description' => 'Slovenská verzia',
    'slug' => 'sk',
    'rootUrl' => $_SERVER['HTTP_HOST'].REWRITE_BASE.'sk',
    'languageIndex' => 2,
  ],
];

$slug = reset(explode("/", str_replace(REWRITE_BASE, "", $_SERVER["REQUEST_URI"])));

$domainToRender = reset($configEnv["domains"]);
foreach ($configEnv["domains"] as $domain) {
  if ($domain["slug"] == $slug) {
    $domainToRender = $domain;
  }
}

define("WEBSITE_DOMAIN_TO_RENDER", $domainToRender["name"]);
define("WEBSITE_REWRITE_BASE", REWRITE_BASE.$domainToRender["slug"]);