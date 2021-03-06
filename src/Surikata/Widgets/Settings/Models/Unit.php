<?php

namespace ADIOS\Widgets\Settings\Models;

class Unit extends \ADIOS\Core\Model {
  var $sqlName = "units";
  var $urlBase = "Settings/Units";
  var $lookupSqlValue = "if({%TABLE%}.name is null, {%TABLE%}.unit, concat({%TABLE%}.unit, ' (', {%TABLE%}.name, ')'))";

  public function init() {
    $this->tableTitle = $this->translate("Units");
    $this->formTitleForInserting = $this->translate("New unit");
    $this->formTitleForEditing = $this->translate("Unit");
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "unit" => [
        "type" => "varchar",
        "title" => $this->translate("Unit"),
        "description" => $this->translate("E.g.: mm, kg, l, btl, pkg, ..."),
        "show_column" => TRUE,
        "required" => TRUE,
      ],

      "name" => [
        "type" => "varchar",
        "title" => $this->translate("Unit name"),
        "description" => $this->translate("E.g.: milimetres, kilogramms, litres, bottles, packages, ..."),
        "show_column" => TRUE,
        "required" => TRUE,
      ],

      "is_for_products" => [
        "type" => "boolean",
        "title" => $this->translate("Is for products"),
        "description" => $this->translate("If checked, this unit will be available as a delivery unit for product."),
        "show_column" => TRUE,
      ],

      "is_for_features" => [
        "type" => "boolean",
        "title" => $this->translate("Is for features"),
        "description" => $this->translate("If checked, this unit will be available as a unit for a product feature."),
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