<?php

namespace ADIOS\Plugins\WAI\Blog\Catalog\Models;

class BlogTag extends \ADIOS\Core\Plugin\Model {
  var $sqlName = "blogs_tags";
  var $urlBase = "Website/Blog/Tags";
  var $lookupSqlValue = "{%TABLE%}.name";
  var $tableTitle = "Blogs tags";
  var $formTitleForInserting = "New blog tag";
  var $formTitleForEditing = "Blog tag";

  public function columns(array $columns = []) {
    return parent::columns([
      "domain" => [
        "type" => "varchar",
        "title" => $this->translate("Domain"),
        "required" => TRUE,
        "enum_values" => $this->adios->getEnumValuesForListOfDomains(),
        "show_column" => TRUE,
      ],

      "name" => [
        "type" => "varchar", 
        "title" => "Tag name", 
        "show_column" => TRUE,
        "required" => TRUE,
      ],

      "description" => [
        'type' => 'text',
        'title' => 'Description',
        'show_column' => TRUE,
      ],

    ]);
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "domain" => [
        "type" => "index",
        "columns" => ["domain"],
      ],
      "unique_name_for_domain" => [
        "type" => "unique",
        "columns" => ["name", "domain"],
      ],
    ]);
  }

  public function getTags() {
    return $this->get()->toArray();
  }

  public function deleteRow($id) {
    $blogTagAssignmentModel = new \ADIOS\Plugins\WAI\Blog\Catalog\Models\BlogTagAssignment($this->adios);
    $blogTagAssignmentModel
      ->where("id_tag", "=", $id)
      ->delete()
    ;
    return parent::deleteRow($id);
  }

}