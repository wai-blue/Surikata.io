<?php

namespace CASCADA;

function str2url($string, $replace_slashes = true) {
  if ($replace_slashes) {
    $string = str_replace('/', '-', $string);
  }

  $string = preg_replace('/ |^(a-z0-9)/', '-', strtolower(_rmspecialchars(_rmdiacritic($string))));

  $string = preg_replace('/[^(\x20-\x7F)]*/', '', $string);
  $string = preg_replace('/[^(\-a-z0-9)]*/', '', $string);
  $string = trim($string, '-');

  while (strpos($string, '--')) {
    $string = str_replace('--', '-', $string);
  }

  return $string;
}

