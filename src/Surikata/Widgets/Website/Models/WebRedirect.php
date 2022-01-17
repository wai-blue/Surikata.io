<?php

namespace ADIOS\Widgets\Website\Models;

class WebRedirect extends \ADIOS\Core\Widget\Model {
  var $sqlName = "web_redirects";
  var $urlBase = "Website/{{ domainName }}/Redirects";
  var $lookupSqlValue = "{%TABLE%}.name";
  static $redirects = [];

  public function init() {
    $this->tableTitle = $this->translate("Website redirects");
    $this->formTitleForInserting = $this->translate("New website redirect");
    $this->formTitleForEditing = $this->translate("Website redirect");
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "domain" => [
        "type" => "varchar",
        "title" => $this->translate("Domain"),
        "required" => TRUE,
      ],

      "from_url" => [
        "type" => "varchar",
        "title" => $this->translate("From URL (relative)"),
        "description" => $this->translate("Relative URL from the root URL of the domain."),
        "show_column" => TRUE,
      ],

      "to_url" => [
        "type" => "varchar",
        "title" => $this->translate("To URL (absolute)"),
        "description" => $this->translate("Absolute URL including root URL of the domain (can also be an external URL)."),
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "type" => [
        "type" => "int",
        "title" => $this->translate("Type"),
        "enum_values" => [
          301 => $this->translate("Permanent redirect (301)"),
          302 => $this->translate("Temporary redirect (302)"),
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
    $params["title"] = "{$params['domainName']} &raquo; " .$this->translate("Redirects");
    $params['where'] = "`domain` = '".$this->adios->db->escape($params['domainName'])."'";

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
    $this->adios->widgets['Website']->rebuildSitemapForAllDomains();
    return parent::onAfterSave($data, $returnValue);
  }

  public function getAllByDomain() {
    if (empty(self::$redirects)) {
      self::$redirects = $this
        ->where("domain", "=", $this->adios->websiteRenderer->domain['name'])
        ->get()
        ->toArray()
      ;
    }

    return self::$redirects;
  }

  public function getHomePageUrl() {
    foreach ($this->getAllByDomain() as $redirect) {
      if ($redirect['from_url'] == "homePage") {
        if (str_contains($redirect['to_url'], "//{% ROOT_URL %}")) {
          $homePageUrl = str_replace(
            "//{% ROOT_URL %}", 
            $this->adios->websiteRenderer->rootUrl, 
            $redirect['to_url']
          );
        } else {
          $homePageUrl = $redirect['to_url'];
        }
        break;
      }
    }

    return $homePageUrl;
  }

}