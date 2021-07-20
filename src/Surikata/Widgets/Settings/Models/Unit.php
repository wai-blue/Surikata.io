<?php

namespace ADIOS\Widgets\Settings\Models;

class Unit extends \ADIOS\Core\Model {
  var $sqlName = "units";
  var $urlBase = "Settings/Units";
  var $tableTitle = "Units";
  var $formTitleForInserting = "New unit";
  var $formTitleForEditing = "Unit";
  var $lookupSqlValue = "if({%TABLE%}.name is null, {%TABLE%}.unit, concat({%TABLE%}.unit, ' (', {%TABLE%}.name, ')'))";

  public function columns(array $columns = []) {
    return parent::columns([
      "unit" => [
        "type" => "varchar",
        "title" => "Unit",
        "description" => "E.g.: mm, kg, l, btl, pkg, ...",
        "show_column" => TRUE,
        "required" => TRUE,
      ],

      "name" => [
        "type" => "varchar",
        "title" => "Unit name",
        "description" => "E.g.: milimetres, kilogramms, litres, bottles, packages, ...",
        "show_column" => TRUE,
        "required" => TRUE,
      ],

      "is_for_products" => [
        "type" => "boolean",
        "title" => "Is for products",
        "description" => "If checked, this unit will be available as a delivery unit for product.",
        "show_column" => TRUE,
      ],

      "is_for_features" => [
        "type" => "boolean",
        "title" => "Is for features",
        "description" => "If checked, this unit will be available as a unit for a product feature.",
        "show_column" => TRUE,
      ],
    ]);
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "unit" => [
        "type" => "index",
        "columns" => ["unit"],
      ],
    ]);
  }

  public function product() {
    return $this->belongsTo(\ADIOS\Widgets\Products\Models\Product::class, "id_delivery_unit");
  }

  // $initiatingModel = model formulara, v ramci ktoreho je lookup generovany
  // $initiatingColumn = nazov stlpca, z ktoreho je lookup generovany
  // $formData = aktualne data formulara
  public function lookupSqlWhere($initiatingModel = NULL, $initiatingColumn = NULL, $formData = [], $params = []) {
    if (
      $initiatingModel == "Widgets/Products/Models/Product"
      && $initiatingColumn == "id_delivery_unit"
    ) {
      return "`{$this->table}`.is_for_products = TRUE";
    } else if (
      $initiatingModel == "Widgets/Products/Models/ProductFeature"
      && $initiatingColumn == "id_measurement_unit"
    ) {
      return "`{$this->table}`.is_for_features = TRUE";
    } else {
      return "TRUE";
    }
  }

}