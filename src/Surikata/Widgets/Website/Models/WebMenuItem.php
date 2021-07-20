<?php

namespace ADIOS\Widgets\Website\Models;

class WebMenuItem extends \ADIOS\Core\Model {
  var $sqlName = "web_menu_items";
  var $urlBase = "Website/Menu/{{ id_menu }}/Items";
  var $tableTitle = "Website menu items";
  var $formTitleForInserting = "New website menu item";
  var $formTitleForEditing = "Website menu item";
  var $lookupSqlValue = "concat({%TABLE%}.title, ' -> https://www.mojadomena.sk/', {%TABLE%}.url)";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_menu" => [
        "type" => "lookup",
        "model" => "Widgets/Website/Models/WebMenu",
        "title" => "Menu",
        "readonly" => TRUE,
        "show_column" => TRUE
      ],

      "title" => [
        "type" => "varchar",
        "title" => "Title",
        "show_column" => TRUE,
        "description" => "Príklad: Všeobecné obchodné podmienky, alebo: Pravidlá nakupovania",
      ],

      "url" => [
        "type" => "varchar",
        "title" => "URL address",
        // "required" => TRUE,
        // "pattern" => "[a-zA-Z0-9\\/.]+",
        "show_column" => TRUE,
        "description" => "Vložte tú časť adresy, ktorá nasleduje za https://www.mojadomena.sk. Príklad: /vseobecne-obchodne-podmienky, alebo /pravidla-nakupovania",
      ],

      "expand_product_categories" => [
        "type" => "boolean",
        "title" => "Expand to product categories",
        "show_column" => TRUE,
        "description" => "Označte, či má byť do nižších úrovní dynamicky generovaný strom kategórií produktov.",
      ],

      "id_parent" => [
        "type" => "lookup",
        "model" => "Widgets/Website/Models/WebMenuItem",
        "title" => "Parent menu item",
        "order_column" => "order_index",
        "readonly" => TRUE,
        "show_column" => TRUE
      ],

      "order_index" => [
        "type" => "int",
        "title" => "Ordering index",
        "readonly" => TRUE,
        "show_column" => TRUE,
      ],

    ]);
  }

  public function tableParams($params) {
    $params["where"] = "{$this->table}.id_menu = ".(int) $params['id_menu'];
    return $params;
  }

  public function formParams($data, $params) {
    $params['default_values'] = [
      'id_menu' => $params['id_menu'],
      'id_parent' => $params['id_parent']
    ];

    $params['columns']['id_menu']['readonly'] = is_numeric($params['id_menu']);
    $params['columns']['id_parent']['readonly'] = is_numeric($params['id_parent']);

    return $params;
  }

  public function getByIdMenu($idMenu) {
    return $this
      ->where("id_menu", $idMenu)
      ->orderBy("order_index")
      // ->orderBy("id")
      ->get()
      ->toArray()
    ;
  }

}