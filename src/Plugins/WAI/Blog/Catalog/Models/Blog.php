<?php

namespace ADIOS\Plugins\WAI\Blog\Catalog\Models;

class Blog extends \ADIOS\Core\Model {
  var $sqlName = "blogs";
  var $tableTitle = "Blogs";
  var $urlBase = "Website/Blog";
  var $lookupSqlValue = "{%TABLE%}.name";

  public function columns(array $columns = []) {
    return parent::columns([
      "name" => [
        "type" => "varchar",
        "title" => "Title",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "content" => [
        "type" => "text",
        "title" => "Content",
        "interface" => "formatted_text",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "perex" => [
        "type" => "text",
        "title" => "Perex",
        "interface" => "formatted_text",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "image" => [
        "type" => "image",
        "title" => "Image",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "created_at" => [
        "type" => "date",
        "title" => "Created at",
        "show_column" => TRUE,
      ],

      "id_user" => [
        "type" => "lookup",
        "title" => "Author",
        "model" => "Core/Models/User",
        "show_column" => TRUE,
      ],
    ]);
  }

  public function blogTags() {
    return $this
      ->belongsToMany(
        \ADIOS\Plugins\WAI\Blog\Catalog\Models\BlogTag::class,
        GTP."_blogs_tags_assignment",
        'id_blog',
        'id_tag'
      )
    ;
  }

  public function formParams($data, $params) {
    $params["template"] = [
      "columns" => [
        [
          "tabs" => [
            "General" => [
              "name",
              "content",
              "perex",
              "image",
              "created_at",
              "id_user"
            ],
            $this->translate("Tags") => [
              "action" => "UI/Table",
              "params" => [
                "model"    => "Plugins/WAI/Blog/Catalog/Models/BlogTagAssignment",
                "id_blog"  => $data['id'],
              ]
            ],
          ]
        ]
      ]
    ];

    $params["default_values"] = 
      [
        "created_at" => date("Y-m-d"),
        "id_user"    => $this->adios->userProfile['id'],
      ];

    return $params;
  }

  public function getByDate($year, $month, $filteredTags, $limit) {
    $blogQuery = $this->getQuery();

    $this->addLookupsToQuery($blogQuery, ['id_user' => 'USER']);

    if($year) {
      $blogQuery->whereYear("created_at", $year);
    }

    if ($month) {
      $blogQuery->whereMonth("created_at", $month);
    }

    if ($filteredTags) {
      $blogTagAssignment = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\BlogTagAssignment($this->adios);
      $filteredBlogs = $blogTagAssignment->getBlogsByTags($filteredTags);
      $blogQuery->whereIn("{$this->table}.id", $filteredBlogs);
    }

    $query = $blogQuery->orderBy('created_at', 'DESC');
  
    if ($limit !== NULL) {
      $query = $query->skip(0)->take($limit);
    }

    return $this->fetchQueryAsArray($query);
  }

  public function getById($id) {
    $id = (int) $id;

    $item = self::with('blogTags')->find($id);

    return ($item === NULL ? [] : $item->toArray());
  }

  public function deleteRow($id) {
    $blogTagAssignmentModel = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\BlogTagAssignment($this->adios);
    $blogTagAssignmentModel
      ->where("id_blog", "=", $id)
      ->delete()
    ;
    return parent::deleteRow($id);
  }

}