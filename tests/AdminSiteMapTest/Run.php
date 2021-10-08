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

try {

  require_once(__DIR__."/../../Init.php");

  function addReport($url, $report) {
  }

  $web = new \MyEcommerceProject\Web($websiteRendererConfig);
  
  $adminPanel = new \MyEcommerceProject\AdminPanel(
    $adminPanelConfig,
    ADIOS_MODE_FULL,
    $web
  );

  $siteMap = [];

  foreach ($adminPanel->modelObjects as $model) {
    // $model = $adminPanel->getModel($modelName);
    if (!empty($model->urlBase)) {
      if (strpos($model->urlBase, "{{") === FALSE) {
        $siteMap[] = $model->urlBase;
      } else {
        foreach ($model->getAll() as $item) {
          $siteMap[] = $model->getFullUrlBase($item);
          break;
        }
      }
    }
  }

  foreach ($siteMap as $relativeUrl) {
    $url = str_replace("//", "http://", "{$adminPanel->config['url']}/{$relativeUrl}");
    $html = \ADIOS\Core\HelperFunctions::loadUrl($url);
    
    addReport($url, $);
    break;
  }

  var_dump($html);

} catch (\Exception $e) {
  echo $e->getMessage();
}