<?php

namespace ADIOS\Widgets\Website\Models;

class WebPage extends \ADIOS\Core\Model {
  const WEBPAGE_VISIBILITY_PUBLIC  = 0;
  const WEBPAGE_VISIBILITY_PRIVATE = 1;

  var $sqlName = "web_pages";
  var $urlBase = "Website/{{ domainName }}/Pages";
  var $tableTitle = "Website pages";
  var $formTitleForInserting = "Website - {{ domain }} - New Page";
  var $formTitleForEditing = "Website - {{ domain }} - Edit Page";
  var $lookupSqlValue = "{%TABLE%}.url";


  public function init() {
    $this->enumWebPageVisibilityOptions = [
      self::WEBPAGE_VISIBILITY_PUBLIC => "Public",
      self::WEBPAGE_VISIBILITY_PRIVATE => "Only for signed-in visitors",
    ];
  }

  public function columns(array $columns = []) {
    $tmp_domena = "https://".($this->adios->config['settings']['web']['profile']['rootUrl'] ?? "MojaDomena.sk");

    return parent::columns([
      "domain" => [
        "type" => "varchar",
        "title" => "Domain",
        "required" => TRUE,
        "readonly" => TRUE,
      ],

      "name" => [
        "type" => "varchar",
        "title" => "Name",
        "required" => TRUE,
        "show_column" => TRUE,
        "description" => "Your webpage name. Example: 'homepage', 'list of products'.",
      ],

      "url" => [
        "type" => "varchar",
        "title" => "URL address",
        // "required" => TRUE,
        // "pattern" => "[a-zA-Z0-9\\/.]+",
        "show_column" => TRUE,
        "description" => "If you left this input blank, the URL of this page will be determined by the plugins used.",
        // "description" => "Vložte tú časť adresy, ktorá nasleduje za {$tmp_domena}. Príklad: vseobecne-obchodne-podmienky, alebo pravidla-nakupovania",
        "input" => [
          "style" => "font-size:1.5em",
        ]
      ],

      "content_structure" => [
        "type" => "text",
        "title" => "Layout structure and plugin configuratin",
        "input" => "Widgets/Website/Inputs/ContentStructure",
        "description" => "More detailed settings are available by clicking on the selected panel.",
        "show_column" => FALSE,
      ],

      "visibility" => [
        "type" => "int",
        "enum_values" => $this->enumWebPageVisibilityOptions,
        "title" => "Visibility",
        "show_column" => TRUE,
      ],

      "publish_always" => [
        "type" => "boolean",
        "title" => "Publish without time limitations",
        "show_column" => TRUE,
      ],

      "publish_from" => [
        "type" => "date",
        "title" => "Publish from",
      ],

      "publish_to" => [
        "type" => "date",
        "title" => "Publish until",
        "show_column" => TRUE,
      ],

      "seo_title" => [
        "type" => "varchar",
        "title" => "SEO Title",
        "description" => "Used in <title> tag.",
      ],

      "seo_keywords" => [
        "type" => "varchar",
        "title" => "Meta Keyword",
        "description" => "Used in <meta keywords> tag.",
      ],

      "seo_description" => [
        "type" => "varchar",
        "title" => "Meta Description",
        "description" => "Used in <meta description> tag.",
      ],
    ]);
  }

  public function indexes($columns = []) {
    return parent::indexes([
      "domain" => [
        "type" => "index",
        "columns" => ["domain"],
      ],
    ]);
  }

  public function tableParams($params) {
    $params["title"] = "{$params['domainName']} &raquo; Pages";

    $domains = $this->adios->config['widgets']['Website']['domains'];
    $domain = "";
    foreach ($domains as $key => $domainInfo) {
      if ($domainInfo['name'] == $params['domainName']) {
        $domain = $key;
      }
    }

    $params['where'] = "`domain` = '".$this->adios->db->escape($domain)."'";

    return $this->adios->dispatchEventToPlugins("onModelAfterTableParams", [
      "model" => $this,
      "params" => $params,
    ])["params"];
  }

  public function formParams($data, $params) {
    if ($params['id'] == -1) {
      $domains = $this->adios->config['widgets']['Website']['domains'];
      $domainName = $params['domainName'];

      $domain = "";
      foreach ($domains as $tmpDomain => $tmpDomainInfo) {
        if ($tmpDomainInfo['name'] == $domainName) {
          $domain = $tmpDomain;
        }
      }

      $params['default_values'] = ["domain" => $domain];
    }

    $params["template"] = [
      "columns" => [
        [
          "class" => "col-md-8 pr-2",
          "rows" => [
            "domain",
            "name",
            "url",
            "content_structure",
            // "typ_stranky",
          ],
        ],
        [
          "class" => "col-md-4 pl-0",
          "tabs" => [
            // "Textový obsah" => [
            //   "obsah_h1",
            //   "obsah_text",
            // ],
            "SEO" => [
              "seo_title",
              "seo_keywords",
              "seo_description",
            ],
            "Visibility and publishing" => [
              "visibility",
              "publish_always",
              "publish_from",
              "publish_to",
            ],
          ],
        ],
      ],
    ];

    return $params;
  }

  public function onAfterSave($data, $returnValue) {
    $this->adios->widgets['Website']->rebuildSitemap($data['domain']);
    return parent::onAfterSave($data, $returnValue);
  }

}