<?php

namespace ADIOS\Widgets\Orders\Models;
use ADIOS\Widgets\Shipping\Models\DeliveryService;

class Order extends \ADIOS\Core\Model {
  const STATE_NEW      = 1;
  const STATE_INVOICED = 2;
  const STATE_PAID     = 3;
  const STATE_SHIPPED  = 4;
  const STATE_RECEIVED = 5;
  const STATE_CANCELED = 6;

  var $sqlName = "orders";
  var $lookupSqlValue = "{%TABLE%}.number";
  var $urlBase = "Orders";
  var $tableTitle = "Orders";
  var $formTitleForInserting = "New order";
  var $formTitleForEditing = "Order # {{ number }}";
  var $formAddButtonText = "Create new order";
  var $formSaveButtonText = "Update order";

  var $disableNotifications = FALSE;

  public function init() {

    $this->enumOrderStates = [
      self::STATE_NEW      => 'New',
      self::STATE_INVOICED => 'Invoiced',
      self::STATE_PAID     => 'Paid',
      self::STATE_SHIPPED  => 'Shipped',
      self::STATE_RECEIVED => 'Received',
      self::STATE_CANCELED => 'Canceled',
    ];

    $this->enumOrderStateColors = [
      self::STATE_NEW      => '#0000FF',     //blue
      self::STATE_INVOICED => '#FFA500',     //orange
      self::STATE_PAID     => '#008000',     //green
      self::STATE_SHIPPED  => '#800080',     //purple
      self::STATE_RECEIVED => '#D3D3D3',     //light-gray
      self::STATE_CANCELED => '#808080',     //gray
    ];

    $this->enumDeliveryServices = (new DeliveryService($this->adios))->getEnumValues();

  }

