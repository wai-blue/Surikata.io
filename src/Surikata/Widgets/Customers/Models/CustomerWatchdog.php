<?php

namespace ADIOS\Widgets\Customers\Models;

class CustomerWatchdog extends \ADIOS\Core\Widget\Model {
  var $sqlName = "customers_watchdog";
  var $urlBase = "Customers/{{ id_customer }}/Watchdog";
  var $tableTitle = "Customer watchdog";
  var $formTitleForInserting = "New customer watchdog";
  var $formTitleForEditing = "Customer watchdog";

  public function columns(array $columns = []) {
    return parent::columns([
      "id_customer" => [
        "type" => "lookup",
        "title" => "Customer",
        "model" => "Widgets/Customers/Models/Customer",
        "readonly" => TRUE,
        "required" => TRUE,
      ],

      "id_product" => [
        "type" => "lookup",
        "title" => "Product",
        "model" => "Widgets/Products/Models/Product",
        "readonly" => TRUE,
        "required" => TRUE,
        "show_column" => TRUE,
      ],
    ]);
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "id_customer___id_product" => [
        "type" => "unique",
        "columns" => ["id_customer", "id_product"],
      ],
    ]);
  }

  public function routing(array $routing = []) {
    return parent::routing([
      '/^Customers\/(\d+)\/Watchdog$/' => [
        "action" => "UI/Cards",
        "params" => [
          "model" => $this->name,
          "id_customer" => '$1',
        ]
      ],
    ]);
  }

  public function cardsParams($params) {
    $params['where'] = "`{$this->table}`.`id_customer` = ".(int) $params['id_customer'];
    $customerModel = $this->adios->getModel("Widgets/Customers/Models/Customer");
    $customer = $customerModel->getById($params['id_customer']);

    $params['columns'] = 4;
    $params['window']['title'] = $this->translate("Watchdog");
    $params['window']['subtitle'] = $customer['email'];
    return $params;
  }

  public function cardsCardHtmlFormatter($cardsObject, $data) {
    return "
      <div class='card shadow mb-2'>
        <div class='card-header py-3'>
          <h6 class='m-0 font-weight-bold text-primary'>".hsc($data['PRODUCT']["name_lang_{$this->adios->translatedColumnIndex}"])."</h6>
        </div>
        <div class='card-body text-center' style='height:220px;'>
          <div style='height:calc(100% - 2.5em);overflow:hidden;margin-bottom:1em'>
            <img
              src='{$this->adios->config['upload_url']}/{$data['PRODUCT']['image']}'
              style='max-width:100%;max-height:100%;cursor:pointer;'
              onclick='window_render(\"Products/{$data['id_product']}/Edit\");'
            />
          </div>
          <a
            href='javascript:void(0)'
            onclick='window_render(\"Products/{$data['id_product']}/Edit\");'
          >". $this->translate("Show product")."</a>
        </div>
      </div>
    ";
  }

}