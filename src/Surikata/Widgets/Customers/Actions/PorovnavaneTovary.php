<?php

namespace ADIOS\Actions\Customers;

class PorovnavaneProdukty extends \ADIOS\Core\Action {
  public function init() {
    $this->languageDictionary["en"] = [
      "Klient" => "Customer",
      "Porovnávané produkty" => "Compared products",
    ];
  }

  public function render() {
    $customerModel = $this->adios->getModel("Widgets/Customers/Models/Customer");

    $customer = reset($this->adios->db->get_all_rows_query("
      select * from {$customerModel->table}
      where id = ".(int) $this->params['id']."
    "));
    
    return $this->adios->ui->Window([
      "uid" => "{$this->uid}_window",
      "title" => 
        $this->translate("Klient").
        " [{$customer['code']}] {$customer['given_name']} {$customer['family_name']} {$customer['company_name']} &raquo; ".
        $this->translate("Porovnávané produkty")
      ,
      "content" => 
        $this->adios->renderAction("UI/Table", [
          "model" => "Widgets/Customers/Models/CustomerProduktPorovnavany",
          "id_customer" => (int) $this->params['id'],
        ]),
    ])->render();
  }
}