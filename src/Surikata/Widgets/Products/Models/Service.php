<?php

namespace ADIOS\Widgets\Products\Models;

class Service extends \ADIOS\Core\Widget\Model {
  var $sqlName = "services";
  var $urlBase = "Services";

  public function init() {
    $this->tableTitle = $this->translate("Services");
    $this->formTitleForInserting = $this->translate("New service");
    $this->formTitleForEditing = $this->translate("Service");
    $this->lookupSqlValue = "{%TABLE%}.name_lang_{$this->adios->translatedColumnIndex}";
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

    return parent::columns(array_merge(
      $translatedColumns,
      [
        "pictogram" => [
          'type' => 'image',
          'title' => $this->translate('Pictogram'),
          "description" => $this->translate("Supported image extensions: jpg, gif, png, jpeg"),
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
      $params['title'] = $data["name_lang_{$this->adios->translatedColumnIndex}"];
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
              "name_lang_{$this->adios->translatedColumnIndex}",
              "description_lang_{$this->adios->translatedColumnIndex}",
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