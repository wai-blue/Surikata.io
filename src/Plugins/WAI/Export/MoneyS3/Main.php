<?php

namespace ADIOS\Plugins\WAI\Export;

class MoneyS3 extends \Surikata\Core\AdminPanel\Plugin {

  var $niceName = "MoneyS3 Export";

  public function onModelAfterSave($event) {
    $model = $event['model'];
    $data = $event['data'];

    if (strpos($model->name, "Widgets/Products/") === 0) {
      $settings = $this->adios->config["settings"]["plugins"]["WAI"]["Export"]["MoneyS3"];
      $outputFileProducts = $settings["outputFileProducts"];
      $outputFileOrders = $settings["outputFileOrders"];

      if (!empty($outputFileProducts)) {
        $productModel = new \ADIOS\Widgets\Products\Models\Product($this->adios);
        $products = $productModel->getAll();

        $h = fopen(PROJECT_ROOT_DIR."/".$outputFileProducts, "w");
        fwrite($h, print_r($products, TRUE));
        fclose($h);
      }

      if (!empty($outputFileOrders)) {
        $orderModel = new \ADIOS\Widgets\Orders\Models\Order($this->adios);
        $orders = $orderModel->getAll();

        $h = fopen(PROJECT_ROOT_DIR."/".$outputFileOrders, "w");
        fwrite($h, print_r($orders, TRUE));
        fclose($h);
      }
    }

    return $event; // forward event unchanged
  }

}
