<?php

namespace ADIOS\Widgets\Products\Models;

class ProductVariationGroup extends \ADIOS\Core\Model {
  var $sqlName = "products_variations_groups";
  var $urlBase = "Products/Variations/Groups";
  var $lookupSqlValue = "{%TABLE%}.id_variation_group";

  var $isCrossTable = TRUE; // nebude obsahovat stlpec ID

  public function init() {
    $this->tableTitle = $this->translate("Product variation groups");
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

    $columns = parent::columns([

      // stlpec je id_*, ale nie je lookup
      // pretoze tato tabulka je cross tabulka a id_variation_group, ak by bolo lookup,
      // tak by sa muselo odkazovat samo na seba
      "id_variation_group" => [
        "type" => "int",
        "title" => $this->translate("Variation Group ID"),
      ],

      "id_variation" => [
        "type" => "lookup",
        "title" => $this->translate("Variation"),
        "model" => "Widgets/Products/Models/ProductVariation",
      ],
    ]);

    return $columns;
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      [
        "type" => "unique",
        "columns" => ["id_variation_group", "id_variation"],
      ],
    ]);
  }

  public function getByIdVariationGroup(int $idVariationGroup) {
    return $this->where("id_variation_group", $idVariationGroup)->pluck('id_variation')->toArray();
  }

}