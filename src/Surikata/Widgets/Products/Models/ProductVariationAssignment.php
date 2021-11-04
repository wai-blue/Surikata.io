<?php

namespace ADIOS\Widgets\Products\Models;

class ProductVariationAssignment extends \ADIOS\Core\Model {
  var $sqlName = "products_variations_assignments_values";
  var $urlBase = "Products/Variations/Assignments/Values";
  var $lookupSqlValue = "concat({%TABLE%}.id_variation, ' - ', {%TABLE%}.id_value)";

  var $isCrossTable = TRUE;

  public function init() {
    $this->tableTitle = $this->translate("Product variation assignment value");
  }

  public function columns(array $columns = []) {
    $columns = parent::columns([
      // stlpec je id_*, ale nie je lookup
      // pretoze tato tabulka je cross tabulka a id_variation_group, ak by bolo lookup,
      // tak by sa muselo odkazovat samo na seba
      "id_variation_group" => [
        "type" => "int",
        "title" => $this->translate("Variation Group ID"),
      ],

      "id_product" => [
        "type" => "lookup",
        "title" => $this->translate("Product"),
        "model" => "Widgets/Products/Models/Product",
        "readonly" => TRUE,
      ],

      "id_variation" => [
        "type" => "lookup",
        "title" => $this->translate("Variation"),
        "model" => "Widgets/Products/Models/ProductVariation",
        "show_column" => TRUE,
      ],

      "id_value" => [
        "type" => "lookup",
        "title" => $this->translate("Value"),
        "model" => "Widgets/Products/Models/ProductVariationValue",
        "show_column" => TRUE,
      ],
    ]);

    return $columns;
  }

  // public function indexes(array $indexes = []) {
  //   return parent::indexes([
  //     [
  //       "type" => "unique",
  //       "columns" => ["id_variation_group", "id_variation", "id_value"],
  //     ],
  //   ]);
  // }

  public function tableParams($params) {
    $params["where"] = "{$this->table}.`id_product` = ".(int) $params['id_product'];
    $params['show_search_button'] = FALSE;
    $params['show_export_csv_button'] = FALSE;
    $params['show_controls'] = FALSE;
    $params['show_filter'] = FALSE;
    $params['title'] = " ";

    return $params;
  }

  public function formParams($data, $params) {
    $params['default_values'] = ['id_product' => (int) $params['id_product']];
    return $params;
  }

}