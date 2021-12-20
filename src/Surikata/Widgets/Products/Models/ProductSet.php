<?php

namespace ADIOS\Widgets\Products\Models;

class ProductSet extends \ADIOS\Core\Widget\Model {
  var $sqlName = "products_sets";
  var $urlBase = "Products/Sets";

  public function init() {
    $this->tableTitle = $this->translate("Product sets");
    $this->formTitleForInserting = $this->translate("New product set");
    $this->formTitleForEditing = $this->translate("Product Set");
    $this->lookupSqlValue = "{%TABLE%}.name_lang_{$this->adios->translatedColumnIndex}";
  }

  public function columns(array $columns = []) {
    $translatedColumns = [];
    $domainLanguages = $this->adios->config['widgets']['Website']['domainLanguages'];

    foreach ($domainLanguages as $languageIndex => $languageName) {
      $translatedColumns["name_lang_{$languageIndex}"] = [
        "type" => "varchar",
        "title" => $this->translate("Name")." (".$this->translate($languageName).")",
        "show_column" => ($languageIndex == $this->adios->translatedColumnIndex),
        "is_searchable" => ($languageIndex == $this->adios->translatedColumnIndex),
      ];
    }

    return parent::columns(array_merge(
      $translatedColumns,
      []
    ));
  }

  public function tableParams($params) {
    $params['header'] = "
      Product sets are used to group products that you want to sell as a set with a special price
      or discount.
      <br/>
      This list only for managing product set information, like names, prices and/or discount rates.
      If you want to add a product to the set, go to the product catalog, open desired product
      and scroll to tab 'Sets'.
    ";
    return $params;
  }
}