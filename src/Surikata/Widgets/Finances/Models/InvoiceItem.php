<?php

namespace ADIOS\Widgets\Finances\Models;

class InvoiceItem extends \ADIOS\Core\Model {
  var $sqlName = "invoices_items";
  var $urlBase = "Invoices/{{ id_invoice }}/Items";

  public function columns(array $columns = []) {
    $this->tableTitle = $this->translate("Items");
    $this->formTitleForInserting = $this->translate("New invoice item");
    $this->formTitleForEditing = $this->translate("Invoice item");

    return parent::columns([
      "id_invoice" => [
        "type" => "lookup",
        "title" => $this->translate("Invoice"),
        "model" => "Widgets/Finances/Models/Invoice",
        "readonly" => TRUE,
      ],

      "item" => [
        "type" => "varchar",
        "title" => $this->translate("Item"),
        "show_column" => TRUE,
      ],

      "quantity" => [
        "type" => "float",
        "title" => $this->translate("Quantity"),
        "show_column" => TRUE,
      ],

      "id_delivery_unit" => [
        "type" => "lookup",
        "title" => $this->translate("Delivery unit"),
        "model" => "Widgets/Settings/Models/Unit",
      ],

      "unit_price" => [
        "type" => "float",
        "sql_data_type" => "decimal",
        "decimals" => 4,
        "title" => $this->translate("Unit price excl. VAT"),
        "unit" => $this->adios->locale->currencySymbol(),
        "show_column" => TRUE,
      ],

      "vat_percent" => [
        "type" => "int",
        "title" => $this->translate("VAT"),
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