  public function columns(array $columns = []) {
    return parent::columns([
      "accounting_year" => [
        "type" => "int",
        "title" => "Accounting Year",
        "readonly" => TRUE,
        "description" => "Will be generated automaticaly",
      ],

      "serial_number" => [
        "type" => "int",
        "title" => "Serial number",
        "readonly" => TRUE,
        "description" => "Will be generated automaticaly",
      ],

      "number" => [
        "type" => "varchar",
        "title" => "Number",
        "pattern" => '\d{10}',
        "readonly" => TRUE,
        "description" => "Will be generated automaticaly",
        "show_column" => TRUE,
      ],

      "id_customer" => [
        "type" => "lookup",
        "title" => "Customer",
        "model" => "Widgets/Customers/Models/Customer",
        "show_column" => TRUE,
      ],

      "id_customer_uid" => [
        "type" => "lookup",
        "title" => "Customer",
        "model" => "Widgets/Customers/Models/CustomerUID",
        "show_column" => FALSE,
      ],



      "del_given_name" => [
        "type" => "varchar",
        "title" => "Delivery: Given Name",
      ],

      "del_family_name" => [
        "type" => "varchar",
        "title" => "Delivery: Family Name",
      ],

      "del_company_name" => [
        "type" => "varchar",
        "title" => "Delivery: Company Name",
      ],

      "del_street_1" => [
        "type" => "varchar",
        "title" => "Delivery: Street, 1st line",
      ],

      "del_street_2" => [
        "type" => "varchar",
        "title" => "Delivery: Street, 2nd line",
      ],

      "del_floor" => [
        "type" => "varchar",
        "title" => "Delivery: Floor",
      ],

      "del_city" => [
        "type" => "varchar",
        "title" => "Delivery: City",
        "show_column" => TRUE,
      ],

      "del_zip" => [
        "type" => "varchar",
        "title" => "Delivery: ZIP",
      ],

      "del_region" => [
        "type" => "varchar",
        "title" => "Delivery: Region",
      ],

      "del_country" => [
        "type" => "varchar",
        "title" => "Delivery: Country",
      ],



      "inv_given_name" => [
        "type" => "varchar",
        "title" => "Invoice: Given Name",
        "show_column" => TRUE,
      ],

      "inv_family_name" => [
        "type" => "varchar",
        "title" => "Invoice: Family Name",
        "show_column" => TRUE,
      ],

      "inv_company_name" => [
        "type" => "varchar",
        "title" => "Invoice: Company Name",
        "show_column" => TRUE,
      ],

      "inv_street_1" => [
        "type" => "varchar",
        "title" => "Invoice: Street, 1st line",
      ],

      "inv_street_2" => [
        "type" => "varchar",
        "title" => "Invoice: Street, 2nd line",
      ],

      "inv_city" => [
        "type" => "varchar",
        "title" => "Invoice: City",
        "show_column" => TRUE,
      ],

      "inv_zip" => [
        "type" => "varchar",
        "title" => "Invoice: ZIP",
      ],

      "inv_region" => [
        "type" => "varchar",
        "title" => "Invoice: Region",
      ],

      "inv_country" => [
        "type" => "varchar",
        "title" => "Invoice: Country",
      ],



      "confirmation_time" => [
        "type" => "datetime",
        "title" => "Confirmed",
        "show_column" => true
      ],

      "delivery_service" => [
        "type" => "varchar",
        "title" => "Delivery service",
        "enum_values" => $this->enumDeliveryServices,
      ],

      "required_delivery_time" => [
        "type" => "date",
        "title" => "Required delivery time",
        "show_column" => true
      ],
    
      "delivery_price" => [
        "type" => "float",
        "title" => "Delivery price",
        "unit" => $this->adios->locale->currencySymbol(),
      ],

      "number_customer" => [
        "type" => "varchar",
        "title" => "Customer`s order number",
      ],

      "notes" => [
        "type" => "text",
        "title" => "Notes",
      ],

      "state" => [
        "type" => "int",
        "enum_values" => $this->enumOrderStates,
        "title" => "State",
        "show_column" => true
      ],

      "id_invoice" => [
        "type" => "lookup",
        "title" => "Invoice",
        "model" => "Widgets/Finances/Models/Invoice",
        "show_column" => TRUE,
      ],

      "phone_number" => [
        "type" => "varchar",
        "title" => "Contact: Phone number",
      ],

      "email" => [
        "type" => "varchar",
        "title" => "Contact: Email",
      ],

      "domain" => [
        "type" => "varchar",
        "title" => "Domain",
        "readonly" => TRUE,
        "show_column" => TRUE,
      ],
    ]);
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "order___accounting_year___serial_number" => [
        "type" => "unique",
        "columns" => ["accounting_year", "serial_number"],
      ],
      "order___number" => [
        "type" => "unique",
        "columns" => ["number"],
      ],
      "order___delivery_service" => [
        "type" => "index",
        "columns" => ["delivery_service"],
      ],
    ]);
  }

  public function routing(array $routing = []) {
    return parent::routing([
      '/^Orders\/([New|InvoiceIssued|Paid|Shipped|Canceled]+)$/' => [
        "action" => "UI/Table",
        "params" => [
          "model" => "Widgets/Orders/Models/Order",
          "filter_type" => '$1',
        ]
      ],
      '/^Orders\/(\d+)\/Tlacit$/' => [
        "action" => "Orders/Tlacit",
        "params" => [
          "id" => '$1',
        ]
      ],
    ]);
  }

  public function tableParams($params) {
    switch ($params['filter_type']) {
      case "New":
        $params["title"] = "New orders";
        $params['where'] = "{$this->table}.state = (".self::STATE_NEW.")";
      break;
      case "InvoiceIssued":
        $params["title"] = "Invoiced orders";
        $params['where'] = "{$this->table}.state = (".self::STATE_INVOICED.")";
      break;
      case "Paid":
        $params["title"] = "Paid orders";
        $params['where'] = "{$this->table}.state = (".self::STATE_PAID.")";
      break;
      case "Shipped":
        $params["title"] = "Shipped orders";
        $params['where'] = "{$this->table}.state = (".self::STATE_SHIPPED.")";
      break;
      case "Canceled":
        $params["title"] = "Canceled orders";
        $params['where'] = "{$this->table}.state = (".self::STATE_CANCELED.")";
      break;
      default:
        $params["title"] = "All orders";
      break;
    }
    $params['order_by'] = "number DESC";

    return $params;
  }

  public function onBeforeSave($data) {

    return $data;
  }

  public function calculateOrderNumber($orderData) {
    $tmp = (int) ($orderData['serial_number'] ?? 0);

    return
      date("ymd", strtotime($orderData['confirmation_time']))
      .str_pad($tmp, 4, "0", STR_PAD_LEFT)
    ;
  }

  /**
   * @param array $orderData
   * @param null $customerUID
   * @param null $cartContents
   * @param bool $checkRequiredFields
   * @return int|string
   * @throws \ADIOS\Widgets\Orders\Exceptions\EmptyRequiredFields
   * @throws \ADIOS\Widgets\Orders\Exceptions\InvalidCustomerID
   * @throws \ADIOS\Widgets\Orders\Exceptions\PlaceOrderUnknownError
   * @throws \ADIOS\Widgets\Orders\Exceptions\UnknownCustomer
   * @throws \ADIOS\Widgets\Orders\Exceptions\UnknownDeliveryService
   * @throws \ADIOS\Widgets\Orders\Exceptions\UnknownShipment
   */
  public function placeOrder($orderData, $customerUID = NULL, $cartContents = NULL, $checkRequiredFields = TRUE) {
    $idCustomer = 0;
    $idAddress = (int) $orderData['id_address'];
    $cartModel = new \ADIOS\Widgets\Customers\Models\ShoppingCart($this->adios);
    $customerModel = new \ADIOS\Widgets\Customers\Models\Customer($this->adios);
    $customerUIDModel = new \ADIOS\Widgets\Customers\Models\CustomerUID($this->adios);
    $customerAddressModel = new \ADIOS\Widgets\Customers\Models\CustomerAddress($this->adios);

    if (!empty($customerUID)) {
      $customerUIDlink = $customerUIDModel->getByCustomerUID($customerUID);
      $idCustomerUID = $customerUIDlink['id'];
    }

    if (!empty($orderData['id_customer'])) {
      $idCustomer = (int) $orderData['id_customer'];

      $customer = $customerModel->getById($idCustomer);
      if ((int) $customer['id'] == 0) {
        throw new \ADIOS\Widgets\Orders\Exceptions\InvalidCustomerID();
      }
    }

    if ($idCustomer == 0 && $idCustomerUID == 0) {
      throw new \ADIOS\Widgets\Orders\Exceptions\UnknownCustomer($customerUID);
    }

    $requiredFieldsEmpty = [];

    if ($idAddress <= 0 && $checkRequiredFields) {
      $requiredFieldsConsents = [
        "general_terms_and_conditions",
        "gdpr_consent"
      ];

      $requiredFieldsBilling = [
        "inv_given_name",
        "inv_family_name",
        "inv_street_1",
        "inv_city",
        "inv_zip",
        "phone_number",
        "email",
      ];
      $requiredFieldsDelivery = [
        "del_given_name",
        "del_family_name",
        "del_street_1",
        "del_city",
        "del_zip",
      ];

      foreach ($requiredFieldsBilling as $fieldName) {
        if (empty($orderData[$fieldName])) {
          $requiredFieldsEmpty[] = $fieldName;
        }
        if ($orderData["differentDeliveryAddress"] != "1") {
          $replaceInvForDel = str_replace('inv', 'del', $fieldName);
          $orderData[$replaceInvForDel] = $orderData[$fieldName];
        }
      }

      if ($orderData["differentDeliveryAddress"] == "1") {
        foreach ($requiredFieldsDelivery as $fieldName) {
          if (empty($orderData[$fieldName])) {
            $requiredFieldsEmpty[] = $fieldName;
          }
        }
      }
    }

    foreach ($requiredFieldsConsents as $fieldName) {
      if (empty($orderData[$fieldName])) {
        $requiredFieldsEmpty[] = $fieldName;
      }
    }

    if (count($requiredFieldsEmpty) > 0) {
      throw new \ADIOS\Widgets\Orders\Exceptions\EmptyRequiredFields(join(",", $requiredFieldsEmpty));
    }

    if ($idAddress <= 0 && $idCustomer != 0) {
      $idAddress = $customerAddressModel->saveAddress($idCustomer, $orderData);
    }

    if ($cartContents === NULL && !empty($customerUID)) {
      $cartContents = $cartModel->getCartContents($customerUID);
    }

    if (!empty($orderData['deliveryService'])) {
      $shipmentModel = 
        new \ADIOS\Widgets\Shipping\Models\Shipment(
          $this->adminPanel
        )
      ;

      $shipmentPriceModel = 
        new \ADIOS\Widgets\Shipping\Models\ShipmentPrice(
          $this->adminPanel
        )
      ;

      $shipment = 
        $shipmentModel->getShipment(
          $orderData['deliveryService'],
          $orderData['paymentMethod']
        )
      ; 

      if ($shipment === NULL) {
        throw new \ADIOS\Widgets\Orders\Exceptions\UnknownShipment();
      }

      $deliveryPrice = 
        ($shipmentPriceModel->getById($shipment['id']))['shipment_price']
      ;
    } else {
      //$deliveryPlugin = NULL;
      $deliveryPrice = 0;
    }

    if (empty($orderData['confirmation_time'])) {
      $confirmationTime = date("Y-m-d H:i:s");
    } else {
      $confirmationTime = date("Y-m-d H:i:s", strtotime($orderData['confirmation_time']));
    }

    $idOrder = $this->insertRow([
      "accounting_year" => ["sql" => "year('{$confirmationTime}')"],
      "serial_number" => ["sql" => "
        @serial_number := (ifnull(
          (
            select
              ifnull(max(`o`.`serial_number`), 0)
            from `{$this->table}` o
            where year(`o`.`confirmation_time`) = year('{$confirmationTime}')
          ),
          0
        ) + 1)
      "],
      "number" => $orderData['number'] ?? ["sql" => "
        @number := concat('".date("ymd", strtotime($confirmationTime))."', lpad(@serial_number, 4, '0'))
      "],
      "id_customer"       => $idCustomer ?? 0,
      "id_customer_uid"   => $idCustomerUID ?? 0,

      "del_given_name"    => $orderData['del_given_name'],
      "del_family_name"   => $orderData['del_family_name'],
      "del_company_name"  => $orderData['del_company_name'],
      "del_street_1"      => $orderData['del_street_1'],
      "del_street_2"      => $orderData['del_street_2'],
      "del_floor"         => $orderData['del_floor'],
      "del_city"          => $orderData['del_city'],
      "del_zip"           => $orderData['del_zip'],
      "del_region"        => $orderData['del_region"'],
      "del_country"       => $orderData['del_country'],

      "inv_given_name"    => $orderData['inv_given_name'],
      "inv_family_name"   => $orderData['inv_family_name'],
      "inv_company_name"  => $orderData['inv_company_name'],
      "inv_street_1"      => $orderData['inv_street_1'],
      "inv_street_2"      => $orderData['inv_street_2'],
      "inv_city"          => $orderData['inv_city'],
      "inv_zip"           => $orderData['inv_zip'],
      "inv_region"        => $orderData['inv_region'],
      "inv_country"       => $orderData['inv_country'],

      "phone_number"      => $orderData['phone_number'],
      "email"             => $orderData['email'],
      "confirmation_time" => $confirmationTime,
      "delivery_service"  => $orderData['deliveryService'],
      "delivery_price"    => $deliveryPrice,
      "notes"             => $orderData['notes'],
      "domain"            => $orderData['domain'],
      "state"             => self::STATE_NEW,
    ]);

    if (!is_numeric($idOrder)) {
      throw new \ADIOS\Widgets\Orders\Exceptions\PlaceOrderUnknownError();
    }

    (new \ADIOS\Widgets\Orders\Models\OrderHistory($this->adios))
      ->insertRow([
        "id_order" => $idOrder,
        "state" => self::STATE_NEW,
        "event_time" => "SQL:now()",
      ])
    ;

    $placedOrderData = $this->getById($idOrder);
    // $placedOrderNumber = $this->calculateOrderNumber($placedOrderData);
    // $this->updateRow(["number" => $placedOrderNumber], $idOrder);
    // $placedOrderData["number"] = $placedOrderNumber;

    foreach ($cartContents['items'] as $item) {
      $this->addItem($idOrder, [
        "id_product" => $item["id_product"],
        "quantity" => $item["quantity"],
        "id_delivery_unit" => $item["PRODUCT"]["id_delivery_unit"],
        "unit_price" => $item["unit_price"],
        "vat_percent" => $item["PRODUCT"]["vat_percent"],
      ]);
    }

    $cartModel->emptyCart($customerUID);

    $this->sendNotificationForPlacedOrder($placedOrderData);

    return $idOrder;

  }

  public function sendNotificationForPlacedOrder($orderData) {

    if ($this->disableNotifications) return;

    $domain = $this->adios->websiteRenderer->currentPage['domain'];

    $subject = $this->adios->config["settings"]["emails"][$domain]['after_order_confirmation_SUBJECT'];
    $body = $this->adios->config["settings"]["emails"][$domain]['after_order_confirmation_BODY'];
    $signature = $this->adios->config["settings"]["emails"][$domain]['signature'];

    // Create variables from table orders without id_cols and arrays
    foreach ($orderData as $key => $col) {
      if (!str_contains($key, 'id_') && !is_array($col)) {
        $camelCaseKey = lcfirst(str_replace('_', '', ucwords($key, "_")));
        $body = str_replace("{% $camelCaseKey %}", $col, $body);
      }
    }

    $this->adios->sendEmail(
      $orderData["email"],
      str_replace("{% number %}", $orderData["number"], $subject),
      "
        <div style='font-family:Verdana;font-size:10pt;'>
          {$body}
        </div>
        <div style='font-family:Verdana;font-size:10pt;padding-top:10px;margin-top:10px;border-top:1px solid #AAAAAA'>{$signature}</div>
      ",
      ""
    );
  }

  /**
   * Function create new order mainly from admin
   * For Frontend checkout use placeOrder function
   * @param $idCustomer
   * @param null $orderData
   * @throws \ADIOS\Widgets\Orders\Exceptions\EmptyRequiredFields
   * @throws \ADIOS\Widgets\Orders\Exceptions\InvalidCustomerID
   * @throws \ADIOS\Widgets\Orders\Exceptions\PlaceOrderUnknownError
   * @throws \ADIOS\Widgets\Orders\Exceptions\UnknownCustomer
   * @throws \ADIOS\Widgets\Orders\Exceptions\UnknownDeliveryService
   */
  public function addCustomerInfoToOrderData($orderData) {
    if (!is_array($orderData)) {
      throw new \ADIOS\Widgets\Orders\Exceptions\InvalidOrderDataFormat();
    }

    if ($orderData["id_customer"] <= 0) {
      throw new \ADIOS\Widgets\Orders\Exceptions\InvalidCustomerID();
    }

    $customerModel = new \ADIOS\Widgets\Customers\Models\Customer($this->adios);
    $customer = $customerModel->getById($orderData["id_customer"]);

    $orderData["inv_given_name"] = $customer["inv_given_name"];
    $orderData["inv_family_name"] = $customer["inv_family_name"];
    $orderData["inv_company_name"] = $customer["inv_company_name"];
    $orderData["inv_street_1"] = $customer["inv_street_1"];
    $orderData["inv_street_2"] = $customer["inv_street_2"];
    $orderData["inv_city"] = $customer["inv_city"];
    $orderData["inv_zip"] = $customer["inv_zip"];
    $orderData["inv_region"] = $customer["inv_region"];
    $orderData["inv_country"] = $customer["inv_country"];


    if (count($customer["ADDRESSES"]) > 0) {
      $orderData["id_address"] = $customer["ADDRESSES"][0]["id"];
      $addressFields = [
        "phone_number",
        "email",
        "del_given_name",
        "del_family_name",
        "del_company_name",
        "del_street_1",
        "del_street_2",
        "del_floor",
        "del_city",
        "del_zip",
        "del_region",
        "del_country",
      ];
      foreach ($addressFields as $field) {
        $orderData[$field] = $customer["ADDRESSES"][0][$field];
        // if delivery address is empty - use inv address
        if (strpos($field, "del_") !== false) {
          $invField = str_ireplace("del_", "inv_", $field);
          if (
            strlen($customer["ADDRESSES"][0][$field]) == 0
            && strlen($customer["ADDRESSES"][0][$invField]) > 0
          ) {
            $orderData[$field] = $customer["ADDRESSES"][0][$invField];
          }
        }
      }
    }

    return $orderData;
  }

  public function changeOrderState($idOrder, $data, $isCron = false) {
    $this->updateRow(["state" => $data["state"]], $idOrder);

    $idUser = $isCron ? 0 : $this->adios->userProfile['id'];
    (new \ADIOS\Widgets\Orders\Models\OrderHistory($this->adios))
      ->insertRow([
        "id_order" => $idOrder,
        "state" => $data["state"],
        "event_time" => "SQL:now()",
        "user" => $idUser,
      ])
    ;

  }

  public function addItem($idOrder, $item) {
    $item["id_order"] = $idOrder;
    return (new \ADIOS\Widgets\Orders\Models\OrderItem($this->adios))
      ->insertRow($item)
    ;
  }

  public function formParams($data, $params) {

    if ($data['id'] <= 0) {
      $params["template"] = [
        "columns" => [
          [
            "rows" => [
              "id_customer",
              // "number_customer",
              "notes",
            ],
          ],

        ],
      ];
      $params['save_action'] = "Orders/PlaceOrder";
    } else {

      $btnIssueInvoice = $this->adios->ui->button([
        "text"    => "Issue invoice",
        "onclick" => "
          let tmp_form_id = $(this).closest('.adios.ui.Form').attr('id');
          _ajax_read('Orders/IssueInvoice', 'id_order=".(int) $data['id']."', function(res) {
            if (isNaN(res)) {
              alert(res);
            } else {
              // refresh order window
              window_refresh(tmp_form_id + '_form_window');
            }
          });
        ",
        "class"   => "btn-light mb-2 w-100",
        "style" => "border-left: 10px solid {$this->enumOrderStateColors[self::STATE_INVOICED]}",
      ])->render();

      $btnShowInvoice = $this->adios->ui->button([
        "text" => "Show invoice nr. ".hsc($data['INVOICE']['number']),
        "onclick" => "
          let tmp_form_id = $(this).closest('.adios.ui.Form').attr('id');
          window_render('Invoices/".(int) $data['INVOICE']['id']."/Edit', '', function(res) {
            // refresh order window
            window_refresh(tmp_form_id + '_form_window');
          });
        ",
        "class"   => "btn-light mb-2 w-100",
        "style" => "border-left: 10px solid {$this->enumOrderStateColors[self::STATE_INVOICED]}",
      ])->render();

      $btnOrderStatePaid = $this->adios->ui->button([
        "text" => "Set as paid",
        "onclick" => "
          let tmp_form_id = $(this).closest('.adios.ui.Form').attr('id');
          _ajax_read('Orders/ChangeOrderState', 'id_order=".(int) $data['id']."&state=".(int) self::STATE_PAID."', function(res) {
            if (isNaN(res)) {
              alert(res);
            }
            else {
              // refresh order window
              window_refresh(tmp_form_id + '_form_window');
            }
          });
        ",
        "class" => "btn-light mb-2 w-100",
        "style" => "border-left: 10px solid {$this->enumOrderStateColors[self::STATE_PAID]}",
      ])->render();

      $btnOrderStateShipped = $this->adios->ui->button([
        "text" => "Set as shipped",
        "onclick" => "
          let tmp_form_id = $(this).closest('.adios.ui.Form').attr('id');
          _ajax_read('Orders/ChangeOrderState', 'id_order=".(int) $data['id']."&state=".(int) self::STATE_SHIPPED."', function(res) {
            if (isNaN(res)) {
              alert(res);
            }
            else {
              // refresh order window
              window_refresh(tmp_form_id + '_form_window');
            }
          });
        ",
        "class" => "btn-light mb-2 w-100",
        "style" => "border-left: 10px solid {$this->enumOrderStateColors[self::STATE_SHIPPED]}",
      ])->render();


      $btnOrderStateCanceled = $this->adios->ui->button([
        "text" => "Set as canceled",
        "onclick" => "
          let tmp_form_id = $(this).closest('.adios.ui.Form').attr('id');
          _ajax_read('Orders/ChangeOrderState', 'id_order=".(int) $data['id']."&state=".(int) self::STATE_CANCELED."', function(res) {
            if (isNaN(res)) {
              alert(res);
            }
            else {
              // refresh order window
              window_refresh(tmp_form_id + '_form_window');
            }
          });
        ",
        "class" => "btn-light mb-2 w-100",
        "style" => "border-left: 10px solid {$this->enumOrderStateColors[self::STATE_CANCELED]}",
      ])->render();

      $formTitle = "Order&nbsp;#&nbsp;".hsc($data["number"]);

      $sidebarHtml = $this->adios->dispatchEventToPlugins("onOrderDetailSidebarButtons", [
        "model" => $this,
        "params" => $params,
        "data" => $data,
      ])["html"];
      $sidebarHtml .= "
        <div class='card shadow mb-2'>
          <div class='card-header py-3'>
            Order is
            <span
              class='badge badge-adios'
              style='background-color: {$this->enumOrderStateColors[$data['state']]};'
            >
              {$this->enumOrderStates[$data['state']]}
            </span>
          </div>
          <div class='card-body'>
            {$btnOrderStatePaid}
            {$btnOrderStateShipped}
            {$btnOrderStateCanceled}
          </div>
        </div>
      ";

      $params["titleRaw"] = $formTitle;
      $params["template"] = [
        "columns" => [
          [
            "class" => "col-md-9",
            "tabs" => [
              "General" => [
                "serial_number",
                "number",
                "id_customer",
                "confirmation_time",
                "number_customer",
                "notes",
                "domain",
                [
                  "title" => "State",
                  "input" => "
                    <div
                      class='badge badge-adios'
                      style='background-color: {$this->enumOrderStateColors[$data['state']]};'
                    >
                      {$this->enumOrderStates[$data['state']]}
                    </div>
                  "
                ],
              ],
              "Billing" => [
                "inv_given_name",
                "inv_family_name",
                "inv_company_name",
                "inv_street_1",
                "inv_street_2",
                "inv_city",
                "inv_zip",
                "inv_region",
                "inv_country",
              ],
              "Delivery" => [
                "required_delivery_time",
                "delivery_service",
                "delivery_price",
                "del_given_name",
                "del_family_name",
                "del_company_name",
                "del_street_1",
                "del_street_2",
                "del_floor",
                "del_city",
                "del_zip",
                "del_region",
                "del_country",
              ],
              "Items" => [
                "action" => "UI/Table",
                "params" => [
                  "model"    => "Widgets/Orders/Models/OrderItem",
                  "id_order" => (int) $data['id'],
                ]
              ],
              "History" => [
                "action" => "UI/Table",
                "params" => [
                  "model"    => "Widgets/Orders/Models/OrderHistory",
                  "id_order" => (int) $data['id'],
                ]
              ],
            ],
          ],
          [
            "class" => "col-md-3",
            "html" => "
              <div style='margin-bottom:2em'>
                ".($data['INVOICE']['id'] <= 0 ? $btnIssueInvoice : $btnShowInvoice)."
              </div>
              {$sidebarHtml}
            ",
          ]
        ],
      ];

    }

    return $params;

  }

  public function tableCellCSSFormatter($data) {
    // if ($data['column'] == "id") {
    //   return "border-left:10px solid {$this->enumOrderStateColors[$data['row']['state']]};";
    // }
    if ($data['column'] == "number") {
      return "border-left:10px solid {$this->enumOrderStateColors[$data['row']['state']]};";
    }
  }

  public function getById($id) {
    $id = (int) $id;

    $order = reset($this->adios->db->get_all_rows_query("
      select
        obj.*
      from {$this->table} obj
      where obj.id = {$id}
    "));

    return $this->getExtendedData($order);
  }

  public function getByNumber($number) {
    $order = reset($this->adios->db->get_all_rows_query("
      select
        o.*
      from {$this->table} o
      where o.number = '".$this->adios->db->escape($number)."'
    "));

    return $this->getExtendedData($order);
  }

  public function getExtendedData($order) {
    if ($order['id'] > 0) {
      $orderItemModel = new \ADIOS\Widgets\Orders\Models\OrderItem($this->adios);
      $productModel = new \ADIOS\Widgets\Products\Models\Product($this->adios);
      $customerModel = new \ADIOS\Widgets\Customers\Models\Customer($this->adios);
      $invoiceModel = new \ADIOS\Widgets\Finances\Models\Invoice($this->adios);

      $order['ITEMS'] = $this->adios->db->get_all_rows_query("
        select
          i.*,
          p.number as product_number,
          p.name_lang_1 as product_name
        from `{$orderItemModel->table}` i
        left join `{$productModel->table}` p on p.id = i.id_product
        where i.id_order = ".(int) $order['id']."
      ");

      $order['CUSTOMER'] = reset($this->adios->db->get_all_rows_query("
        select
          k.*
        from `{$customerModel->table}` k
        where k.id = ".(int) $order['id_customer']."
      "));

      $order['INVOICE'] = reset($this->adios->db->get_all_rows_query("
        select
          f.*
        from `{$invoiceModel->table}` f
        where f.id = ".(int) $order['id_invoice']."
      "));
    }

    return $order;

  }

  public function getCheckCode($order) {
    return 
      substr(md5($order['number'].$order['id_customer']), 0, 5)
      ."-"
      .md5($order['id_customer'].$order['number'])
      ."-"
      .substr(strrev(md5($order['number'].$order['id_customer'])), 0, 5)
    ;
  }

  public function validateCheckCode($order, $checkCode) {
    return $checkCode == $this->getCheckCode($order);
  }

  public function deleteById($idOrder) {
    $idOrder = (int) $idOrder;

    $orderItemModel = new \ADIOS\Widgets\Orders\Models\OrderItem($this->adios);
    $orderHistoryModel = new \ADIOS\Widgets\Orders\Models\OrderHistory($this->adios);

    $order = $this->getById($idOrder);

    if ($order['id_invoice'] > 0) {
      return "Unable to delete the order with inovice issued.";
    } else {
      $this->adios->db->query("delete from `{$orderItemModel->table}` where `id_order` = {$idOrder}");
      $this->adios->db->query("delete from `{$orderHistoryModel->table}` where `id_order` = {$idOrder}");
      $this->adios->db->query("delete from `{$this->table}` where `id` = {$idOrder}");

      return TRUE;
    }
  }

  public function prepareInvoiceData($idOrder, $useDatesFromOrder = FALSE) {
    $order = $this->getById($idOrder);

    $invoiceItems = [];
    foreach ($order['ITEMS'] as $item) {
      $invoiceItems[] = [
        "item" => $item["product_number"]." ".$item["product_name"],
        "quantity" => $item["quantity"],
        "id_delivery_unit" => $item["id_delivery_unit"],
        "unit_price" => $item["unit_price"],
        "vat_percent" => $item["vat_percent"],
      ];
    }

    $invoiceData = [
      "HEADER" => [
        "id_order" => $idOrder,
        "constant_symbol" => "0008",
        "payment_method" => \ADIOS\Widgets\Finances\Models\Invoice::PAYMENT_METHOD_WIRE_TRANSFER,
        "order_number" => $order['number'],
      ],
      "CUSTOMER" => [
        "id" => $order["id_customer"],
        "company_id" => $order["CUSTOMER"]["company_id"],
        "company_tax_id" => $order["CUSTOMER"]["company_tax_id"],
        "company_vat_id" => $order["CUSTOMER"]["company_vat_id"],
        "street_1" => $order["inv_street_1"],
        "street_2" => $order["inv_street_2"],
        "city" => $order["inv_city"],
        "zip" => $order["inv_zip"],
        "country" => $order["inv_country"],
        "email" => $order["email"],
        "phone_number" => $order["phone_number"],
      ],
      "SUPPLIER" => [
        "name" => "MyCompany ltd.",
        "ulica_1" => "Brandenburgische Straße 81",
        "mesto" => "Berlin",
        "psc" => "10119",
        "stat" => "Germany",
        "company_id" => "123456789",
        "company_tax_id" => "9988776655",
        "company_vat_id" => "DE9988776655",
        "email" => "info@mycompany.sk",
        "phone_number" => "030 55 61562",
      ],
      "ITEMS" => $invoiceItems,
    ];

    if ($useDatesFromOrder) {
      $invoiceData["TIMESTAMPS"]["issue_time"] = $order['confirmation_time'];
    }

    return $invoiceData;
  }

  public function assignToInvoice($idOrder, $idInvoice) {
    $this->updateRow([
      "id_invoice" => $idInvoice,
      "state" => self::STATE_INVOICED,
    ], $idOrder);

    $this->changeOrderState($idOrder, ["state" => self::STATE_INVOICED]);
    
  }

  public function issueInvoce($idOrder, $useDatesFromOrder = FALSE) {
    $invoiceModel = new \ADIOS\Widgets\Finances\Models\Invoice($this->adios);
    $invoiceModel->disableNotifications = $this->disableNotifications;

    $invoiceData = $this->prepareInvoiceData($idOrder, $useDatesFromOrder);
    $invoice = $invoiceModel->issueInvoice($invoiceData);
    $this->assignToInvoice($idOrder, $invoice['id']);

    return (int) $invoice['id'];
  }

}