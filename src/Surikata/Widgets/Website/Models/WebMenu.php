<?php

namespace ADIOS\Widgets\Website\Models;

class WebMenu extends \ADIOS\Core\Model {
  var $sqlName = "web_menu";
  var $urlBase = "Website/{{ domainName }}/Menu";
  var $tableTitle = "Website menu";
  var $formTitleForInserting = "New website menu";
  var $formTitleForEditing = "Website menu";
  var $lookupSqlValue = "{%TABLE%}.name";

  public function columns(array $columns = []) {
    return parent::columns([
      "domain" => [
        "type" => "varchar",
        "title" => "Domain",
        "required" => TRUE,
      ],

      "name" => [
        "type" => "varchar",
        "title" => "Web menu name (for internal use)",
        "required" => TRUE,
        "show_column" => TRUE,
      ],
    ]);
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "domain" => [
        "type" => "index",
        "columns" => ["domain"],
      ],
    ]);
  }

  public function tableParams($params) {
    $params["title"] = "{$params['domainName']} &raquo; Menu";
    $params['where'] = "`domain` = '".$this->adios->db->escape($params['domainName'])."'";

    return $this->adios->dispatchEventToPlugins("onModelAfterTableParams", [
      "model" => $this,
      "params" => $params,
    ])["params"];
  }

  public function formParams($data, $params) {
    if ($data['id'] > 0) {

      $itemsInputUid = "{$params['uid']}_items";

      $params["template"] = [
        "columns" => [
          [
            "rows" => [
              "name",
              [
                "title" => "Menu items",
                "input" => (new \ADIOS\Core\UI\Input\Tree(
                  $this->adios,
                  $itemsInputUid,
                  [
                    "model" => "Widgets/Website/Models/WebMenuItem",
                    "id_menu" => $data['id'],
                    "where" => "id_menu = {$data['id']}",
                  ],
                ))->render(),
              ],
            ],
          ],
        ],
      ];

      $params['save_action'] = "Website/WebMenuSave";
    }

    return $params;
  }

}