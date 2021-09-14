<?php

namespace Surikata\Plugins\WAI\Customer {
  class Cart extends \Surikata\Core\Web\Plugin {
    var $cartContents = NULL;

    public function getCartContents($reload = FALSE) {
      if ($reload || $this->cartContents === NULL) {
        $customerUID = $this->websiteRenderer->getCustomerUID();

        $cartModel = new \ADIOS\Widgets\Customers\Models\ShoppingCart($this->adminPanel);

        $this->cartContents = $cartModel->getCartContents($customerUID);

        $idProducts = [];
        foreach ($this->cartContents['items'] as $key => $value) {
          $this->cartContents['items'][$key]['PRODUCT']['url'] =
            (new \Surikata\Plugins\WAI\Product\Detail($this->websiteRenderer))
            ->getWebPageUrl($this->cartContents['items'][$key]['PRODUCT'])
          ;
          $idProducts[] = $this->cartContents['items'][$key]['PRODUCT']['id'];
        }
      }

      return $this->cartContents;

    }

    public function renderCartContentsOverview() {
      $tmpParams = $this->websiteRenderer->twigParams;
      $tmpParams["cartContents"] = $this->getCartContents(TRUE);
      return $this->websiteRenderer->twig->render(
        "{$this->websiteRenderer->twigTemplatesSubDir}/Cart/Overview.twig",
        $tmpParams
      );
    }

    public function addToCart($idProduct, $qty) {
      $customerUID = $this->websiteRenderer->getCustomerUID();
      return (new \ADIOS\Widgets\Customers\Models\ShoppingCart($this->adminPanel))
        ->addProductToCart($customerUID, $idProduct, $qty)
      ;
    }

    public function updateQty($idProduct, $qty) {
      $customerUID = $this->websiteRenderer->getCustomerUID();
      return (new \ADIOS\Widgets\Customers\Models\ShoppingCart($this->adminPanel))
        ->updateProductQty($customerUID, $idProduct, $qty)
      ;
    }

    public function removeFromCart($idProduct) {
      $customerUID = $this->websiteRenderer->getCustomerUID();
      return (new \ADIOS\Widgets\Customers\Models\ShoppingCart($this->adminPanel))
        ->removeProductFromCart($customerUID, $idProduct)
      ;
    }

    public function placeOrder($orderData) {
      $customerUID = $this->websiteRenderer->getCustomerUID();

      if ($orderData["createAccount"] == "1") {
        $orderData['id_customer'] = (new \ADIOS\Widgets\Customers\Models\Customer($this->adminPanel))
          ->createAccount(
            $customerUID,
            $orderData['email'],
            $orderData,
            TRUE // saveAddress
          )
        ;
      } else if (!empty($this->websiteRenderer->userLogged['id'])) {
        $orderData['id_customer'] = $this->websiteRenderer->userLogged['id'];
      } else {
        $orderData['id_customer'] = 0;
      }

      return (new \ADIOS\Widgets\Orders\Models\Order($this->adminPanel))
        ->placeOrder($orderData, $customerUID)
      ;
    }

    public function renderJSON() {
      $returnArray = [];

      $cartAction = $this->websiteRenderer->urlVariables['cartAction'] ?? "";
      $idProduct = (int) $this->websiteRenderer->urlVariables['idProduct'] ?? 0;
      
      $qty = (string) $this->websiteRenderer->urlVariables['qty'] ?? "";
      $qty = (float) str_replace(",", ".", str_replace(" ", "", $qty));

      switch ($cartAction) {
        case "addToCart":
          $returnArray["itemAdded"] = $this->addToCart($idProduct, $qty);
          $returnArray["cartOverviewHtml"] = $this->renderCartContentsOverview();
        break;
        case "updateQty":
          $returnArray["itemUpdated"] = $this->updateQty($idProduct, $qty);
          $returnArray["cartOverviewHtml"] = $this->renderCartContentsOverview();
        break;
        case "removeFromCart":
          $returnArray["itemRemoved"] = $this->removeFromCart($idProduct);
          $returnArray["cartOverviewHtml"] = $this->renderCartContentsOverview();
        break;
        case "placeOrder":
          $orderData = $this->websiteRenderer->urlVariables["orderData"];

          try {
            $idOrder = $this->placeOrder($orderData);

            $orderModel = new \ADIOS\Widgets\Orders\Models\Order($this->adminPanel);
            $placedOrder = $orderModel->getById($idOrder);

            $returnArray["idOrder"] = $idOrder;
            $returnArray["status"] = "OK";
            $returnArray["placedOrder"] = $placedOrder;
            $returnArray["orderConfirmationUrl"] = (new \Surikata\Plugins\WAI\Order\Confirmation($this->websiteRenderer))
              ->getWebPageUrl(["order" => $placedOrder])
            ;

          } catch (
            \ADIOS\Widgets\Orders\Exceptions\PlaceOrderUnknownError
            | \ADIOS\Widgets\Orders\Exceptions\EmptyRequiredFields
            | \ADIOS\Widgets\Orders\Exceptions\UnknownCustomer
            | \ADIOS\Widgets\Orders\Exceptions\UnknownDeliveryService
            | \ADIOS\Widgets\Orders\Exceptions\UnknownShipment
            | \ADIOS\Widgets\Customers\Exceptions\EmailIsEmpty
            | \ADIOS\Widgets\Customers\Exceptions\EmailIsInvalid
            | \ADIOS\Widgets\Customers\Exceptions\AccountAlreadyExists
            | \ADIOS\Widgets\Customers\Exceptions\CreateAccountUnknownError
            $e
          ) {
            $returnArray["status"] = "FAIL";
            $returnArray["exception"] = get_class($e);
            $returnArray["error"] = $e->getMessage();
          }

        break;
      }

      return $returnArray;

    }

  }
}

namespace ADIOS\Plugins\WAI\Customer {
  class Cart extends \Surikata\Core\AdminPanel\Plugin {
  }
}