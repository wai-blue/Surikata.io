<?php

namespace ADIOS\Actions\Customers;

class Invoices extends \ADIOS\Core\Action {
  public function render() {
    $customerModel = $this->adios->getModel("Widgets/Customers/Models/Customer");
    $invoiceModel = $this->adios->getModel("Widgets/Finances/Models/Invoice");

    $customer = reset($this->adios->db->get_all_rows_query("
      select * from `".$customerModel->getFullTableSQLName()."`
      where id = ".(int) $this->params['id']."
    "));
    
    return $this->adios->ui->Window([
      "uid" => "{$this->uid}_window",
      "title" => $customer['email'],
      "subtitle" => $this->translate("Invoices"),
      "content" => 
        $this->adios->renderAction("UI/Table", [
          "model" => "Widgets/Finances/Models/Invoice",
          "where" => $invoiceModel->getFullTableSQLName().".id_customer = ".(int) $this->params['id'],
          "show_title" => FALSE,
        ]),
    ])->render();
  }
}