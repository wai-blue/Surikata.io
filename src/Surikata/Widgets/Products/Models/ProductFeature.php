<?php

namespace ADIOS\Widgets\Products\Models;

class ProductFeature extends \ADIOS\Core\Widget\Model {
  var $sqlName = "products_features";
  var $urlBase = "Products/Features";

  public static $allItemsCache = NULL;

  const FEATURE_DATA_TYPE_NUMBER  = 1;
  const FEATURE_DATA_TYPE_TEXT    = 2;
  const FEATURE_DATA_TYPE_BOOLEAN = 3;

  const ENTRY_METHOD_SLIDER   = 1;
  const ENTRY_METHOD_SELECT   = 2;
  const ENTRY_METHOD_RADIO    = 3;
  const ENTRY_METHOD_CHECKBOX = 4;
  const ENTRY_METHOD_TEXT     = 5;

  public function init() {
    $this->formTitleForInserting = $this->translate("New product feature");
    $this->formTitleForEditing = $this->translate("Product feature");
    $this->tableTitle = $this->translate("Product features");

    $this->enumValuesValueType = [
      self::FEATURE_DATA_TYPE_NUMBER => $this->translate("Number"),
      self::FEATURE_DATA_TYPE_TEXT => $this->translate("Text"),
      self::FEATURE_DATA_TYPE_BOOLEAN => $this->translate("Yes/No"),
    ];

    $this->enumValuesEntryMethod = [
      self::ENTRY_METHOD_SLIDER   => "Slider",
      self::ENTRY_METHOD_SELECT   => "Select",
      self::ENTRY_METHOD_RADIO    => "Radio",
      self::ENTRY_METHOD_CHECKBOX => "Checkbox",
      self::ENTRY_METHOD_TEXT     => "Text",
    ];
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
      $translatedColumns["description_lang_{$languageIndex}"] = [
        "type" => "text",
        "title" => $this->translate("Description")." ({$languageName})",
        "interface" => "formatted_text",
        "show_column" => ($languageIndex == $this->adios->translatedColumnIndex),
        "is_searchable" => ($languageIndex == $this->adios->translatedColumnIndex),
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

  public function getExtendedData($feature) {
    if ($feature["id"] > 0) {
      $languageIndex = $this->adios->websiteRenderer->domain["languageIndex"];

      $feature["UNIT"] = (new \ADIOS\Widgets\Settings\Models\Unit($this->adios))
        ->getById($feature["id_measurement_unit"])
      ;

      $feature["url"] = 
        \ADIOS\Core\HelperFunctions::str2url($feature["name_lang_{$languageIndex}"])
      ;

    }
    
    return $feature;
  }

  public function lookupSqlValue($tableAlias = NULL) {
    $unitModel = $this->adios->getModel("Widgets/Settings/Models/Unit");

    $value = "
      concat(
        {%TABLE%}.name_lang_{$this->adios->translatedColumnIndex},
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
      'id_parent' => $params['id_parent'],
      'id_measurement_unit' => 1
    ];

    if ($data['id'] > 0) {
      $params['title'] = $data["name_lang_{$this->adios->translatedColumnIndex}"];
      $params['subtitle'] = "Product feature";
    }

    $params['columns']['id_parent']['readonly'] = $params['id_parent'] > 0;

    $tabTranslations = [];
    $domainLanguages = $this->adios->config['widgets']['Website']['domainLanguages'];

    $i = 1;
    foreach ($domainLanguages as $languageIndex => $languageName) {
      if ($i != $this->adios->translatedColumnIndex) {
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
              "name_lang_{$this->adios->translatedColumnIndex}",
              "description_lang_{$this->adios->translatedColumnIndex}",
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

    // $params['columns']['min']['disabled'] = true;
    // $params['columns']['max']['disabled'] = true;

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