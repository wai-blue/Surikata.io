<?php

namespace Surikata\Themes;

class AbeloTheme extends \Surikata\Core\Web\Theme {

  public function getDefaultColorsAndStyles() {
    return [
      "themeMainColor" => "#17C3B2",
      "themeSecondColor" => "#222222",
      "themeThirdColor" => "#FE6D73",
      "themeGreyColor" => "#888888",
      "themeLightGreyColor" => "#f5f5f5",

      "bodyBgColor" => "#ffffff",
      "bodyTextColor" => "#333333",
      "bodyLinkColor" => "#17C3B2",
      "bodyHeadingColor" => "#333333",

      "headerBgColor" => "#fff",
      "headerTextColor" => "#333333",
      "headerLinkColor" => "#17C3B2",
      "headerHeadingColor" => "#ffffff",

      "footerBgColor" => "#fff",
      "footerTextColor" => "#272727",
      "footerLinkColor" => "#272727",
      "footerHeadingColor" => "#ffffff",

      "custom_css" => "
        li.slideshow-basic {
          background: rgb(29,6,7);
          background: linear-gradient(180deg, rgba(29,6,7,1) 0%, rgba(29,6,7,0.75) 15%, rgba(73,18,18,0.6) 35%, rgba(156,36,38,0) 100%);
        }
        .rslides {
          background: #000;
        }
      ",
    ];
  }

}