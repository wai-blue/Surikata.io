<?php

namespace ADIOS\Widgets\Website\Models;

class WebRedirect extends \ADIOS\Core\Model {
  var $sqlName = "web_redirects";
  var $urlBase = "Website/{{ domain }}/Redirects";
  var $tableTitle = "Website redirects";
  var $formTitleForInserting = "New website redirect";
  var $formTitleForEditing = "Website redirect";
  var $lookupSqlValue = "{%TABLE%}.name";

  public function columns(array $columns = []) {
    return parent::columns([
      "domain" => [
        "type" => "varchar",
        "title" => "Domain",
        "required" => TRUE,
      ],

      "from_url" => [
        "type" => "varchar",
        "title" => "From URL (relative)",
        "description" => "Relative URL from the root URL of the domain.",
        "show_column" => TRUE,
      ],

      "to_url" => [
        "type" => "varchar",
        "title" => "To URL (absolute)",
        "description" => "Absolute URL including root URL of the domain (can also be an external URL).",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "type" => [
        "type" => "int",
        "title" => "Type",
        "enum_values" => [
          301 => "Permanent redirect (301)",
          302 => "Temporary redirect (302)",
        ],
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
    $params["title"] = "Website - {$params['domain']} - Redirects";
    $params['where'] = "`domain` = '".$this->adios->db->escape($params['domain'])."'";

    return $this->adios->dispatchEventToPlugins("onModelAfterTableParams", [
      "model" => $this,
      "params" => $params,
    ])["params"];
  }

  public function formParams($data, $params) {
    if ($params['id'] == -1) {
      $params['default_values'] = ["domain" => $params['domain']];
    }

    return $params;
  }

  public function onAfterSave($data, $returnValue) {
    $this->adios->widgets['Website']->rebuildSitemap($data['domain']);
    return parent::onAfterSave($data, $returnValue);
  }

}