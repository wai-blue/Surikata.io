<?php

namespace ADIOS\Widgets\Finances\Models;

class InvoiceItem extends \ADIOS\Core\Model {
  var $sqlName = "invoices_items";
  var $urlBase = "Invoices/{{ id_invoice }}/Items";
  var $tableTitle = "Items";
  var $formTitleForInserting = "New invoice item";
  var $formTitleForEditing = "Invoice item";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_invoice" => [
        "type" => "lookup",
        "title" => "Invoice",
        "model" => "Widgets/Finances/Models/Invoice",
        "show_column" => FALSE,
        "readonly" => TRUE,
      ],

      "item" => [
        "type" => "varchar",
        "title" => "Item",
        "show_column" => TRUE,
      ],

      "quantity" => [
        "type" => "float",
        "title" => "Množstvo",
        "show_column" => TRUE,
      ],

      "id_delivery_unit" => [
        "type" => "lookup",
        "title" => $this->translate("Delivery unit"),
        "model" => "Widgets/Settings/Models/Unit",
      ],

      "unit_price" => [
        "type" => "float",
        "title" => "Cena",
        "unit" => $this->adios->locale->currencySymbol(),
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
    $params["where"] = $this->getFullTableSQLName().".id_invoice = ".(int) $params['id_invoice'];
    $params["show_controls"] = FALSE;
    $params["show_search_button"] = FALSE;

    return $params;
  }

  public function formParams($data, $params) {
    $params["default_values"] = ["id_invoice" => (int) $params["id_invoice"]];
    return $params;
  }

}