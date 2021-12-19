<?php

namespace Surikata\Themes;

class HelloWorld extends \Surikata\Core\Web\Theme {

  public function getDefaultColorsAndStyles($installer) {
    return [
      "themeMainColor" => "#17C3B2",
      "themeSecondColor" => "#222222",
      "themeThirdColor" => "#FE6D73",
    ];
  }

}