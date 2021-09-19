<?php

namespace ADIOS\Widgets\Products\Models;

class ProductGallery extends \ADIOS\Core\Model {
  var $sqlName = "products_gallery";
  var $urlBase = "Products/{{ id_product }}/Gallery";
  var $tableTitle = "Product gallery";
  var $formTitleForInserting = "Product gallery - New image";
  var $formTitleForEditing = "Product gallery - Edit image";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_product" => [
        "type" => "lookup",
        "model" => "Widgets/Products/Models/Product",
        "title" => "Product",
        "readonly" => TRUE,
        "show_column" => FALSE,
      ],

      "image" => [
        'type' => 'image',
        'title' => 'Image',
        'show_column' => TRUE,
        "required" => TRUE,
        "subdir" => "products"
      ],
    ]);
  }

  public function tableParams($params) {
    $params["where"] = "{$this->table}.id_product = ".(int) $params['id_product'];
    $params['show_search_button'] = FALSE;
    $params['show_controls'] = FALSE;
    $params['show_filter'] = FALSE;
    $params['title'] = " ";

    return $params;
  }

  public function formParams($data, $params) {
    $params['default_values'] = ['id_product' => (int) $params['id_product']];
    return $params;
  }

  public function cardsParams($params) {
    $params["where"] = "{$this->table}.id_product = ".(int) $params['id_product'];
    $params["columns"] = 4;
    return $params;
  }

  public function cardsCardHtmlFormatter($data) {
    return "
      <div class='card shadow mb-2'>
        <div class='text-center'>
          <img
            src='{$this->adios->config['upload_url']}/{$data['image']}'
            style='width:100%;cursor:pointer;'
            onclick='window_render(\"Products/{$data['id_product']}/Gallery/{$data['id']}/Edit\");'
          />
        </div>
      </div>
    ";
  }
}