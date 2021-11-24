<?php

$availableThemes = [];
foreach (@scandir(__DIR__."/../src/Themes") as $dir) {
  if (
    !in_array($dir, [".", ".."])
    && is_file(__DIR__."/../src/Themes/{$dir}/Main.php")
  ) {
    $availableThemes[] = $dir;
  }
}

foreach (@scandir(__DIR__."/../prop/Themes") as $dir) {
  if (
    !in_array($dir, [".", ".."])
    && is_file(__DIR__."/../prop/Themes/{$dir}/Main.php")
  ) {
    $availableThemes[] = $dir;
  }
}

$availableThemes = array_unique($availableThemes);
sort($availableThemes);

$availableLanguageCombinations = [
  "sk",
  "sk,cz",
  "sk,cz,en",
  "sk,en",
  "cz,en",
];

foreach ($availableThemes as $theme) {
  foreach ($availableLanguageCombinations as $languages) {
    $package = $theme."-".str_replace(",", "-", $languages);
    echo "CREATING PACKAGE {$package}.\n";
    $arguments = [
      "theme" => $theme,
      "languages" => $languages,
      "package" => $package,
    ];

    include(__DIR__."/Install.php");

    echo "PACKAGE {$package} CREATED.\n";
  }
}