<?php

namespace ADIOS\Core\Lib;

class TwigLoader implements \Twig\Loader\LoaderInterface {
  public function __construct(&$adios) {
    $this->adios = $adios;
  }

  /**
    * Returns the source context for a given template logical name.
    *
    * @param string $name The template logical name
    * @return \Twig\Source
    * @throws \Twig\Error\LoaderError When $name is not found
    */
  public function getSourceContext($name): \Twig\Source {
    $templateName = str_replace("\\", "/", $name);

    if (strpos($templateName, "ADIOS/Templates/Widgets/") === 0) {
      $templateName = str_replace("ADIOS/Templates/Widgets/", "", $templateName);
      $widget = substr($templateName, 0, strpos($templateName, "/"));
      $action = substr($templateName, strpos($templateName, "/") + 1);

      $templateFile = ADIOS_WIDGETS_DIR."/{$widget}/Templates/{$action}.twig";
    } else if (strpos($templateName, "ADIOS/Templates/") === 0) {
      $templateName = str_replace("ADIOS/Templates/", "", $templateName);

      // najprv skusim hladat core template...
      $templateFile = dirname(__FILE__)."/../../Templates/{$templateName}.twig";

      // ...potom Widget akciu
      if (!is_file($templateFile)) {
        preg_match('/(\w+)\/([\w\/]+)/', $templateName, $m);
        $templateFile = ADIOS_WIDGETS_DIR."/{$m[1]}/Templates/{$m[2]}.twig";
      }

      // ...a nakoniec Plugin akciu
      if (!is_file($templateFile)) {
        preg_match('/(\w+)\/([\w\/]+)/', $templateName, $m);
        foreach ($this->adios->pluginFolders as $pluginFolder) {
          $folder = $pluginFolder."/{$this->name}/Models";

          $templateFile = "{$folder}/{$m[1]}/Templates/{$m[2]}.twig";
          if (is_file($templateFile)) {
            break;
          }
        }
      }

    } else {
      return new \Twig\Source("", $name);
    }

    if (!is_file($templateFile)) {
      throw new \Twig\Error\LoaderError("Template {$name} does not exist. {$templateName}");
    } else {
      return new \Twig\Source(file_get_contents($templateFile), $name);
    }
  }

  /**
    * Gets the cache key to use for the cache for a given template name.
    *
    * @param string $name The name of the template to load
    *
    * @return string The cache key
    *
    * @throws \Twig\Error\LoaderError When $name is not found
    */
  public function getCacheKey($name): string {
    return $name;
  }

  /**
    * Returns true if the template is still fresh.
    *
    * @param string    $name The template name
    * @param timestamp $time The last modification time of the cached template
    *
    * @return bool    true if the template is fresh, false otherwise
    *
    * @throws \Twig\Error\LoaderError When $name is not found
    */
  public function isFresh(string $name, int $time): bool {
    return true;
  }

  /**
    * Check if we have the source code of a template, given its name.
    *
    * @param string $name The name of the template to check if we can load
    *
    * @return bool    If the template source code is handled by this loader or not
    */
  public function exists(string $name) {
    return (strpos($name, "ADIOS/Templates/") === 0);
  }
}