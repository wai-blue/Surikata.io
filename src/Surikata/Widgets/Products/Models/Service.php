<?php

namespace ADIOS\Widgets\Products\Models;

class Service extends \ADIOS\Core\Model {
  var $sqlName = "services";
  var $lookupSqlValue = "{%TABLE%}.name_lang_1";
  var $urlBase = "Services";

  public function init() {
    $this->tableTitle = $this->translate("Services");
    $this->formTitleForInserting = $this->translate("New service");
    $this->formTitleForEditing = $this->translate("Service");
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

    return parent::columns(array_merge(
      $translatedColumns,
      [
        "pictogram" => [
          'type' => 'image',
          'title' => $this->translate('Pictogram'),
          'show_column' => TRUE,
          "subdir" => "pictograms"
        ],
      ]
    ));
  }

  public function formParams($data, $params) {
    $params['default_values'] = [
      'id_parent' => $params['id_parent']
    ];

    if ($data['id'] > 0) {
      $params['title'] = $data['name_lang_1'];
      $params['subtitle'] = "Service";
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
              "pictogram",
            ],
            $this->translate("Translations") => $tabTranslations,
          ],
        ],
      ],
    ];

    return $params;
  }

}