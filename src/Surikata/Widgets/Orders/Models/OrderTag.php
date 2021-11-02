<?php

namespace ADIOS\Widgets\Orders\Models;

class OrderTag extends \ADIOS\Core\Model {
  var $sqlName = "orders_tags";
  var $urlBase = "Orders/Tags";

  public function init() {
    $this->tableTitle = $this->translate("Order Tags");
    $this->formTitleForInserting = $this->translate("New Order Tag");
    $this->formTitleForEditing = $this->translate("Order Tag");
  }

  public function columns(array $columns = []) {

    /* REVIEW - zobrazovať preklady stavov pri stave objednávky používateľom? */
    /* REVIEW - pre používateľov zobrazovať len niektoré stavy */
    $translatedColumns = [];
    $domainLanguages = $this->adios->config['widgets']['Website']['domainLanguages'];

    foreach ($domainLanguages as $languageIndex => $languageName) {
      $translatedColumns["tag_lang_{$languageIndex}"] = [
        "type" => "varchar",
        "title" => $this->translate("Tag")." ({$languageName})",
        "show_column" => ($languageIndex == 1),
        "is_searchable" => ($languageIndex == 1),
      ];
    }
    $columns = parent::columns(array_merge(
      $translatedColumns,[
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
    ]));

    return $columns;
  }

  public function tableParams($params) {
    $params['show_search_button'] = FALSE;
    return $params;
  }

  public function formParams($data, $params) {

    $tabTranslations = [];
    $domainLanguages = $this->adios->config['widgets']['Website']['domainLanguages'];

    $i = 1;
    foreach ($domainLanguages as $languageIndex => $languageName) {
      if ($i > 1) {
        $tabTranslations[] = ["html" => "<b>".hsc($languageName)."</b>"];
        $tabTranslations[] = "tag_lang_{$languageIndex}";
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
            $this->translate("Translations") => $tabTranslations,
            $this->translate("Tag color") =>
            [
              ["html" => $this->adios->ui->Input([
                "type" => "color",
                "uid" => "{$this->uid}_color",
                "value" => $data['color'],
              ])->render()],
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
      return self::where('id', '>', 0)->get()->toArray();
    }
  }

  public function findTagFromName(string $tagName) {
    $tag = self::where('tag', '=', $tagName)->orderBy('tag')->get()->toArray();
    if ($tag !== false) {
      return $tag[0];
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