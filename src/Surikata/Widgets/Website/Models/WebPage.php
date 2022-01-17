<?php

namespace ADIOS\Widgets\Website\Models;

class WebPage extends \ADIOS\Core\Widget\Model {
  const WEBPAGE_VISIBILITY_PUBLIC  = 0;
  const WEBPAGE_VISIBILITY_PRIVATE = 1;

  var $sqlName = "web_pages";
  var $urlBase = "Website/{{ domainName }}/Pages";
  var $lookupSqlValue = "{%TABLE%}.url";


  public function init() {
    $this->tableTitle = $this->translate("Website pages");
    $this->formTitleForInserting = $this->translate("Website") ." - {{ domain }} - ". $this->translate("New Page");
    $this->formTitleForEditing = $this->translate("Website") ." - {{ domain }} - ". $this->translate("Edit Page");

    $this->enumWebPageVisibilityOptions = [
      self::WEBPAGE_VISIBILITY_PUBLIC => $this->translate("Public"),
      self::WEBPAGE_VISIBILITY_PRIVATE => $this->translate("Only for signed-in visitors"),
    ];
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "domain" => [
        "type" => "varchar",
        "title" => $this->translate("Domain"),
        "required" => TRUE,
        "enum_values" => $this->adios->getEnumValuesForListOfDomains(),
        "readonly" => TRUE,
      ],

      "name" => [
        "type" => "varchar",
        "title" => $this->translate("Brief name"),
        // "required" => TRUE,
        "show_column" => TRUE,
        "description" => $this->translate("Your webpage name. Example: 'homepage', 'list of products'."),
      ],

      "url" => [
        "type" => "varchar",
        "title" => $this->translate("URL address"),
        // "required" => TRUE,
        // "pattern" => "[a-zA-Z0-9\\/.]+",
        "show_column" => TRUE,
        "description" => $this->translate("If you left this input blank, the URL of this page will be determined by the plugins used."),
        // "description" => "Vložte tú časť adresy, ktorá nasleduje za {$tmp_domena}. Príklad: vseobecne-obchodne-podmienky, alebo pravidla-nakupovania",
        "input" => [
          "style" => "font-size:1.5em",
        ]
      ],

      "content_structure" => [
        "type" => "text",
        // "title" => $this->translate("Layout structure and plugin configuratin"),
        "input" => "Widgets/Website/Inputs/ContentStructure",
        // "description" => $this->translate("More detailed settings are available by clicking on the selected panel."),
        "show_column" => FALSE,
      ],

      "visibility" => [
        "type" => "int",
        "enum_values" => $this->enumWebPageVisibilityOptions,
        "title" => $this->translate("Visibility"),
        "show_column" => TRUE,
      ],

      "publish_always" => [
        "type" => "boolean",
        "title" => $this->translate("Publish without time limitations"),
        "show_column" => TRUE,
      ],

      "publish_from" => [
        "type" => "date",
        "title" => $this->translate("Publish from"),
      ],

      "publish_to" => [
        "type" => "date",
        "title" => $this->translate("Publish until"),
        "show_column" => TRUE,
      ],

      "seo_title" => [
        "type" => "varchar",
        "title" => $this->translate("SEO Title"),
        "description" => $this->translate("Used in <title> tag."),
      ],

      "seo_keywords" => [
        "type" => "varchar",
        "title" => $this->translate("Meta Keyword"),
        "description" => $this->translate("Used in <meta keywords> tag."),
      ],

      "seo_description" => [
        "type" => "varchar",
        "title" => $this->translate("Meta Description"),
        "description" => $this->translate("Used in <meta description> tag."),
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
    $params["title"] = "{$params['domainName']} &raquo; " . $this->translate("Pages");
    $params['where'] = "`domain` = '".$this->adios->db->escape($params['domainName'])."'";

    return $this->adios->dispatchEventToPlugins("onModelAfterTableParams", [
      "model" => $this,
      "params" => $params,
    ])["params"];
  }

  public function formParams($data, $params) {

    if ($params['id'] == -1) {
      $params['default_values'] = [
        "domain" => $params['domainName'],
      ];

      $params["template"] = [
        "columns" => [
          [
            "class" => "col-md-8 pr-2",
            "rows" => [
              "domain",
              "url",
            ],
          ],
        ],
      ];

    } else {

      $params["template"] = [
        "columns" => [
          [
            "class" => "col-md-6 pr-2",
            "rows" => [
              "domain",
              "url",
              // ["html" => "<h3>".$this->translate("Page content")."</h3>"],
              "content_structure",
              // "typ_stranky",
            ],
          ],
          [
            "class" => "col-md-6 pl-0",
            "tabs" => [
              // "Textový obsah" => [
              //   "obsah_h1",
              //   "obsah_text",
              // ],
              $this->translate("Visibility") => [
                "publish_from",
                "publish_to",
                "publish_always",
                "visibility",
              ],
              "SEO" => [
                "seo_title",
                "seo_keywords",
                "seo_description",
              ],
              $this->translate("Miscelaneous") => [
                "name",
              ],
            ],
          ],
        ],
      ];
    }

    return $params;
  }

  public function onBeforeSave($data) {

    if ($data['id'] == -1) {
      $data["publish_always"] = TRUE;
      $data["content_structure"] = json_encode([
        "layout" => "WithoutSidebar",
        "panels" => [
          "header" => [
            "plugin" => "WAI/Common/Header",
            "settings" => [],
          ],
          "navigation" => [
            "plugin" => "WAI/Common/Navigation",
            "settings" => [],
          ],
          "footer" => [
            "plugin" => "WAI/Common/Footer",
            "settings" => [],
          ],
        ],
      ]);
    }

    return parent::onBeforeSave($data);
  }

  public function onAfterSave($data, $returnValue) {
    $this->adios->widgets['Website']->rebuildSitemap($data['domain']);
    return parent::onAfterSave($data, $returnValue);
  }

}