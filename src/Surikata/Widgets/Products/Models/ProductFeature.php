<?php

namespace ADIOS\Widgets\Products\Models;

class ProductFeature extends \ADIOS\Core\Widget\Model {
  var $sqlName = "products_features";
  var $urlBase = "Products/Features";

  public static $allItemsCache = NULL;

  public function init() {
    $this->formTitleForInserting = $this->translate("New product feature");
    $this->formTitleForEditing = $this->translate("Product feature");
    $this->tableTitle = $this->translate("Product features");

    // TODO: cisla zamenit za konstanty
    $this->enumValuesValueType = [
      1 => $this->translate("Number"),
      2 => $this->translate("Text"),
      3 => $this->translate("Yes/No"),
    ];

    // TODO: cisla zamenit za konstanty
    $this->enumValuesEntryMethod = [
      1 => "Slider",
      2 => "Select",
      3 => "Radio",
      4 => "Checkbox",
      5 => "Text",
    ];
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
      $translatedColumns["description_lang_{$languageIndex}"] = [
        "type" => "text",
        "title" => $this->translate("Description")." ({$languageName})",
        "interface" => "formatted_text",
        "show_column" => ($languageIndex == 1),
        "is_searchable" => ($languageIndex == 1),
      ];
    }

    $columns = parent::columns(array_merge(
      $translatedColumns,
      [
        "icon" => [
          'type' => 'image',
          'title' => $this->translate("Icon"),
          "description" => $this->translate("Supported image extensions: jpg, gif, png, jpeg"),
          'show_column' => TRUE,
          "subdir" => "feature_icons",
        ],

        "value_type" => [
          "type" => "int",
          "title" => $this->translate("Type of value"),
          "enum_values" => $this->enumValuesValueType,
          "show_column" => TRUE,
        ],

        "id_measurement_unit" => [
          "type" => "lookup",
          "title" => $this->translate("Measurement unit"),
          "model" => "Widgets/Settings/Models/Unit",
        ],

        "entry_method" => [
          "type" => "int",
          "title" => $this->translate("Entry method"),
          "enum_values" => $this->enumValuesEntryMethod,
          "show_column" => TRUE,
        ],

        "min" => [
          "type" => "int",
          "title" => $this->translate("Minimum value"),
          "show_column" => TRUE,
        ],

        "max" => [
          "type" => "int",
          "title" => $this->translate("Maximum value"),
          "show_column" => TRUE,
        ],

        "order_index" => [
          "type" => "int",
          "title" => $this->translate("Order index"),
          "show_column" => TRUE,
        ],

      ]
    ));

    return $columns;
  }

  public function lookupSqlValue($tableAlias = NULL) {
    $unitModel = $this->adios->getModel("Widgets/Settings/Models/Unit");

    $value = "
      concat(
        {%TABLE%}.name_lang_1,
        ' [',
        ifnull(
          (
            select
              unit
            from {$unitModel->table} u
            where u.id = {%TABLE%}.id_measurement_unit
          ),
          'N/A'
        ),
        ']'
      )
    ";

    return ($tableAlias !== NULL
      ? str_replace('{%TABLE%}', "`{$tableAlias}`", $value)
      : $value
    );
  }

  public function tableParams($params) {
    $params['show_search_button'] = FALSE;
    return $params;
  }

  public function formParams($data, $params) {
    $params['default_values'] = [
      'id_parent' => $params['id_parent']
    ];

    if ($data['id'] > 0) {
      $params['title'] = $data['name_lang_1'];
      $params['subtitle'] = "Product feature";
    }

    $params['columns']['id_parent']['readonly'] = $params['id_parent'] > 0;

    $tabTranslations = [];
    $domainLanguages = $this->adios->config['widgets']['Website']['domainLanguages'];

    $i = 1;
    foreach ($domainLanguages as $languageIndex => $languageName) {
      if ($i > 1) {
        $tabTranslations[] = ["html" => "<b>".hsc($languageName)."</b>"];
        $tabTranslations[] = "name_lang_{$languageIndex}";
        $tabTranslations[] = "description_lang_{$languageIndex}";
      }
      $i++;
    }

    if (count($tabTranslations) == 0) {
      $tabTranslations[] = ["html" => $this->translate("No translations available.")];
    }

    $params["template"] = [
      "columns" => [
        [
          "class" => "col-md-9 pl-0",
          "tabs" => [
            $this->translate("General") => [
              "name_lang_1",
              "description_lang_1",
              "icon",
              "value_type",
              "id_measurement_unit",
              "entry_method",
              "min",
              "max",
              "order_index",
            ],
            $this->translate("Translations") => $tabTranslations,
          ],
        ],
      ],
    ];

    $params['onload'] = "
      if ($('#WidgetsProductsModelsProductFeature_1_value_type').val() == 1) {
        $('#WidgetsProductsModelsProductFeature_1_min').removeAttr('disabled');
        $('#WidgetsProductsModelsProductFeature_1_max').removeAttr('disabled');
      }
      else {
        $('#WidgetsProductsModelsProductFeature_1_min').attr('disabled','disabled');
        $('#WidgetsProductsModelsProductFeature_1_max').attr('disabled','disabled');
      }
    ";

    $params['columns']['min']['disabled'] = true;
    $params['columns']['max']['disabled'] = true;

    $params['columns']['value_type']['onchange'] = "
      if ($(this).val() == 1) {
        $('#WidgetsProductsModelsProductFeature_1_min').removeAttr('disabled');
        $('#WidgetsProductsModelsProductFeature_1_max').removeAttr('disabled');
        $('#WidgetsProductsModelsProductFeature_1_entry_method').val('5');
      }
      else {
        $('#WidgetsProductsModelsProductFeature_1_min').attr('disabled','disabled');
        $('#WidgetsProductsModelsProductFeature_1_max').attr('disabled','disabled');
      }
      if ($(this).val() == 2) {
        $('#WidgetsProductsModelsProductFeature_1_entry_method').val('5');
      }
      if ($(this).val() == 3) {
        $('#WidgetsProductsModelsProductFeature_1_entry_method').val('4');
      }
    ";

    return $params;
  }

  public function translateProductFeatureForWeb($productFeature, $languageIndex) {
    $productFeature["TRANSLATIONS"]["name"] = $productFeature["name_lang_{$languageIndex}"];

    return $productFeature;
  }

}