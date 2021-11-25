<?php

require_once __DIR__."/Lib/Autoload.php";

if (php_sapi_name() === 'cli') {
  $installationConfig["do_install"] = "1";
} else {
  $installationConfig = $_GET;
}

\Surikata\Installer\HelperFunctions::echo("
  <html>
  <head>
    <title>Surikata.io Installer</title>
    <link rel='shortcut icon' href='../src/Surikata/Core/Assets/images/Surikata_logo_farebne_znak.png'>
    <style>
      * { font-family: verdana; font-size: 10pt; }
      body { background: #EEEEEE; }
      h1 { color: #224abe; font-size: 16pt; }
      h2 { color: #224abe; font-size: 12pt; }

      table { border: 1px solid #F0F0F0; }
      table tr:nth-child(even) td { background: #F0F0F0; }
      table td { padding: 2px; }

      label { display: block; padding: 2px; }
      label:hover { background: #224abe; color: white; cursor: pointer; }

      .btn { color: #224abe; background: white; cursor: pointer; border: 1px solid #224abe; padding: 1em; margin: 1em 0; }
      .btn:hover { color: white; background: #224abe; }

      a.btn { display: inline-block; text-decoration: none; }

      .content { width: 820px; margin: auto; background: white; padding: 1em; }
      .logo { width: 100px; margin: auto; }

      #log {
        background: #2d2d2d;
        font-family: courier;
        color: white;
        padding: 1em;
        font-size: 9pt;
        margin-top: 1em;
      }
    </style>
  </head>
  <body>
    <div class='content'>
      <h1>Surikata.io Installer</h1>
");

$installationStart = microtime(TRUE);
$rewriteBaseIsCorrect = ($installationConfig['rewrite_base_is_correct'] ?? "") == "1";

if (!defined('PROJECT_ROOT_DIR')) {
  define('PROJECT_ROOT_DIR', realpath(__DIR__."/.."));
}

file_put_contents(PROJECT_ROOT_DIR."/ConfigEnvDomains.php", \Surikata\Installer\HelperFunctions::renderConfigEnvDomains());

set_time_limit(0);

if (!is_file(__DIR__."/../vendor/autoload.php")) {
  \Surikata\Installer\HelperFunctions::echo("
    <div style='color:red'>
      Sorry, it looks like you did not run 'composer install'.<br/>
      <br/>
      Install required libraries:
      <ul>
        <li>run <i>composer install</i> in project's root folder</li>
        <li>rerun this installer again</li>
      </ul>
    </div>
  ");
  exit();
}

if (!is_file(PROJECT_ROOT_DIR."/ConfigEnv.php")) {
  \Surikata\Installer\HelperFunctions::echo("
    <div style='color:red'>
      Sorry, it looks like you do not have your ConfigEnv.php configured.<br/>
      <br/>
      Configure your environment:
      <ul>
        <li>copy <i>ConfigEnv.php.tmp</i> to <i>ConfigEnv.php</i></li>
        <li>modify it based on your environment</li>
        <li>rerun this installer again</li>
      </ul>
    </div>
  ");
  exit();
}

require(__DIR__."/../Init.php"); // $adminPanelConfig a $websiteRendererConfig

if (empty(REWRITE_BASE) || empty(DB_LOGIN) || empty(DB_NAME)) {
  \Surikata\Installer\HelperFunctions::echo("
    <div style='color:red'>
      Sorry, it looks like you did not configure necessary parameters.<br/>
      <br/>
      Check following configurations in your ConfigEnv.php file:
      <ul>
        <li>REWRITE_BASE</li>
        <li>DB_LOGIN</li>
        <li>DB_NAME</li>
        <li>rerun this installer again</li>
      </ul>
    </div>
  ");
  exit();
}

if (!$rewriteBaseIsCorrect) {
  $expectedRewriteBase = $_SERVER['REQUEST_URI'];
  $expectedRewriteBase = str_replace("install/", "", $expectedRewriteBase);
  $expectedRewriteBase = str_replace("index.php", "", $expectedRewriteBase);
  $expectedRewriteBase = str_replace("install.php", "", $expectedRewriteBase);
  if (REWRITE_BASE != $expectedRewriteBase) {
    \Surikata\Installer\HelperFunctions::echo("
      <div style='color:orange'>
        We think that your REWRITE_BASE is not configured properly.<br/>
        <br/>
        REWRITE_BASE that you have configured: <b>".REWRITE_BASE."</b><br/>
        REWRITE_BASE that we think is correct: <b>{$expectedRewriteBase}</b><br/>
        <br/>
        If you are sure that you configured your REWRITE_BASE correctly,
        click on the link below.<br/>
        <br/>
        <a href='?rewrite_base_is_correct=1'>REWRITE_BASE is correctly configured, continue with installation</a>
      </div>
    ");
    exit();
  }
}

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

$availableLanguages = [];
foreach (@scandir(__DIR__."/languages") as $file) {
  if (!in_array($file, [".", ".."])) {
    $availableLanguages[] = $file;
  }
}

$availableSlideshowImageSets = [];
foreach (@scandir(__DIR__."/content/images/slideshow") as $file) {
  if (!in_array($file, [".", ".."])) {
    $availableSlideshowImageSets[] = $file;
  }
}

$doInstall = ($installationConfig['do_install'] === "1");
$createPackage = $installationConfig['create_package'] ?? "";
$slideshowImageSet = $installationConfig['slideshow-image-set'];

$domainsToInstall = \Surikata\Installer\HelperFunctions::parseDomainsToInstall($installationConfig);

$randomProductsCount = $installationConfig['random-products-count'] ?? 50;
if ($randomProductsCount > 100000) $randomProductsCount = 100000;

$partsToInstall = [];
if (($installationConfig['product-catalog'] ?? "") == "yes") $partsToInstall[] = "product-catalog";
if (($installationConfig['delivery-and-payment-services'] ?? "") == "yes") $partsToInstall[] = "delivery-and-payment-services";
if (($installationConfig['customers'] ?? "") == "yes") $partsToInstall[] = "customers";
if (($installationConfig['orders'] ?? "") == "yes") $partsToInstall[] = "orders";

if (!$doInstall) {

  // $languageSelectOptions = "";
  // foreach ($availableLanguages as $availableLanguage) {
  //   $languageSelectOptions .= "
  //     <option value='{$availableLanguage}'>{$availableLanguage}</option>
  //   ";
  // }

  function _getDomainDescriptionInput($domainIndex, $value = "") {
    return "
      <input
        name='domain_{$domainIndex}_description'
        value='{$value}'
        style='width:300px'
      >
    ";
  }

  function _getDomainSlugInput($domainIndex, $value = "") {
    return "
      <input
        name='domain_{$domainIndex}_slug'
        value='{$value}'
        style='width:150px'
      >
    ";
  }

  function _getDomainLanguageIndexInput($domainIndex, $value = "") {
    $languages = [
      1 => "English",
      2 => "Slovensky",
      3 => "Česky",
    ];

    $html = "<select name='domain_{$domainIndex}_language_index'>";
    foreach ($languages as $languageIndex => $language) {
      $html .= "<option value='{$languageIndex}' ".($value == $languageIndex ? "selected" : "").">{$language}</option>";
    }
    $html .= "</select>";

    return $html;
  }

  function _getDomainThemeSelect($domainIndex, $availableThemes, $theme = "") {
    $html = "<select name='domain_{$domainIndex}_theme_name'>";
    foreach ($availableThemes as $availableTheme) {
      $html .= "<option value='{$availableTheme}' ".($theme == $availableTheme ? "selected" : "").">{$availableTheme}</option>";
    }
    $html .= "</select>";

    return $html;
  }

  $slideshowImageSetSelectOptions = "";
  foreach ($availableSlideshowImageSets as $availableSlideshowImageSet) {
    $slideshowImageSetSelectOptions .= "
      <option value='{$availableSlideshowImageSet}'>{$availableSlideshowImageSet}</option>
    ";
  }

  $themeSelectOptions = "";
  foreach ($availableThemes as $availableTheme) {
    $themeSelectOptions .= "
      <option value='{$availableTheme}'>{$availableTheme}</option>
    ";
  }

  \Surikata\Installer\HelperFunctions::echo("
    <form action='' method='GET'>
      <input type='hidden' name='do_install' value='1' />
      <input type='hidden' name='rewrite_base_is_correct' value='1' />

      <p>
        Your configuration is:
      </p>
      <table>
        <tr><td>REWRITE_BASE</td><td>".REWRITE_BASE."</td></tr>
        <tr><td>DB_HOST</td><td>".DB_HOST."</td></tr>
        <tr><td>DB_LOGIN</td><td>".DB_LOGIN."</td></tr>
        <tr><td>DB_NAME</td><td>".DB_NAME."</td></tr>
        <tr><td>SURIKATA_ROOT_DIR</td><td>".SURIKATA_ROOT_DIR."</td></tr>
        <tr><td>PROJECT_ROOT_DIR</td><td>".PROJECT_ROOT_DIR."</td></tr>
        <tr><td>CACHE_DIR</td><td>".CACHE_DIR."</td></tr>
        <tr><td>LOG_DIR</td><td>".LOG_DIR."</td></tr>
        <tr><td>DATA_DIR</td><td>".DATA_DIR."</td></tr>
        <tr><td>TWIG_CACHE_DIR</td><td>".TWIG_CACHE_DIR."</td></tr>
      </table>

      <p>
        Whitch parts do you want to install?
      </p>
      <table>
        <tr>
          <td><input type='checkbox' name='core' checked disabled></td>
          <td>Surikata Core</td>
        </tr>
        <tr>
          <td><input type='checkbox' name='website-content' checked disabled></td>
          <td>Website sitemap and basic content</td>
        </tr>
        <tr>
          <td><input type='checkbox' name='product-catalog' id='product-catalog' value='yes' checked></td>
          <td>
            <label for='product-catalog'>Sample product product catalog</label>
          </td>
          <td>
            <select name='random-products-count'>
              <option value='10' selected>10 random products</option>
              <option value='100'>100 random products</option>
              <option value='1000'>1000 random products</option>
              <option value='5000'>5000 random products</option>
            </select>
          </td>
        </tr>
        <tr>
          <td><input type='checkbox' name='customers' id='customers' value='yes' checked></td>
          <td>
            <label for='customers'>Sample set of customers</label>
          </td>
          <td>
            Each customer will get a password '0000'.
          </td>
        </tr>
        <tr>
          <td><input type='checkbox' name='delivery-and-payment-services' id='delivery-and-payment-services' value='yes' checked></td>
          <td><label for='delivery-and-payment-services'>Sample delivery and payment services</label></td>
        </tr>
        <tr>
          <td><input type='checkbox' name='orders' id='orders' value='yes' checked></td>
          <td><label for='orders'>Sample set of orders</label></td>
        </tr>
      </table>
      <p>
        Configure domains to install:
      </p>
      <table>
        <tr>
          <td><b>Slug</b></td>
          <td><b>Domain description</b></td>
          <td><b>Language</b></td>
          <td><b>Theme</b></td>
        </tr>
        <tr>
          <td>"._getDomainSlugInput(1, "hello-world")."</td>
          <td>"._getDomainDescriptionInput(1, "Developer`s Hello World example")."</td>
          <td>"._getDomainLanguageIndexInput(1, 1)."</td>
          <td>"._getDomainThemeSelect(1, $availableThemes, "HelloWorld")."</td>
        </tr>
        <tr>
          <td>"._getDomainSlugInput(2, "en")."</td>
          <td>"._getDomainDescriptionInput(2, "English version")."</td>
          <td>"._getDomainLanguageIndexInput(2, 1)."</td>
          <td>"._getDomainThemeSelect(2, $availableThemes)."</td>
        </tr>
        <tr>
          <td>"._getDomainSlugInput(3, "sk")."</td>
          <td>"._getDomainDescriptionInput(3, "Slovenská verzia")."</td>
          <td>"._getDomainLanguageIndexInput(3, 2)."</td>
          <td>"._getDomainThemeSelect(3, $availableThemes)."</td>
        </tr>
        <tr>
          <td>"._getDomainSlugInput(4, "cz")."</td>
          <td>"._getDomainDescriptionInput(4, "Česká verzia")."</td>
          <td>"._getDomainLanguageIndexInput(4, 3)."</td>
          <td>"._getDomainThemeSelect(4, $availableThemes)."</td>
        </tr>
      </table>
      <p style='color:#888888'>
        It is also possible to create more domains using the same language and with different design or
        product catalog filtered for a specific brand.
      </p>
      <!-- <select name='language_to_install'>
        {$languageSelectOptions}
      </select> -->
      <p>
        Select an image set for the homepage slideshow:
      </p>
      <select name='slideshow-image-set'>
        {$slideshowImageSetSelectOptions}
      </select>
      <!-- <p>
        Select a theme to use:
      </p>
      <select name='theme'>
        {$themeSelectOptions}
      </select> -->
      <!-- <p>
        Select a color scheme:<br/>
      </p>
      <div style='color:#888888'>[To be done]</div> -->
      <br/>
      <input type='submit' class='btn' value='Hurray! Create Surikata e-shop now.' />
    </form>
  ");
} else {

  try {

    $installationStart = microtime(TRUE);

    $installationLog = \Surikata\Installer\Installer::installSurikataProject(
      $adminPanelConfig,
      $websiteRendererConfig,
      $installationConfig
    );

  } catch (\Exception $e) {
    \Surikata\Installer\HelperFunctions::echo("
      <h2 style='color:red'>Error</h2>
      <div style='color:red'>
        ".get_class($e).": ".$e->getMessage()."
      </div>
    ");
    var_dump($e->getTrace());
  }

  if (count($installationLog["errors"]) > 0) {
    \Surikata\Installer\HelperFunctions::echo("
      <h2 style='color:red'>Awgh!</h2>
      <div style='color:red;margin-bottom:1em'>
        ✕ Some errors occured during the installation.
      </div>
      <div style='color:red'>{$installationLog["errorsHtml"]}</div>
    ");
  } else {
    \Surikata\Installer\HelperFunctions::echo("
      <h2>Done in ".round((microtime(true) - $installationStart), 2)." seconds.</h2>
      <div style='color:green;margin-bottom:1em'>
        ✓ Congratulations. You have successfuly installed your eCommerce project.
      </div>
      <div style='color:orange;margin-bottom:1em'>
        ⚠ WARNING: You should delete the <i>install</i> folder now.
      </div>
      <table>
        <tr><td>Slideshow image set</td><td>{$slideshowImageSet}</td></tr>
        <tr><td>Sample set of products</td><td>".(in_array("product-catalog", $partsToInstall) ? "yes" : "no")."</td></tr>
        <tr><td>Random products count</td><td>{$randomProductsCount}</td></tr>
        <tr><td>Sample set of customers</td><td>".(in_array("customers", $partsToInstall) ? "yes" : "no")."</td></tr>
        <tr><td>Sample set of delivery and payment services</td><td>".(in_array("delivery-and-payment-services", $partsToInstall) ? "yes" : "no")."</td></tr>
        <tr><td>Sample set of orders</td><td>".(in_array("orders", $partsToInstall) ? "yes" : "no")."</td></tr>
      </table>
      <br/>
      ".(count($installationLog["warnings"]) > 0 ? "
        <h2>Warnings</h2>
        <div style='color:orange'>{$installationLog["warningsHtml"]}</div>
      " : "")."
      <br/>
      <!-- <a href='..' class='btn' target=_blank>Go to your e-shop</a> -->
      <a href='../admin' class='btn' target=_blank>Open administration panel</a><br/>
      Login: administrator<br/>
      Password: administrator<br/>
    ");
  }

  \Surikata\Installer\HelperFunctions::echo("
    <br/>
    <h2>Installation log</h2>
    <a
      href='javascript:void(0)'
      onclick='
        document.getElementById(\"log\").style.display = \"block\";
        this.style.display = \"none\";
      '
    >Show installation log</a>
    <div id='log' style='display:none'>{$installationLog["infosHtml"]}</div>
  ");

}

\Surikata\Installer\HelperFunctions::echo("
    </div>
  </body>
  </html>
");