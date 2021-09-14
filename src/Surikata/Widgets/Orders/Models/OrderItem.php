<?php

namespace ADIOS\Widgets\Orders\Models;

class OrderItem extends \ADIOS\Core\Model {
  var $sqlName = "orders_items";
  var $urlBase = "Orders/{{ id_order }}/Items";
  var $tableTitle = " ";
  var $formTitleForInserting = "New order item";
  var $formTitleForEditing = "Order items";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_order" => [
        "type" => "lookup",
        "title" => "Order",
        "model" => "Widgets/Orders/Models/Order",
        "show_column" => FALSE,
        "readonly" => TRUE,
        "required" => TRUE,
      ],

      "id_product" => [
        "type" => "lookup",
        "title" => "Product",
        "model" => "Widgets/Products/Models/Product",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "quantity" => [
        "type" => "float",
        "title" => "Quantity",
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "id_delivery_unit" => [
        "type" => "lookup",
        "title" => $this->translate("Delivery unit"),
        "model" => "Widgets/Settings/Models/Unit",
        "show_column" => TRUE,
      ],

      "unit_price" => [
        "type" => "float",
        "title" => "Unit price",
        "unit" => $this->adios->locale->currencySymbol(),
        "required" => TRUE,
        "show_column" => TRUE,
      ],

      "vat_percent" => [
        "type" => "int",
        "title" => "VAT",
        "unit" => "%",
        "show_column" => TRUE,
      ],
    ]);
  }

  public function tableParams($params) {
    $params["where"] = "`{$this->table}`.`id_order` = ".(int) $params['id_order'];
    $params["show_controls"] = FALSE;
    $params["show_search_button"] = FALSE;
    return $params;
  }

  public function formParams($data, $params) {
    $params["default_values"] = ["id_order" => $params["id_order"], "quantity" => 1];
    $params["columns"]["id_product"]["onchange"] = "
      let productId = $(this).val();
      let inputName = $(this).attr('name');
      _ajax_read('Orders/AddOrderItem', 'id_product='+productId, function(res) {

        // REVIEW: je pouzitie res.sale_price spravne po upravach v pocitani cien produktu?
        // Treba otestovat
        let price = res.sale_price;
        let vat_percent = res.vat_percent;
        let delivery_unit = res.DELIVERY_UNIT;
        
        let indexOfFirst = inputName.indexOf('_');
        let indexOfSecond = inputName.indexOf('_', indexOfFirst + 1);
        let prefix = inputName.substr(0, indexOfFirst);
        let uid = inputName.substring(indexOfFirst + 1, indexOfSecond);
        
        $('#'+prefix+'_'+uid+'_id_delivery_unit').val(delivery_unit.id);
        $('#'+prefix+'_'+uid+'_id_delivery_unit_autocomplete_input').val(delivery_unit.name);
        $('#'+prefix+'_'+uid+'_unit_price').val(price);
        $('#'+prefix+'_'+uid+'_vat_percent').val(vat_percent);
        console.log(price, vat_percent, delivery_unit);
      });
    ";
    return $params;
  }

}