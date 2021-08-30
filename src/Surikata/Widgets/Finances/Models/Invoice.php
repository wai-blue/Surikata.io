<?php

namespace ADIOS\Widgets\Finances\Models;

class Invoice extends \ADIOS\Core\Model {

  /* Invoice payment methods */
  const PAYMENT_METHOD_WIRE_TRANSFER = 1;
  const PAYMENT_METHOD_CASH          = 2;
  const PAYMENT_METHOD_CHEQUE        = 3;
  const PAYMENT_METHOD_CARD          = 4;

  /* Invoice states */
  const STATE_ISSUED = 1;
  const STATE_SENT   = 2;
  const STATE_PAID   = 3;

  var $sqlName = "invoices";
  var $lookupSqlValue = "{%TABLE%}.number";
  var $urlBase = "Invoices";
  var $tableTitle = "Invoices";
  var $formTitleForInserting = "New invoice";
  var $formTitleForEditing = "Invoice nr. {{ number }}";

  var $disableNotifications = FALSE;

  public function init() {
    $this->enumInvoicePaymentMethods = [
      self::PAYMENT_METHOD_WIRE_TRANSFER => 'Bankovým prevodom',
      self::PAYMENT_METHOD_CASH          => 'V hotovosti',
      self::PAYMENT_METHOD_CHEQUE        => 'Dobierka',
      self::PAYMENT_METHOD_CARD          => 'Platobná karta',
    ];

    $this->enumInvoiceStates = [
      self::STATE_ISSUED => 'Issued',
      self::STATE_SENT   => 'Sent',
      self::STATE_PAID   => 'Paid',
    ];

  }

