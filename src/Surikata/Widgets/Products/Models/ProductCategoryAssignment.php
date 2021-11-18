<?php

namespace ADIOS\Widgets\Products\Models;

class ProductCategoryAssignment extends \ADIOS\Core\Widget\Model {
  var $sqlName = "products_categories_assignment";
  var $urlBase = "Products/{{ id_product }}/Categories";
  var $tableTitle = "Product categories";
  var $formTitleForInserting = "New product category";
  var $formTitleForEditing = "Product category";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_product" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Product",
        "title" => "Produkt",
        "readonly" => TRUE,
        "show_column" => TRUE,
      ],

      "id_category" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/ProductCategory",
        "title" => "Domain",
        "show_column" => TRUE,
      ],
    ]);
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "id_product___id_category" => [
        "type" => "unique",
        "columns" => ["id_product", "id_category"],
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

  public function assign($idProduct, $idCategory) {
    $idProduct = (int) $idProduct;
    $idCategory = (int) $idCategory;

    $this->adios->db->query("
      insert into `{$this->table}` (`id_product`, `id_category`) values ({$idProduct}, {$idCategory})
      on duplicate key update `id_product` = {$idProduct}
    ");
  }

  public function deleteUnassigned($idProduct, $assigned) {
    $this
      ->where('id_product', $idProduct)
      ->whereNotIn('id_category', $assigned)
      ->delete()
    ;
  }

}