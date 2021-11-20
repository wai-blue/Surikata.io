<?php

namespace ADIOS\Widgets\Products\Models;

class ProductServiceAssignment extends \ADIOS\Core\Widget\Model {
  var $sqlName = "products_services_assignment";
  var $urlBase = "Products/{{ id_product }}/Services";
  var $tableTitle = "Product services";
  var $formTitleForInserting = "New product service";
  var $formTitleForEditing = "Product service";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_product" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Product",
        "title" => "Produkt",
        "required" => TRUE,
        "readonly" => TRUE,
        "show_column" => FALSE,
      ],

      "id_service" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Service",
        "title" => "Service",
        "required" => TRUE,
        "show_column" => TRUE,
      ],
    ]);
  }

  public function tableParams($params) {
    $params["where"] = "{$this->table}.id_product = ".(int) $params['id_product'];
    $params['show_search_button'] = FALSE;

    return $params;
  }

  public function assign($idProduct, $idService) {
    $idProduct = (int) $idProduct;
    $idService = (int) $idService;

    $this->adios->db->query("
      delete from `{$this->table}` WHERE `id_product` = {$idProduct} AND `id_service` = {$idService};
    ");

    $this->adios->db->query("
      insert into `{$this->table}` (`id_product`, `id_service`) values ({$idProduct}, {$idService})
    ");
  }

  public function deleteUnassigned($idProduct, $assigned) {
    $this
      ->where('id_product', $idProduct)
      ->whereNotIn('id_service', $assigned)
      ->delete()
    ;
  }

  public function formParams($data, $params) {
    $params['default_values'] = [
      'id_product' => $params['id_product'],
    ];
    return $params;
  }

}