<?php

namespace ADIOS\Widgets\Products\Models;

class ProductStockState extends \ADIOS\Core\Model {
  var $sqlName = "products_stock_states";
  var $urlBase = "Products/StockStates";
  var $tableTitle = "Product stock state";
  var $formTitleForInserting = "New product stock state";
  var $formTitleForEditing = "Product stock state";
  var $lookupSqlValue = "{%TABLE%}.name_lang_1";

  public function columns(array $columns = []) {
    $domainLanguages = $this->adios->config['widgets']['Website']['domainLanguages'];

    foreach ($domainLanguages as $languageIndex => $languageName) {
      $translatedColumns["name_lang_{$languageIndex}"] = [
        "type" => "varchar",
        "title" => $this->translate("Stock state")." (".$this->translate($languageName).")",
        "show_column" => ($languageIndex == 1),
        "is_searchable" => ($languageIndex == 1),
      ];
    }

    return parent::columns(array_merge(
      $translatedColumns,
      []
    ));
  }

}