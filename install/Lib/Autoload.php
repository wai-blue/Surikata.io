<?php

spl_autoload_register(function($class) {
  $class = trim(str_replace("\\", "/", $class), "/");

  if (preg_match('/^Surikata\/Installer\/([\w\/]+)/', $class, $m)) {
    require_once __DIR__."/{$m[1]}.php";
  }
});

