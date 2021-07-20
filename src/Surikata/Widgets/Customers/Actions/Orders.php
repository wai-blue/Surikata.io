<?php

namespace ADIOS\Actions\Customers;

class Orders extends \ADIOS\Core\Action {
  public function render() {
    $orderModel = $this->adios->getModel("Widgets/Orders/Models/Order");
    $customerModel = $this->adios->getModel("Widgets/Customers/Models/Customer");

    $customer = reset($this->adios->db->get_all_rows_query("
      select * from `".$customerModel->getFullTableSQLName()."`
      where id = ".(int) $this->params['id']."
    "));
    
    return $this->adios->ui->Window([
      "uid" => "{$this->uid}_window",
      "title" => $customer['email'],
      "subtitle" => $this->translate("Orders"),
      "content" => 
        $this->adios->renderAction("UI/Table", [
          "model" => "Widgets/Orders/Models/Order",
          "where" => $orderModel->getFullTableSQLName().".id_customer = ".(int) $this->params['id'],
          "show_title" => FALSE,
          "column_settings" => ["id_customer" => ["show_column" => FALSE]],
        ]),
    ])->render();
  }
}