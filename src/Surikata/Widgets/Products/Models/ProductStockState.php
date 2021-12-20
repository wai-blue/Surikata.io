<?php

namespace ADIOS\Widgets\Products\Models;

class ProductStockState extends \ADIOS\Core\Widget\Model {
  var $sqlName = "products_stock_states";
  var $urlBase = "Products/StockStates";

  public function init() {
    $this->tableTitle = $this->translate("Product stock state");
    $this->formTitleForInserting = $this->translate("New product stock state");
    $this->formTitleForEditing = $this->translate("Product stock state");
    $this->lookupSqlValue = "{%TABLE%}.name_lang_{$this->adios->translatedColumnIndex}";
  }

  public function columns(array $columns = []) {
    $domainLanguages = $this->adios->config['widgets']['Website']['domainLanguages'];

    $translatedColumns = [];
    foreach ($domainLanguages as $languageIndex => $languageName) {
      $translatedColumns["name_lang_{$languageIndex}"] = [
        "type" => "varchar",
        "title" => $this->translate("Stock state")." (".$this->translate($languageName).")",
        "show_column" => ($languageIndex == $this->adios->translatedColumnIndex),
        "is_searchable" => ($languageIndex == $this->adios->translatedColumnIndex),
      ];
    }

    return parent::columns(array_merge(
      $translatedColumns,
      []
    ));
  }

}