  public function columns(array $columns = []) {
    return parent::columns([
      "id_numeric_series" => [
        "type" => "lookup",
        "title" => "Numeric series",
        "model" => "Widgets/Finances/Models/InvoiceNumericSeries",
      ],

      "accounting_year" => [
        "type" => "int",
        "title" => "Accounting Year",
        "readonly" => TRUE,
      ],

      "serial_number" => [
        "type" => "int",
        "title" => "Serial number",
        "readonly" => TRUE,
        "show_column" => TRUE,
      ],

      "number" => [
        "type" => "varchar",
        "title" => "Number",
        "show_column" => TRUE,
      ],

      "id_customer" => [
        "type" => "lookup",
        "title" => $this->translate("Customer"),
        "model" => "Widgets/Customers/Models/Customer",
        "show_column" => TRUE,
      ],

      "id_order" => [
        "type" => "lookup",
        "title" => "Order",
        "model" => "Widgets/Orders/Models/Order",
        "show_column" => FALSE,
      ],

      // CUSTOMER

        "customer_name" => [
          "type" => "varchar",
          "title" => "Customer, Name on the Invoice",
          "show_column" => TRUE,
        ],

        "customer_street_1" => [
          "type" => "varchar",
          "title" => "Customer, Street, 1st line",
        ],

        "customer_street_2" => [
          "type" => "varchar",
          "title" => "Customer, Street, 2nd line",
        ],

        "customer_city" => [
          "type" => "varchar",
          "title" => "Customer, City",
        ],

        "customer_zip" => [
          "type" => "varchar",
          "title" => "Customer, ZIP",
        ],

        "customer_country" => [
          "type" => "varchar",
          "title" => "Customer, Country",
        ],

        "customer_company_id" => [
          "type" => "varchar",
          "title" => "Customer, Company ID",
          "show_column" => TRUE,
        ],

        "customer_company_tax_id" => [
          "type" => "varchar",
          "title" => "Customer, Company TAX ID",
        ],

        "customer_company_vat_id" => [
          "type" => "varchar",
          "title" => "Customer, Company VAT ID",
        ],

        "customer_email" => [
          "type" => "varchar",
          "title" => "Customer, E-mail",
        ],

        "customer_phone" => [
          "type" => "varchar",
          "title" => "Customer, Phone number",
        ],

        "customer_www" => [
          "type" => "varchar",
          "title" => "Customer, Web",
        ],

        "customer_iban" => [
          "type" => "varchar",
          "title" => "Customer, Bank account IBAN",
        ],

      // DODAVATEL

        "supplier_name" => [
          "type" => "varchar",
          "title" => "Supplier, Name on the Invoice",
        ],

        "supplier_street_1" => [
          "type" => "varchar",
          "title" => "Supplier, Street, 1st line",
        ],

        "supplier_street_2" => [
          "type" => "varchar",
          "title" => "Supplier, Street, 2nd line",
        ],

        "supplier_city" => [
          "type" => "varchar",
          "title" => "Supplier, City",
        ],

        "supplier_zip" => [
          "type" => "varchar",
          "title" => "Supplier, ZIP",
        ],

        "supplier_country" => [
          "type" => "varchar",
          "title" => "Supplier, Country",
        ],

        "supplier_company_id" => [
          "type" => "varchar",
          "title" => "Supplier, Company ID",
        ],

        "supplier_company_tax_id" => [
          "type" => "varchar",
          "title" => "Supplier, Company TAX ID",
        ],

        "supplier_company_vat_id" => [
          "type" => "varchar",
          "title" => "Supplier, Company VAT ID",
        ],

        "supplier_email" => [
          "type" => "varchar",
          "title" => "Supplier, E-mail",
        ],

        "supplier_phone" => [
          "type" => "varchar",
          "title" => "Supplier, Phone number",
        ],

        "supplier_www" => [
          "type" => "varchar",
          "title" => "Supplier, Web",
        ],

        "supplier_iban" => [
          "type" => "varchar",
          "title" => "Supplier, Bank account IBAN",
        ],

      // DATUMY

        "issue_time" => [
          "type" => "datetime",
          "title" => "Issued",
          "show_column" => TRUE,
        ],

        "delivery_time" => [
          "type" => "datetime",
          "title" => "Delivered",
          "show_column" => TRUE,
        ],

        "payment_due_time" => [
          "type" => "datetime",
          "title" => "Payment Due",
          "show_column" => TRUE,
        ],

        // "datum_uhrady" => [
        //   "type" => "date",
        //   "title" => "Dátum úhrady",
        //   "show_column" => TRUE,
        // ],

      // OSTATNE

        "variable_symbol" => [
          "type" => "varchar",
          "title" => "Variable symbol",
          "show_column" => TRUE,
        ],

        "specific_symbol" => [
          "type" => "varchar",
          "title" => "Specific symbol",
        ],

        "constant_symbol" => [
          "type" => "varchar",
          "title" => "Constant symbol",
        ],

        "payment_method" => [
          "type" => "int",
          "enum_values" => $this->enumInvoicePaymentMethods,
          "title" => "Forma úhrady",
          "show_column" => FALSE,
        ],

        "order_number" => [
          "type" => "varchar",
          "title" => "Order number",
        ],

        "cislo_dodacieho_listu" => [
          "type" => "varchar",
          "title" => "Číslo dodacieho listu",
        ],

        "notes" => [
          "type" => "varchar",
          "byte_size" => 255,
          "title" => "Notes",
          "show_column" => TRUE,
        ],

        "state" => [
          "type" => "int",
          "byte_size" => 8,
          "enum_values" => $this->enumInvoiceStates,
          "title" => "State",
          "show_column" => TRUE,
        ],
    ]);
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "invoice___accounting_year___serial_number" => [
        "type" => "unique",
        "columns" => ["accounting_year", "serial_number"],
      ],
      "invoice___number" => [
        "type" => "unique",
        "columns" => ["number"],
      ],
    ]);
  }

  public function routing(array $routing = []) {
    return parent::routing([
      '/^Invoices\/(\d+)\/PrintInvoice$/' => [
        "action" => "Finances/Invoices/PrintInvoice",
        "params" => [
          "id" => '$1',
        ]
      ],
      '/^Invoices\/(\d+)\/TlacitDodaciList$/' => [
        "action" => "Finances/Invoices/TlacitDodaciList",
        "params" => [
          "id" => '$1',
        ]
      ],
    ]);
  }

  public function getById($id) {
    $id = (int) $id;

    $invoice = reset($this->adios->db->get_all_rows_query("
      select
        fv.*
      from {$this->table} fv
      where fv.id = {$id}
    "));

    return $this->getExtendedData($invoice);
  }

  public function getByNumber($number) {
    $invoice = reset($this->adios->db->get_all_rows_query("
      select
        inv.*
      from {$this->table} inv
      where inv.number = '".$this->adios->db->escape($number)."'
    "));

    return $this->getExtendedData($invoice);
  }

  public function getExtendedData($invoice) {
    if ($invoice['id'] > 0) {
      $orderModel = $this->adios->getModel("Widgets/Orders/Models/Order");
      $invoiceItemModel = $this->adios->getModel("Widgets/Finances/Models/InvoiceItem");

      $invoice['ORDER'] = reset($this->adios->db->get_all_rows_query("
        select
          o.*
        from {$orderModel->table} o
        where o.id_invoice = {$invoice['id']}
      "));

      $invoice['ITEMS'] = $this->adios->db->get_all_rows_query("
        select
          p.*
        from {$invoiceItemModel->table} p
        where p.id_invoice = {$invoice['id']}
      ");

      $sumaCelkomBezDPH = 0;
      $sumaCelkomSDPH = 0;
      $sumaCelkomDPH = 0;

      foreach ($invoice['ITEMS'] as $key => $item) {
        $tmpMnozstvo = $invoice['ITEMS'][$key]['quantity'];
        $tmpSadzbaDPH = $invoice['ITEMS'][$key]['dph'];

        $tmpJednotkovaCenaBezDPH = $invoice['ITEMS'][$key]['unit_price'];
        $tmpSumaBezDPH = round($tmpJednotkovaCenaBezDPH * $tmpMnozstvo, 2);

        $tmpJednotkovaCenaDPH = round($tmpJednotkovaCenaBezDPH * ($tmpSadzbaDPH / 100), 2);
        $tmpSumaDPH = round($tmpJednotkovaCenaDPH * $tmpMnozstvo, 2);

        $tmpJednotkovaCenaSDPH = $tmpJednotkovaCenaBezDPH + $tmpJednotkovaCenaDPH;
        $tmpSumaSDPH = $tmpSumaBezDPH + $tmpSumaDPH;


        $invoice['ITEMS'][$key]['sadzba_dph'] = $tmpSadzbaDPH;

        $invoice['ITEMS'][$key]['jednotkova_cena_bez_dph'] = $tmpJednotkovaCenaBezDPH;
        $invoice['ITEMS'][$key]['jednotkova_cena_dph'] = $tmpJednotkovaCenaDPH;
        $invoice['ITEMS'][$key]['jednotkova_cena_s_dph'] = $tmpJednotkovaCenaSDPH;

        $invoice['ITEMS'][$key]['suma_bez_dph'] = $tmpSumaBezDPH;
        $invoice['ITEMS'][$key]['suma_dph'] = $tmpSumaDPH;
        $invoice['ITEMS'][$key]['suma_s_dph'] = $tmpSumaSDPH;

        if (!is_array($invoice['SADZBY_DPH'][$tmpSadzbaDPH])) {
          $invoice['SADZBY_DPH'][$tmpSadzbaDPH] = [
            "sadzba" => $tmpSadzbaDPH,
            "zaklad_celkom" => 0,
            "suma_celkom" => 0,
          ];
        }

        $invoice['SADZBY_DPH'][$tmpSadzbaDPH]['zaklad_celkom'] += $invoice['ITEMS'][$key]['suma_bez_dph'];
        $invoice['SADZBY_DPH'][$tmpSadzbaDPH]['suma_celkom'] += $tmpSumaDPH;

        $sumaCelkomDPH += $tmpSumaDPH;
        $sumaCelkomBezDPH += $tmpSumaBezDPH;
        $sumaCelkomSDPH += $tmpSumaSDPH;
      }

      $invoice['SUMAR'] = [
        "suma_celkom_dph" => $sumaCelkomDPH,
        "suma_celkom_bez_dph" => $sumaCelkomBezDPH,
        "suma_celkom_s_dph" => $sumaCelkomSDPH,
      ];
    }

    return $invoice;
  }

  public function deleteById($idInvoice) {
    $idInvoice = (int) $idInvoice;

    $orderModel = $this->adios->getModel("Widgets/Orders/Models/Order");
    $invoiceItemModel = $this->adios->getModel("Widgets/Finances/Models/InvoiceItem");

    $invoice = $this->getById($idInvoice);

    $tmp = reset($this->adios->db->get_all_rows_query("
      select
        i.*
      from {$this->table} i
      where
        i.accounting_year = ".(int) $invoice['accounting_year']."
        and i.serial_number > ".(int) $invoice['serial_number']."
      limit 1
    "));

    if ($tmp['id'] > 0) {
      return "Unable to delete the invoice. Invoice with higher serial number have been issued already.";
    } else {
      $this->adios->db->query("delete from `".$invoiceItemModel->getFullTableSqlName()."` where `id_invoice` = {$idInvoice}");
      $this->adios->db->query("delete from `{$this->table}` where `id` = {$idInvoice}");
      $this->adios->db->query("
        update `".$orderModel->getFullTableSqlName()."` set
          `id_invoice` = 0,
          `state` = ".\ADIOS\Widgets\Orders\Models\Order::STATE_NEW."
        where `id_invoice` = {$idInvoice}
      ");

      return TRUE;
    }
  }

  public function formParams($data, $params) {
    if ($data['id'] <= 0) {
      $params['title'] = "New invoice";

      $params["template"] = [
        "columns" => [
          [
            "tabs" => [
              "Customer" => [
                ["html" => "<h4>Choose customer from addressbook</h4>"],
                "id_customer",
                ["html" => "<h4>or enter the details manually.</h4>"],
                "customer_name",
                "customer_street_1",
                "customer_street_2",
                "customer_city",
                "customer_zip",
                "customer_country",
                "customer_company_id",
                "customer_company_tax_id",
                "customer_company_vat_id",
                "customer_email",
                "customer_phone",
                "customer_www",
                "customer_iban",
              ],
            ],
          ],
        ],
      ];

      $params["columns"]["id_customer"]["onchange"] = "
        _ajax_read(
          'Customers/Ajax/GetCustomerData',
          {id: $('#{$params['uid']}_id_customer').val()},
          function(res) {

            let customer_names = [];
            if (res.given_name != '') customer_names.push(res.given_name);
            if (res.family_name != '') customer_names.push(res.family_name);
            if (res.company_name != '') customer_names.push(res.company_name);

            $('#{$params['uid']}_customer_name').val(customer_names.join(' '));

            if (res.ADDRESSES && res.ADDRESSES[0]) {
              let a = res.ADDRESSES[0];
              $('#{$params['uid']}_customer_street_1').val(a.inv_street_1);
              $('#{$params['uid']}_customer_street_2').val(a.inv_street_2);
              $('#{$params['uid']}_customer_city').val(a.inv_city);
              $('#{$params['uid']}_customer_zip').val(a.inv_zip);
              $('#{$params['uid']}_customer_country').val(a.inv_country);
              $('#{$params['uid']}_customer_email').val(a.email);
              $('#{$params['uid']}_customer_phone').val(a.phone_number);
            }

            $('#{$params['uid']}_customer_company_id').val(res.company_id);
            $('#{$params['uid']}_customer_company_tax_id').val(res.company_tax_id);
            $('#{$params['uid']}_customer_company_vat_id').val(res.company_vat_id);
          }
        );
      ";

    } else {
      $params['title'] = "Invoice nr. ".hsc($data['number']);

      $btn_print_invoice_html = $this->adios->ui->button([
        "text"    => "Print invoice",
        "onclick" => "window.open(_APP_URL + '/Invoices/".(int) $data['id']."/PrintInvoice');",
        "class"   => "btn-primary mb-2 w-100",
      ])->render();

      $params["template"] = [
        "columns" => [
          [
            "class" => "col-md-9 pl-0",
            "tabs" => [
              "General" => [
                "accounting_year",
                "serial_number",
                "number",
                "variable_symbol",
                "constant_symbol",
                "specific_symbol",
                ["html" => "<hr>"],
                "delivery_time",
                "issue_time",
                "payment_due_time",
                "datum_uhrady",
                ["html" => "<hr>"],
                "payment_method",
                "order_number",
                "cislo_dodacieho_listu",
                "notes",
                "state",
              ],
              "Items" => [
                "action" => "UI/Table",
                "params" => [
                  "model"      => "Widgets/Finances/Models/InvoiceItem",
                  "id_invoice" => (int) $data['id'],
                ]
              ],
              "Customer" => [
                "customer_name",
                "customer_street_1",
                "customer_street_2",
                "customer_city",
                "customer_zip",
                "customer_country",
                "customer_company_id",
                "customer_company_tax_id",
                "customer_company_vat_id",
                "customer_email",
                "customer_phone",
                "customer_www",
                "customer_iban",
              ],
              "Supplier" => [
                "supplier_name",
                "supplier_street_1",
                "supplier_street_2",
                "supplier_city",
                "supplier_zip",
                "supplier_country",
                "supplier_company_id",
                "supplier_company_tax_id",
                "supplier_company_vat_id",
                "supplier_email",
                "supplier_phone",
                "supplier_www",
                "supplier_iban",
              ],
              // "Úhrady" => [
              //   "action" => "Finances/Invoices/TabUhrady",
              //   "params" => [
              //     "id_invoice" => $data['id'],
              //   ]
              // ],
            ],
          ],
          [
            "class" => "col-md-3 pr-0",
            "html" => "
              {$btn_print_invoice_html}
              <br/><br/>
              <b>Customer</b><br/>
              ".hsc($data['customer_name'])."</br>
              ".hsc($data['customer_street_1'])."</br>
              ".hsc($data['customer_zip'])." ".hsc($data['customer_city'])."</br>
              </br>
              <b>".number_format($data['SUMAR']['suma_celkom_bez_dph'], 2, ",", " ")." EUR excl. VAT</b><br/>
              <b>".number_format($data['SUMAR']['suma_celkom_s_dph'], 2, ",", " ")." EUR incl. VAT</b><br/>
            ",
          ],
        ],
      ];

    }

    return $params;
  }

  public function getDefaultValuesForNewInvoice($issueTime = "now") {
    $numberingPattern = "ymd";
    $issueTime = date("Y-m-d H:i:s", strtotime($issueTime));

    return [
      "accounting_year" => ["sql" => "year('{$issueTime}')"],
      "serial_number" => ["sql" => "
        @serial_number := (ifnull(
          (
            select
              ifnull(max(`i`.`serial_number`), 0)
            from `{$this->table}` `i`
            where year(`i`.`issue_time`) = year('{$issueTime}')
          ),
          0
        ) + 1)
      "],
      "number" => ["sql" => "
        @number := concat(
          '".date($numberingPattern, strtotime($issueTime))."',
          lpad(@serial_number, 4, '0')
        )
      "],
      "issue_time" => $issueTime,
    ];
  }

  public function onBeforeSave($data) {
    return array_merge(
      $this->getDefaultValuesForNewInvoice(),
      ["variable_symbol" => ["sql" => "@number"]],
      $data
    );
  }

  public function issueInvoice($data) {
    // $data = [
    //   "HEADER" => [
    //     "variable_symbol", "constant_symbol", "specific_symbol"
    //     "payment_method", "order_number", "cislo_dodacieho_listu", "notes",
    //   ],
    //   "TIMESTAMPS" => [
    //     "issue_time", "delivery_time", "payment_due_time"
    //   ],
    //   "CUSTOMER" => [
    //     "id", "name", "ulica_1", "ulica_2", "mesto", "psc", "stat",
    //     "company_id", "company_tax_id", "company_vat_id",
    //     "email", "phone_number",
    //     "cislo_uctu", "cislo_uctu_iban",
    //   ],
    //   "SUPPLIER" => [
    //     "name", "ulica_1", "ulica_2", "mesto", "psc", "stat",
    //     "company_id", "company_tax_id", "company_vat_id",
    //     "email", "phone_number",
    //     "cislo_uctu", "cislo_uctu_iban",
    //   ],
    //   "ITEMS" => [
    //     "item", "unit_price", "quantity", "merna_jednotka", "dph"
    //   ],
    // ];

    $invoiceItemModel = new \ADIOS\Widgets\Finances\Models\InvoiceItem($this->adios);

    $issueTime = (empty($data["TIMESTAMPS"]["issue_time"])
      ? date("Y-m-d H:i:s")
      : date("Y-m-d H:i:s", strtotime($data["TIMESTAMPS"]["issue_time"]))
    );

    $deliveryTime = (empty($data["TIMESTAMPS"]["delivery_time"])
      ? date("Y-m-d H:i:s", strtotime($issueTime))
      : date("Y-m-d H:i:s", strtotime($data["TIMESTAMPS"]["delivery_time"]))
    );

    $paymentDueTime = (empty($data["TIMESTAMPS"]["payment_due_time"])
      ? date("Y-m-d H:i:s")
      : date("Y-m-d H:i:s", strtotime($data["TIMESTAMPS"]["payment_due_time"], "+14 days"))
    );

    $idInvoice = $this->insertRow(array_merge(
      $this->getDefaultValuesForNewInvoice($issueTime),
      [
        "state" => self::STATE_ISSUED,
        "variable_symbol" => (empty($data["HEADER"]["variable_symbol"])
          ? ["sql" => "@number"]
          : $data["HEADER"]["variable_symbol"]
        ),
        "specific_symbol" => $data["HEADER"]["specific_symbol"],
        "constant_symbol" => $data["HEADER"]["constant_symbol"],

        "delivery_time" => $deliveryTime,
        "payment_due_time" => $paymentDueTime,

        "id_order" => (int) $data["HEADER"]["id_order"],
        "id_customer" => (int) $data["CUSTOMER"]["id"],

        "customer_name" => $data["CUSTOMER"]["name"],
        "customer_street_1" => $data["CUSTOMER"]["street_1"],
        "customer_street_2" => $data["CUSTOMER"]["street_2"],
        "customer_city" => $data["CUSTOMER"]["city"],
        "customer_zip" => $data["CUSTOMER"]["zip"],
        "customer_country" => $data["CUSTOMER"]["country"],
        "customer_company_id" => $data["CUSTOMER"]["company_id"],
        "customer_company_tax_id" => $data["CUSTOMER"]["company_tax_id"],
        "customer_company_vat_id" => $data["CUSTOMER"]["company_vat_id"],
        "customer_email" => $data["CUSTOMER"]["email"],
        "customer_phone" => $data["CUSTOMER"]["phone_number"],
        "customer_iban" => $data["CUSTOMER"]["iban"],

        "supplier_name" => $data["SUPPLIER"]["name"],
        "supplier_street_1" => $data["SUPPLIER"]["ulica_1"],
        "supplier_street_2" => $data["SUPPLIER"]["ulica_2"],
        "supplier_city" => $data["SUPPLIER"]["mesto"],
        "supplier_zip" => $data["SUPPLIER"]["psc"],
        "supplier_country" => $data["SUPPLIER"]["stat"],
        "supplier_company_id" => $data["SUPPLIER"]["company_id"],
        "supplier_company_tax_id" => $data["SUPPLIER"]["company_tax_id"],
        "supplier_company_vat_id" => $data["SUPPLIER"]["company_vat_id"],
        "supplier_email" => $data["SUPPLIER"]["email"],
        "supplier_phone" => $data["SUPPLIER"]["phone_number"],
        "supplier_iban" => $data["SUPPLIER"]["cislo_uctu"],

        "payment_method" => $data["HEADER"]["payment_method"],
        "order_number" => $data["HEADER"]["order_number"],
        "cislo_dodacieho_listu" => (empty($data["HEADER"]["cislo_dodacieho_listu"])
          ? "SQL:@number"
          : $data["HEADER"]["cislo_dodacieho_listu"]
        ),
        "notes" => $data["HEADER"]["notes"],
      ]
    ));

    if (is_array($data["ITEMS"])) {
      foreach ($data["ITEMS"] as $item) {
        $invoiceItemModel->insertRow([
          "id_invoice" => $idInvoice,
          "item" => $item['item'],
          "quantity" => $item['quantity'],
          "id_delivery_unit" => $item['id_delivery_unit'],
          "unit_price" => $item['unit_price'],
          "vat_percent" => $item['vat_percent'],
        ]);
      }
    }

    $issuedInvoiceData = $this->getById($idInvoice);

    $this->sendNotificationForIssuedInvoice($issuedInvoiceData);

    return $this->getById($idInvoice);
  }

  public function sendNotificationForIssuedInvoice($invoiceData) {

    if ($this->disableNotifications) return;

    $subject = $this->adios->config["settings"]["emails"]['after_regular_invoice_issue_SUBJECT'];
    $body = $this->adios->config["settings"]["emails"]['after_regular_invoice_issue_BODY'];
    $signature = $this->adios->config["settings"]["emails"]['signature'];

    $invoiceHtml = print_r($invoiceData, TRUE);

    $this->adios->sendEmail(
      $invoiceData['customer_email'],
      str_replace("{#}", $invoiceData['number'], $subject),
      "
        <div style='font-family:Verdana;font-size:10pt'>
          ".str_replace("{#}", $invoiceHtml, $body)."
        </div>
        <div style='font-family:Verdana;font-size:10pt;padding-top:10px;margin-top:10px;border-top:1px solid #AAAAAA'>{$signature}</div>
      ",
      ""
    );
  }

}