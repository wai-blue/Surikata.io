<?php

namespace ADIOS\Widgets\Products\Models;

class ProductVariationValue extends \ADIOS\Core\Model {
  var $sqlName = "products_variations_values";
  var $urlBase = "Products/Variations/Values";
  var $lookupSqlValue = "{%TABLE%}.value_lang_1";

  public function init() {
    $this->tableTitle = $this->translate("Product variations values");
  }

  public function columns(array $columns = []) {
    $translatedColumns = [];
    $domainLanguages = $this->adios->config['widgets']['Website']['domainLanguages'];

    foreach ($domainLanguages as $languageIndex => $languageName) {
      $translatedColumns["value_lang_{$languageIndex}"] = [
        "type" => "varchar",
        "title" => $this->translate("Value")." ({$languageName})",
        "show_column" => ($languageIndex == 1),
        "is_searchable" => ($languageIndex == 1),
      ];
    }

    $columns = parent::columns(array_merge(
      $translatedColumns,
      [
        "id_variation" => [
          "type" => "lookup",
          "title" => $this->translate("Variation"),
          "model" => "Widgets/Products/Models/ProductVariation",
          "show_column" => TRUE,
        ],
      ]
    ));

    return $columns;
  }

  public function getByIdVariation(int $idVariationGroup) {
    $tmp = $this->where("id_variation", $idVariationGroup)->get()->toArray();
    $variations = [];
    foreach ($tmp as $key => $value) {
      $variations[$value['id']] = $value;
    }
    return $variations;
  }

}