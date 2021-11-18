<?php

namespace ADIOS\Actions\Products\Products;

class Search extends \ADIOS\Core\Widget\Action {
  // public function init() {
  //   $this->languageDictionary["en"] = [
  //     "Hľadať" => "Search",
  //   ];
  // }

  public function render() {
    $modelName = "Widgets/Products/Models/Product";
    $model = $this->adios->getModel($modelName);

    $unsearchableColumnTypes = [
      "image",
      "file",
    ];

    $tabs = [];
    $tabs[$model->name] = [];
    $tabs[$model->name]["title"] = $model->shortName;
    $tabs[$model->name]["items"] = [];

    foreach ($model->columns() as $colName => $colDef) {
      if ($colDef['type'] == "lookup") {
        $lookupModelName = $colDef['model'];
        $lookupModel = $this->adios->getModel($lookupModelName);
        $tabs[$lookupModel->name] = [];
        $tabs[$lookupModel->name]["title"] = $lookupModel->shortName;

        foreach ($lookupModel->columns() as $lookupColName => $lookupColDef) {
          if (!in_array($lookupColDef["type"], $unsearchableColumnTypes)) {
            $tabs[$lookupModel->name]["items"][] = [
              "title" => $lookupColDef['title'],
              "input" => $this->adios->ui->Input([
                "type" => $lookupColDef["type"],
                "uid" => "{$this->uid}_{$colName}___{$lookupColName}",
              ]),
            ];
          }
        }
        // $tabs[$lookupModel->name]["items"][] = $lookupTabs;
      } else if (!in_array($colDef["type"], $unsearchableColumnTypes)) {
        $tabs[$model->name]["items"][] = [
          "title" => $colDef['title'],
          "input" => $this->adios->ui->Input([
            "type" => $colDef["type"],
            "uid" => "{$this->uid}_{$colName}",
          ]),
        ];
      }
    }

    return $this->adios->renderAction("UI/SettingsPanel", [
      "settings_group" => "plugins/WAI/Product/Catalog",
      "title" => "{$model->tableTitle}: ".$this->translate("Hľadať"),
      "onsave" => "
        console.log(ui_form_get_values('{$this->uid}_window', '{$this->uid}_'));
      ",
      "template" => [
        "tabs" => $tabs,
      ],
    ]);
  }
}