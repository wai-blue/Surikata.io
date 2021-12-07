<?php

if (defined('WEBSITE_REWRITE_BASE')) {
  $relativeRequestUri = str_replace(WEBSITE_REWRITE_BASE, "", $_SERVER['REQUEST_URI']);

  if (strpos($relativeRequestUri, "theme/assets") !== FALSE) {
    $ext = strtolower(pathinfo($relativeRequestUri, PATHINFO_EXTENSION));

    $assetCacheFile = $websiteRendererConfig["assetsCacheDir"]."/".md5($relativeRequestUri).".{$ext}";

    if (file_exists($assetCacheFile)) {

      $assetContent = file_get_contents($assetCacheFile);

      switch ($ext) {
        case "css":
        case "js":
          header("Content-type: text/{$ext}");
          header($headerExpires);
          header("Pragma: cache");
          header($headerCacheControl);
          echo $assetContent;
        break;
        case "eot":
        case "ttf":
        case "woff":
        case "woff2":
          header("Content-type: font/{$ext}");
          header($headerExpires);
          header("Pragma: cache");
          header($headerCacheControl);
          echo $assetContent;
        break;
        case "bmp":
        case "gif":
        case "jpg":
        case "jpeg":
        case "png":
        case "tiff":
        case "webp":
        case "svg":
          if ($ext == "svg") {
            $contentType = "svg+xml";
          } else {
            $contentType = $ext;
          }

          header("Content-type: image/{$contentType}");
          header($headerExpires);
          header("Pragma: cache");
          header($headerCacheControl);
          echo $assetContent;
        break;
      }

      exit();
    }
  }
}