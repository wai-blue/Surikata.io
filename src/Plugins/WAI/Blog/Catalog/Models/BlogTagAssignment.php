<?php

namespace ADIOS\Plugins\WAI\Blog\Catalog\Models;

class BlogTagAssignment extends \ADIOS\Core\Plugin\Model {
  var $sqlName = "blogs_tags_assignment";
  var $urlBase = "Website/Blog/{{ id_blog }}/Tags";
  var $tableTitle = "Blog tags";
  var $formTitleForInserting = "New blog tag";
  var $formTitleForEditing = "Blog tag";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_blog" => [
        "type" => "lookup",
        "model" => "Plugins/WAI/Blog/Catalog/Models/Blog",
        "title" => "Blog",
        "readonly" => TRUE,
        "show_column" => FALSE,
      ],

      "id_tag" => [
        "type" => "lookup",
        "model" => "Plugins/WAI/Blog/Catalog/Models/BlogTag",
        "title" => "Tag",
        "required" => TRUE,
        "show_column" => TRUE,
      ]
    ]);
  }

  public function tableParams($params) {
    $params["where"] = "{$this->table}.id_blog = ".(int) $params['id_blog'];
    return $params;
  }

  public function formParams($data, $params) {
    $params['default_values'] = ['id_blog' => $params['id_blog']];
    return $params;
  }

  public function getBlogsByTags($tags) {
    $countTags = count($tags);

    $blogs = self::
      whereIn('id_tag', $tags)
      ->groupBy('id_blog')
      ->havingRaw("COUNT(*) = {$countTags}")
      ->pluck('id_blog')
    ;

    return ($blogs ?? $blogs->toArray());
  }

}