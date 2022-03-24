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
    $translatedColumns = [];
    $domainLanguages = $this->adios->config['widgets']['Website']['domainLanguages'];

    foreach ($domainLanguages as $languageIndex => $languageName) {
      $translatedColumns["name_lang_{$languageIndex}"] = [
        "type" => "varchar",
        "title" => $this->translate("Name")." ({$languageName})",
        "show_column" => ($languageIndex == $this->adios->translatedColumnIndex),
        "is_searchable" => ($languageIndex == $this->adios->translatedColumnIndex),
      ];
    }

    $columns = parent::columns(array_merge(
      $translatedColumns, []
    ));

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