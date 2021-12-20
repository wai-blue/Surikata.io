<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\Table;
/**
 * @package UI\Actions\Table
 */
class Search extends \ADIOS\Core\Action {
  public function render() {
    $model = $this->adios->getModel($this->params['model']);
    $searchGroup = $this->params['searchGroup'];

    $unsearchableColumnTypes = [
      "image",
      "file",
    ];

    $tabs = [];
    $tabs[$model->name] = [];
    $tabs[$model->name]["title"] = $model->tableTitle;
    $tabs[$model->name]["items"] = [];

    foreach ($model->columns() as $colName => $colDef) {
      if (!($colDef["is_searchable"] ?? TRUE)) continue;

      if ($colDef['type'] == "lookup") {
        $lookupModelName = $colDef['model'];
        $lookupModel = $this->adios->getModel($lookupModelName);
        $tabs[$lookupModel->name] = [];
        $tabs[$lookupModel->name]["title"] = $lookupModel->tableTitle;

        foreach ($lookupModel->columns() as $lookupColName => $lookupColDef) {
          if (!($colDef["is_searchable"] ?? TRUE)) continue;

          if (!in_array($lookupColDef["type"], $unsearchableColumnTypes)) {
            $tabs[$lookupModel->name]["items"][] = [
              "title" => $lookupColDef['title'],
              "input" => $this->adios->ui->Input([
                "model" => $this->params['model'],
                "type" => $lookupColDef["type"],
                "value" => NULL,
                "uid" => "{$this->uid}_LOOKUP___{$colName}___{$lookupColName}",
              ]),
            ];
          }
        }
      }

      if (!in_array($colDef["type"], $unsearchableColumnTypes)) {
        $tabs[$model->name]["items"][] = [
          "title" => $colDef['title'],
          "input" => $this->adios->ui->Input([
            "model" => $colDef['model'] ?? $this->params['model'],
            "type" => $colDef["type"],
            "input_style" => "select",
            "value" => NULL,
            "uid" => "{$this->uid}_{$colName}",
          ]),
        ];
      }
    }

    $content = "
      <div class='row'>
        <div class='col-12 col-lg-8'>
          ".(new \ADIOS\Core\UI\Input\SettingsPanel(
            $this->adios,
            $this->uid."_settings_panel",
            [
              "settings_group" => "tableSearch",
              "template" => [
                "tabs" => $tabs,
              ],
            ]
          ))->render()."
        </div>
        <div class='col-12 col-lg-4'>
          <div class='card shadow mb-4'>
            <a class='d-block card-header py-3'>
              <h6 class='m-0 font-weight-bold text-primary'>".$this->translate("Saved searches")."</h6>
            </a>
            <div>
              <div class='card-body'>
                <div id='{$this->uid}_saved_searches_div'>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <script>
        function {$this->uid}_get_search_string() {
          let values = JSON.stringify(ui_form_get_values('{$this->uid}_settings_panel_form', '{$this->uid}_'));
          return Base64.encode(values);
        }

        function {$this->uid}_search(searchString) {
          if (typeof searchString == 'undefined') {
            searchString = {$this->uid}_get_search_string();
          }

          desktop_update(
            '{$model->urlBase}',
            {
              'searchGroup': '".ads($searchGroup)."',
              'search': searchString,
            },
            {
              'type': 'POST',
            }
          )
        }

        function {$this->uid}_save_search() {
          let searchName = prompt('Enter name of the search:', 'My Search');

          if (searchName != '' && searchName != null) {
            _ajax_read(
              'UI/Table/Search/Save',
              {
                'model': '".ads($model)."',
                'searchGroup': '".ads($searchGroup)."',
                'searchName': searchName,
                'search': {$this->uid}_get_search_string(),
              },
              function(res) {
                {$this->uid}_update_saved_searches();
              }
            );
          }
        }

        function {$this->uid}_update_saved_searches() {
          _ajax_update(
            'UI/Table/Search/SavedSearchesOverview',
            {
              'parentUid': '{$this->uid}',
              'searchGroup': '".ads($searchGroup)."'
            },
            '{$this->uid}_saved_searches_div'
          );
        }

        function {$this->uid}_delete_saved_search(searchName) {
          if (confirm('Do you want to delete saved search?\\n\\n' + searchName)) {
            _ajax_read(
              'UI/Table/Search/Delete',
              {
                'searchGroup': '".ads($searchGroup)."',
                'searchName': searchName,
              },
              function(res) {
                {$this->uid}_update_saved_searches();
              }
            );
          }
        }

        function {$this->uid}_load_saved_search(searchName) {
          _ajax_read(
            'UI/Table/Search/Load',
            {
              'searchGroup': '".ads($searchGroup)."',
              'searchName': searchName,
            },
            function(res) {
              // TODO: nie je dokoncene nastavovanie hodnot cez JS
              // pretoze chyba na to vhodne JS API.
              for (var i in res) {
                $('#{$this->uid}_' + i).val(res[i]);
              }
            }
          );
        }

        function {$this->uid}_close() {
          window_close('{$this->uid}_window');
        }

        {$this->uid}_update_saved_searches();
      </script>
    ";

    $window = $this->adios->ui->Window([
      'uid' => "{$this->uid}_window",
      'title' => $this->translate("Search in").": ".hsc($searchGroup),
      'content' => $content,
    ]);

    $window->params['header'] = [
      $this->adios->ui->button([
        'type' => 'close',
        'onclick' => "{$this->uid}_close();",
      ]),
      $this->adios->ui->button([
        'type' => 'save',
        'text' => $this->translate('Apply'),
        'onclick' => "{$this->uid}_search();",
      ]),
      $this->adios->ui->button([
        'text' => $this->translate('Save this search'),
        'onclick' => "{$this->uid}_save_search();",
        'class' => 'btn-light btn-icon-split float-right',
        'fa_icon' => 'fas fa-save',
      ]),
    ];
    
    return $window->render();
  }
}
