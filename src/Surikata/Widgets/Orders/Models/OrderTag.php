<?php

namespace ADIOS\Widgets\Orders\Models;

class OrderTag extends \ADIOS\Core\Widget\Model {
  var $sqlName = "orders_tags";
  var $urlBase = "Orders/Tags";

  public function init() {
    $this->tableTitle = $this->translate("Order Tags");
    $this->formTitleForInserting = $this->translate("New Order Tag");
    $this->formTitleForEditing = $this->translate("Order Tag");
  }

  public function columns(array $columns = []) {

    $columns = parent::columns(
      [
        "tag" => [
         "type" => "varchar",
         "title" => $this->translate("Tag"),
         "show_column" => TRUE,
        ],
        "color" => [
          "type" => "varchar",
          "title" => $this->translate("Color"),
          "show_column" => TRUE,
        ],
      ]
    );

    return $columns;
  }

  public function tableParams($params) {
    $params['show_search_button'] = FALSE;
    return $params;
  }

  public function formParams($data, $params) {

    $tabTranslations = [];

    $tabTranslations[] = "tag";


    $params["template"] = [
      "columns" => [
        [
          "class" => "col-md-9 pl-0",
          "tabs" => [
            $this->translate("Translations") => $tabTranslations,
            $this->translate("Tag color") =>
            [
              [
                "html" => $this->adios->ui->Input([
                  "type" => "color",
                  "uid" => "{$this->uid}_color",
                  "value" => $data['color'],
                ])->render()
              ],
            ],
          ],
        ],
      ],
    ];

    return $params;
  }

  public function getSelectedTags($tagList = []) {
    if (count($tagList) > 0) {
      return self::whereIn('id', $tagList)->get()->toArray();
    }
    else {
      return [];
    }
  }

  public function findTagByName(string $tagName, $createNewTags = true) {
    $tag = self::where('tag', '=', $tagName)->orderBy('tag')->get()->toArray();
    if (count($tag) > 0) {
      return $tag[0];
    }
    else {
      if ($createNewTags) {
        $tag["id"] = $this->insertRow(["tag" => $tagName, "color" => "#BCBCBC"]);
      }
    }
    return $tag;
  }

  public function getTagNamesFromArray($tagArray) {
    $names = [];
    foreach ($tagArray as $tag) {
      $names[] = $tag["tag"];
    }
    return $names;
  }

}