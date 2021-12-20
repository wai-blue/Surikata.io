<?php

namespace ADIOS\Widgets\Settings\Models;

class Unit extends \ADIOS\Core\Widget\Model {
  var $sqlName = "units";
  var $urlBase = "Settings/Units";

  public function init() {
    $this->tableTitle = $this->translate("Units");
    $this->formTitleForInserting = $this->translate("New unit");
    $this->formTitleForEditing = $this->translate("Unit");

    $this->lookupSqlValue = "
      if(
        {%TABLE%}.name_lang_{$this->adios->translatedColumnIndex} is null,
        {%TABLE%}.unit,
        concat({%TABLE%}.unit, ' (', {%TABLE%}.name_lang_{$this->adios->translatedColumnIndex}, ')')
      )
    ";
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
      [
        "unit" => [
          "type" => "varchar",
          "title" => $this->translate("Unit"),
          "description" => $this->translate("E.g.: mm, kg, l, btl, pkg, ..."),
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
      ]
    ));
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