<?php

namespace ADIOS\Actions\Orders;

use ADIOS\Widgets\Orders\Models\Order;
use ADIOS\Widgets\Finances\Models\Invoice;

class PrintOrder extends \ADIOS\Core\Widget\Action {
  public static $hideDefaultDesktop = TRUE;

  public function preRender() {
    $orderModel = new Order($this->adios);

    $order = $orderModel->prepareInvoiceData(
     $this->params['id']
    );

    foreach ($order["ITEMS"] as $key => $item) {
      if (is_numeric($item["id_delivery_unit"])) {
        $order["ITEMS"][$key]["DELIVERY_UNIT"] = (new \ADIOS\Widgets\Settings\Models\Unit($this->adios))->getById((int)$item["id_delivery_unit"]);
      }
    }

    $order['ITEMS'] = (new \ADIOS\Widgets\Finances($this->adios))
      ->calculatePricesForInvoice($order['ITEMS'])
    ;
    
    $order['SUMMARY'] = $orderModel->calculateSummaryInfo($order);

    switch($order["HEADER"]["payment_method"]) {
      case Invoice::PAYMENT_METHOD_WIRE_TRANSFER:
        $order["HEADER"]["payment_method_value"] = "Bank transfer";
        break;
      case Invoice::PAYMENT_METHOD_CASH:
        $order["HEADER"]["payment_method_value"] = "Cash";
        break;
      case Invoice::PAYMENT_METHOD_CHEQUE:
        $order["HEADER"]["payment_method_value"] = "Cheque";
        break;
      case Invoice::PAYMENT_METHOD_CARD:
        $order["HEADER"]["payment_method_value"] = "Card";
        break;
    }

    return [
      "order" => $order,
      "template" => 1
    ];
  }
}