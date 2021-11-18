<?php

namespace ADIOS\Widgets\Products\Models;

class ProductDomainAssignment extends \ADIOS\Core\Widget\Model {
  var $sqlName = "products_domains_assignment";
  var $urlBase = "Products/{{ id_product }}/Domains";
  var $tableTitle = "Product domains";
  var $formTitleForInserting = "New product domains";
  var $formTitleForEditing = "Product domains";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_product" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Product",
        "title" => "Produkt",
        "readonly" => TRUE,
        "show_column" => TRUE,
      ],

      "domain" => [
        "type" => "varchar",
        "title" => "Domain",
        "show_column" => TRUE,
      ],
    ]);
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "domain" => [
        "type" => "index",
        "columns" => ["domain"],
      ],
      "id_product___domain" => [
        "type" => "unique",
        "columns" => ["id_product", "domain"],
      ],
    ]);
  }

  public function tableParams($params) {
    $params["where"] = "{$this->table}.id_product = ".(int) $params['id_product'];
    return $params;
  }

  public function formParams($data, $params) {
    $params['default_values'] = ['id_product' => $params['id_product']];
    return $params;
  }

  public function assign($idProduct, $domain) {
    $idProduct = (int) $idProduct;

    $this->adios->db->query("
      insert into `{$this->table}` (`id_product`, `domain`)
        values ({$idProduct}, '".$this->adios->db->escape($domain)."')
      on duplicate key update `id_product` = {$idProduct}
    ");
  }

  public function deleteUnassigned($idProduct, $assigned) {
    $this
      ->where('id_product', $idProduct)
      ->whereNotIn('domain', $assigned)
      ->delete()
    ;
  }

}