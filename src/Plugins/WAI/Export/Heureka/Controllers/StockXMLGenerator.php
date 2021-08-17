<?php

namespace Surikata\Plugins\WAI\Export\Heureka\Controllers;

use \ADIOS\Widgets\Products\Models\Product;
use \Surikata\Plugins\WAI\Product\Detail;

class StockXMLGenerator extends \Surikata\Core\Web\Controller {
  public function getHeurekaProductId($product) {
    return substr(md5($product['id']), 0, 5).".".str_pad((string) $product['id'], 8, "0", STR_PAD_LEFT);
  }

  public function render() {
    $xml = "bla";

    // file_put_contents(__DIR__."/test.xml", $xml);
    
    return $xml;
  }
}