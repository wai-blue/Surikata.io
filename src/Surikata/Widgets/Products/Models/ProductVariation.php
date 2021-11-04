<?php

namespace ADIOS\Widgets\Products\Models;

class ProductVariation extends \ADIOS\Core\Model {
  var $sqlName = "products_variations";
  var $urlBase = "Products/Variations";
  var $lookupSqlValue = "{%TABLE%}.name_lang_1";

  public function init() {
    $this->tableTitle = $this->translate("Product variations");
  }

  public function columns(array $columns = []) {
    $translatedColumns = [];
    $domainLanguages = $this->adios->config['widgets']['Website']['domainLanguages'];

    foreach ($domainLanguages as $languageIndex => $languageName) {
      $translatedColumns["name_lang_{$languageIndex}"] = [
        "type" => "varchar",
        "title" => $this->translate("Name")." ({$languageName})",
        "show_column" => ($languageIndex == 1),
        "is_searchable" => ($languageIndex == 1),
      ];
    }

    $columns = parent::columns(array_merge(
      $translatedColumns,
      []
    ));

    return $columns;
  }

  public function routing(array $routing = []) {
    return parent::routing([
      '/^Products\/(\d+)\/Variations\/EditValues$/' => [
        "action" => "Products/Variations/EditValues",
        "params" => [
          "idProduct" => '$1',
        ]
      ],
      // '/^Products\/(\d+)\/Variations\/Step2$/' => [
      //   "action" => "Products/Variations/Step2",
      //   "params" => [
      //     "idProduct" => '$1',
      //   ]
      // ],
    ]);
  }

}