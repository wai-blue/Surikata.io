<?php

namespace ADIOS\Widgets\Products\Models;

class ProductFeatureOption extends \ADIOS\Core\Widget\Model {
  var $sqlName = "products_features_options";
  var $urlBase = "Products/Features/Options";

  public function init() {
    $this->tableTitle = $this->translate("Product feature option");
    $this->formTitleForInserting = $this->translate("New product feature option");
    $this->formTitleForEditing = $this->translate("Product feature option");
  }

  public function columns(array $columns = []) {

    $columns = parent::columns(
      [
        "name" => [
         "type" => "varchar",
         "title" => $this->translate("Name"),
         "show_column" => TRUE,
        ]
      ]
    );

    return $columns;
  }

  public function tableParams($params) {
    $params['show_search_button'] = FALSE;
    return $params;
  }

  public function getSelectedOptions(array $optionsList = []) {
    return 
      (!empty($optionsList) 
        ? self::whereIn('id', $optionsList)->get()->toArray()
        : []
      )
    ;
  }

  public function getOptionNamesFromArray(array $optionsList = []) {
    $names = [];
    foreach ($optionsList as $option) {
      $names[] = $option["name"];
    }
    return $names;
  }

}