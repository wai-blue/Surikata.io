<?php

namespace ADIOS\Widgets\Orders\Models;

class OrderTag extends \ADIOS\Core\Model {
  var $sqlName = "orders_tags";
  var $urlBase = "Orders/Tags";

  public function columns(array $columns = []) {
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
    ]));

    return $columns;
  }

  public function getSelectedTags($tagList = []) {
    if (count($tagList) > 0) {
      return $this->whereIn('id', $tagList)->get();
    }
    else {
      return $this->where('id', '>', 1)->get();
    }
  }

}