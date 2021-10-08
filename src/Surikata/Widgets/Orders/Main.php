<?php

namespace ADIOS\Widgets;

class Orders extends \ADIOS\Core\Widget {
  public function init() {
    if ($this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_SALES)) {
      $orderModel = new \ADIOS\Widgets\Orders\Models\Order($this->adios);

      $this->adios->config['desktop']['sidebarItems']['Orders'] = [
        "fa_icon" => "fas fa-shopping-basket",
        "title" => $this->translate("Orders"),
        "onclick" => "desktop_update('Orders');",
        "sub" => [
          [
            "title" => $this->translate("New"),
            "onclick" => "desktop_update('Orders/New');",
            "style" => "border-left:5px solid {$orderModel->enumOrderStateColors[$orderModel::STATE_NEW]}",
          ],
          [
            "title" => $this->translate("Invoiced"),
            "onclick" => "desktop_update('Orders/Invoiced');",
            "style" => "border-left:5px solid {$orderModel->enumOrderStateColors[$orderModel::STATE_INVOICED]}",
          ],
          [
            "title" => $this->translate("Paid"),
            "onclick" => "desktop_update('Orders/Paid');",
            "style" => "border-left:5px solid {$orderModel->enumOrderStateColors[$orderModel::STATE_PAID]}",
          ],
          [
            "title" => $this->translate("Shipped"),
            "onclick" => "desktop_update('Orders/Shipped');",
            "style" => "border-left:5px solid {$orderModel->enumOrderStateColors[$orderModel::STATE_SHIPPED]}",
          ],
          [
            "title" => $this->translate("Canceled"),
            "onclick" => "desktop_update('Orders/Canceled');",
            "style" => "border-left:5px solid {$orderModel->enumOrderStateColors[$orderModel::STATE_CANCELED]}",
          ],
          //[
          //  "title" => "Claims",
          //  "onclick" => "desktop_update('Claims');",
          //],
        ],
      ];
    }
  }

}


