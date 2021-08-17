<?php

namespace ADIOS\Actions\Plugins\WAI\Export\Heureka;

class Main extends \ADIOS\Core\Action {
  public function render() {
    $productsXMLUrl = "//".$_SERVER['HTTP_HOST'].WEBSITE_REWRITE_BASE."heureka-products.xml";
    $stockXMLUrl = "//".$_SERVER['HTTP_HOST'].WEBSITE_REWRITE_BASE."heureka-stock.xml";

    return "
      <h1>Heureka XML feedy</h1>

      <a href='{$productsXMLUrl}' target=_blank>XML so zoznamom produktov</a><br/>
      <a href='{$stockXMLUrl}' target=_blank>XML s dostupnosťou produktov</a><br/>
      <br/>
      <br/>
      <a href='javascript:void(0)' onclick='window_render(\"Plugins/WAI/Export/Heureka/Settings\");'>Nastavenia</a><br/>
    ";
  }
}
