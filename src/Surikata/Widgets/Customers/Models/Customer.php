<?php

namespace ADIOS\Widgets\Customers\Models;

class Customer extends \ADIOS\Core\Model {
  var $sqlName = "customers";
  var $urlBase = "Customers";
  var $lookupSqlValue = "{%TABLE%}.email";
  var $tableTitle = "";

  public $lastCreatedAccountPassword = "";

  public function init() {
    $this->languageDictionary["en"] = [
      "Klienti" => "Customers",
      "Všetci klienti" => "All customers",
      "Blokovaní klienti" => "Blocked accounts",
      "Neoverení klienti" => "Non-validated accounts",
      "Veľkoobchodníci" => "Wholesale customers",
      "Nový klient" => "New customer",
    ];

    $this->tableTitle = $this->translate("Customers");
  }

  public function columns(array $columns = []) {
    return parent::columns([
      "id_category" => [
        "type" => "lookup",
        "title" => $this->translate("Category"),
        "model" => "Widgets/Customers/Models/CustomerCategory",
        "show_column" => TRUE,
      ],

      "email" => [
        "type" => "varchar",
        "title" => "Email",
        "css_class" => "flex-2",
        "show_column" => TRUE,
      ],

      "given_name" => [
        "type" => "varchar",
        "title" => "Given name",
        "show_column" => TRUE,
      ],

      "family_name" => [
        "type" => "varchar",
        "title" => "Family name",
        "show_column" => TRUE,
      ],

      "company_name" => [
        "type" => "varchar",
        "title" => "Company name",
        "show_column" => TRUE,
      ],

      "company_id" => [
        "type" => "varchar",
        "title" => "Company ID",
        "show_column" => TRUE,
      ],

      "company_tax_id" => [
        "type" => "varchar",
        "title" => "Company Tax ID",
        "pattern" => '\d{10}',
        "show_column" => FALSE,
      ],

      "company_vat_id" => [
        "type" => "varchar",
        "title" => "Company VAT ID",
        "show_column" => FALSE,
      ],




      "inv_given_name" => [
        "type" => "varchar",
        "title" => "Billing: Given Name",
      ],

      "inv_family_name" => [
        "type" => "varchar",
        "title" => "Billing: Family Name",
        "show_column" => TRUE,
      ],

      "inv_company_name" => [
        "type" => "varchar",
        "title" => "Billing: Company Name",
        "show_column" => TRUE,
      ],

      "inv_street_1" => [
        "type" => "varchar",
        "title" => "Billing: Street, 1st line",
        "show_column" => TRUE,
      ],

      "inv_street_2" => [
        "type" => "varchar",
        "title" => "Billing: Street, 2nd line",
      ],

      "inv_city" => [
        "type" => "varchar",
        "title" => "Billing: City",
        "show_column" => TRUE,
      ],

      "inv_zip" => [
        "type" => "varchar",
        "title" => "Billing: ZIP",
      ],

      "inv_region" => [
        "type" => "varchar",
        "title" => "Billing: Region",
      ],

      "inv_country" => [
        "type" => "varchar",
        "title" => "Billing: Country",
      ],





      "password" => [
        "type" => "password",
        "title" => "Password",
        "pattern" => '[A-Za-z]{2}\d{3}[A-Za-z\d]{3}',
      ],

      "is_wholesale" => [
        "type" => "boolean",
        "title" => "Is wholesale customer",
      ],

      "is_validated" => [
        "type" => "boolean",
        "title" => "Account is validated",
        "readonly" => TRUE,
      ],

      "is_blocked" => [
        "type" => "boolean",
        "title" => "Account is blocked",
      ],

      "last_login" => [
        "type" => "datetime",
        "title" => "Last login",
        "readonly" => TRUE,
      ],

      "consent_privacy" => [
        "type" => "boolean",
        "title" => "Consent for privacy",
      ],

      "consent_newsletter" => [
        "type" => "boolean",
        "title" => "Consent for newsletter",
      ],

    ]);
  }

  public function indexes($columns = []) {
    return parent::indexes([
      "uid" => [
        "type" => "unique",
        "columns" => ["email"],
      ],
    ]);
  }

  public function routing($columns = []) {
    return parent::routing([
      '/^Customers\/Categories\/Tree$/' => [
        "action" => "UI/Tree",
        "params" => [
          "model" => "Widgets/Customers/Models/CustomerCategory",
        ]
      ],
      '/^Customers\/([NonValidated|Blocked|Wholesale]+)$/' => [
        "action" => "UI/Table",
        "params" => [
          "model" => "Widgets/Customers/Models/Customer",
          "filter_type" => '$1',
        ]
      ],
      '/^Customers\/(\d+)\/Orders$/' => [
        "action" => "Customers/Orders",
        "params" => [
          "id" => '$1',
        ]
      ],
      '/^Customers\/(\d+)\/Invoices$/' => [
        "action" => "Customers/Invoices",
        "params" => [
          "id" => '$1',
        ]
      ],
      '/^Customers\/(\d+)\/PrezeraneProdukty$/' => [
        "action" => "Customers/PrezeraneProdukty",
        "params" => [
          "id" => '$1',
        ]
      ],
      '/^Customers\/(\d+)\/PorovnavaneProdukty$/' => [
        "action" => "Customers/PorovnavaneProdukty",
        "params" => [
          "id" => '$1',
        ]
      ],
    ]);
  }

  public function getById($id) {
    $id = (int) $id;

    $customer = reset($this->adios->db->get_all_rows_query("
      select
        c.*,
        (
          select
            group_concat(uid)
          from ".$this->adios->getModel("Widgets/Customers/Models/CustomerUID")->table."
          where id_customer = c.id
        ) as pridelene_uid_identifikatory
      from `{$this->table}` c
      where `id` = {$id}
    "));
  
    return $this->getExtendedData($customer);
  }

  public function getByEmail($email) {
    $customer = reset($this->adios->db->get_all_rows_query("
      select
        *
      from {$this->table}
      where `email` = '".$this->adios->db->escape($email)."'
    "));

    return $this->getExtendedData($customer);
  }

  public function getExtendedData($customer) {
    if ($customer['id'] > 0) {
      $customer['ADDRESSES'] = $this->adios->db->get_all_rows_query("
        select
          *
        from ".$this->adios->getModel("Widgets/Customers/Models/CustomerAddress")->table."
        where `id_customer` = ".(int) $customer['id']."
      ");
    }

    return $customer;
  }

  public function getCheckCode($email) {
    return md5($email."-".strrev($email)).md5(strrev($email)."-".$email);
  }

  public function validateCheckCode($email, $checkCode) {
    return $checkCode == $this->getCheckCode($email);
  }

  public function validateAccountByEmail($email) {
    $this->adios->db->query("
      update {$this->table} set `is_validated` = TRUE
      where `email` = '".$this->adios->db->escape($email)."'
    ");
  }

  public function tableParams($params) {
    switch ($params['filter_type']) {
      case "Blocked":
        $params["title"] = $this->translate("Customers")." &raquo; ".$this->translate("Blocked");
        $params['where'] = "`is_blocked` = TRUE";
      break;
      case "NonValidated":
        $params["title"] = $this->translate("Customers")." &raquo; ".$this->translate("Non validated");
        $params['where'] = "`is_validated` = TRUE";
      break;
      case "Wholesale":
        $params["title"] = $this->translate("Customers")." &raquo; ".$this->translate("Wholesale");
        $params['where'] = "`is_wholesale` = TRUE";
      break;
      default:
        $params["title"] = $this->translate("Customers")." &raquo; ".$this->translate("All");
      break;
    }

    $params["columns_order"] = [
      "email",
      "given_name",
      "family_name",
      "company_name",
      "company_id",
      // "code",
      "category",
    ];

    return $this->adios->dispatchEventToPlugins("onModelAfterTableParams", [
      "model" => $this,
      "params" => $params,
    ])["params"];

  }

  public function tableCellCSSFormatter($data) {
    if ($data['column'] == "given_name") {
      return "font-weight:bold;";
    }

    if ($data['column'] == "email") {
      return "color:blue;";
    }
  }

  public function formParams($data, $params) {

    if ($data['id'] <= 0) {
      $params['title'] = $this->translate("Nový klient");

      $params["template"] = [
        "columns" => [
          [
            "rows" => [
              // "code",
              "email",
              "given_name",
              "family_name",
              "company_name",
              "company_id",
            ],
          ],
        ],
      ];
    } else {
      $params['title'] = "{$data['email']} {$data['given_name']} {$data['family_name']} {$data['company_name']}";
      $params['subtitle'] = $this->translate("Customer");

      $sidebarHtml = $this->adios->dispatchEventToPlugins("onCustomerDetailSidebarButtons", [
        "model" => $this,
        "params" => $params,
        "data" => $data,
      ])["html"];

      $params["template"] = [
        "columns" => [
          [
            "class" => "col-md-9 pl-0",
            "tabs" => [
              "General" => [
                // ["html" => '<i class="fas fa-tachometer-alt" style="color:#2d4a8a;font-size:5em"></i>'],
                // "code",
                "email",
                "given_name",
                "family_name",
                "company_name",
                "company_id",
                "company_tax_id",
                "company_vat_id",
                "is_wholesale",
                "id_category",
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
              "Delivery addresses" => [
                "action" => "UI/Table",
                "params" => [
                  "model" => "Widgets/Customers/Models/CustomerAddress",
                  "id_customer" => $data['id'],
                ]
              ],
              "Account" => [
                "is_blocked",
                "password",
                "is_validated",
                "last_login",
              ],
              "Consents" => [
                "consent_privacy",
                "consent_newsletter",
              ],
            ],
          ],
          [
            "class" => "col-md-3 pr-0",
            "content" => [
              $this->adios->ui->Button([
                "text" => "Orders",
                "onclick" => "window_render('Customers/{$data['id']}/Orders');",
                "class" => "btn-info mb-2 w-100 text-left",
              ]),
              $this->adios->ui->Button([
                "text" => "Invoices",
                "onclick" => "window_render('Customers/{$data['id']}/Invoices');",
                "class" => "btn-info mb-4 w-100 text-left",
              ]),
              $this->adios->ui->Button([
                "text" => "Wishlist",
                "onclick" => "window_render('Customers/{$data['id']}/Wishlist');",
                "class" => "btn-light mb-2 w-100 text-left",
              ]),
              $this->adios->ui->Button([
                "text" => "Watchdog",
                "onclick" => "window_render('Customers/{$data['id']}/Watchdog');",
                "class" => "btn-light mb-4 w-100 text-left",
              ]),
              "
                <div style='margin-top:2em'>
                  {$sidebarHtml}
                </div>
              "
            ]
          ]
        ],
      ];
    }

    return $this->adios->dispatchEventToPlugins("onModelAfterFormParams", [
      "model" => $this,
      "params" => $params,
    ])["params"];
  }
  
  /**
   * Validates customer's data when modifying through UI/Form.
   *
   * @param  array $data Customer's data
   * @throws \ADIOS\Core\FormSaveException When customer's data are not valid.
   * @return void
   */
  public function formValidate($data) {
    if (empty($data['email'])) {
      throw new \ADIOS\Core\Exceptions\FormSaveException("Email is required.");
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
      throw new \ADIOS\Core\Exceptions\FormSaveException("Email is not valid.");
    }

    if ($data['id'] <= 0) {
      $customer = $this->getByEmail($data['email']);

      if ($customer['id'] > 0) {
        throw new \ADIOS\Core\Exceptions\FormSaveException("Account with email '{$data['email']}' already exists.");
      }
    }
  }
  
  /**
   * Authenticates the customer using his email and password.
   *
   * @param  string $loginEmail Customer's email.
   * @param  string $loginPassword Customer's password.
   * @return object|null Returns NULL if authentication fails, otherwise the customer's profile information.
   */
  public function authenticate($loginEmail, $loginPassword) {
    $tmp = $this
      ->where('email', '=', $loginEmail)
      ->where('is_validated', '=', TRUE)
      ->where('is_blocked', '=', FALSE)
      ->first()
    ;

    if ($tmp !== NULL) {
      $tmp = $this->getById($tmp->id);

      if (password_verify($loginPassword, $tmp['password'])) {
        return $tmp;
      }
    } else {
      return NULL;
    }
  }

  public function assignCustomerUID($idCustomer, $customerUID = "") {
    if (empty($customerUID)) {
      $customerUID = uniqid('', TRUE);
    }

    $this->adios->getModel("Widgets/Customers/Models/CustomerUID")
      ->firstOrCreate([
        "id_customer" => $idCustomer,
        "uid" => $customerUID,
      ])
    ;

    return $customerUID;
  }

  public function createAccount($customerUID, $email, $accountInfo, $saveAddress, $createFromOrder = false) {
    $requiredFieldsEmpty = [];
    $requiredFieldsRegistration = [
      "email",
    ];
    if (!$createFromOrder) {
      $requiredFieldsRegistration[] = "password";
    }

    foreach ($requiredFieldsRegistration as $fieldName) {
      if (empty($accountInfo[$fieldName])) {
        $requiredFieldsEmpty[] = $fieldName;
      }
    }

    if (count($requiredFieldsEmpty) > 0) {
      throw new \ADIOS\Widgets\Customers\Exceptions\EmptyRequiredFields(join(",", $requiredFieldsEmpty));
    }

    if ($accountInfo["password"] !== $accountInfo["password_2"]) {
      throw new \ADIOS\Widgets\Customers\Exceptions\NewPasswordsDoNotMatch();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new \ADIOS\Widgets\Customers\Exceptions\EmailIsInvalid();

    $tmpCustomer = $this->where('email', '=', $email)->get()->toArray();
    $idCustomer = 0;
    if (count($tmpCustomer) > 0) {
      if (strlen($tmpCustomer[0]["password"]) > 0 && $tmpCustomer[0]["is_validated"] !== 0) {
        throw new \ADIOS\Widgets\Customers\Exceptions\AccountAlreadyExists();
      }
      $idCustomer = $tmpCustomer[0]["id"];
    }
  
    $password = $accountInfo["password"];

    foreach ($this->columnNames() as $colName) {
      if (isset($accountInfo[$colName])) {
        $data[$colName] = $accountInfo[$colName];
      }
    }

    $data["email"] = $email;
    $data["password"] = $password;
    $data["password_1"] = $password;
    $data["password_2"] = $password;
    $data["is_validated"] = FALSE;
    $data["is_blocked"] = FALSE;

    if (count($tmpCustomer) == 0) {
      $idCustomer = $this->insertRow($data);
    }
    else {
      $this->where('email', '=', $data['email'])
        ->update([
          "password" => password_hash($data["password"],PASSWORD_DEFAULT),
          "is_validated" => $data["is_validated"],
          "is_blocked" => $data["is_blocked"]]
        )
      ;
    }

    if ($idCustomer == 0) throw new \ADIOS\Widgets\Customers\Exceptions\CreateAccountUnknownError();

    $this->assignCustomerUID(
      $idCustomer,
      $customerUID
    );

    if ($saveAddress) {
      $idAddress = $this->adios
        ->getModel("Widgets/Customers/Models/CustomerAddress")
        ->saveAddress($idCustomer, $accountInfo)
      ;
    }

    $createdAccountInfo = $this->getById($idCustomer);

    $this->lastCreatedAccountPassword = $password;

    if (!$createFromOrder) {
      $this->sendNotificationForCreateAccount($createdAccountInfo);
    }
    return $idCustomer;
  }

  public function changePassword($userLoggedInfo, $currentPassword, $password1, $password2) {
    // REVIEW: premysliet, co s argumentami v thrown exceptions

    if (empty($userLoggedInfo)) {
      throw new \ADIOS\Widgets\Customers\Exceptions\UnknownError("Unknown error! Try refreshing the page.");
    }

    if ($currentPassword !== FALSE) {
      if (!password_verify($currentPassword, $userLoggedInfo['password'])) { 
        throw new \ADIOS\Widgets\Customers\Exceptions\InvalidPassword("Current password is incorrect!");
      }

      // Check if current pass is not same as new one
      if ($currentPassword == $password1) {
        throw new \ADIOS\Widgets\Customers\Exceptions\UnknownError("New password is the same as the current password!");
      }

    }

    if (empty($password1)) {
      throw new \ADIOS\Widgets\Customers\Exceptions\NewPasswordIsEmpty("New password cannot be empty!");
    }

    if (strlen($password1) < 8) {
      throw new \ADIOS\Widgets\Customers\Exceptions\InvalidPasswordLength("Password must be at least 8 characters long");
    } 

    if ($password1 != $password2) {
      throw new \ADIOS\Widgets\Customers\Exceptions\NewPasswordsDoNotMatch("Passwords does not match!");
    }

    $customer = $this
      ->where('id', '=', (int) $userLoggedInfo['id'])
    ;

    if (!$customer->update(
      ["password" => password_hash($password1, PASSWORD_DEFAULT)]
    )) {
      throw new \ADIOS\Widgets\Customers\Exceptions\UnknownError("Unknown error!");
    }

    return TRUE;
  }

  public function changeName($userLoggedInfo, $given_name, $family_name) {
    if (empty($userLoggedInfo)) {
      throw new \ADIOS\Widgets\Customers\Exceptions\UnknownError("Unknown error! Try refreshing the page.");
    }

    $customer = $this
      ->where('id', '=', (int) $userLoggedInfo['id'])
    ;

    $update = $customer->update(["given_name" => $given_name, "family_name" => $family_name]);

    return TRUE;
  }

  public function changeCompanyInfo($userLoggedInfo, $company_name, $company_id, $company_tax_id, $company_vat_id) {
    if (empty($userLoggedInfo)) {
      throw new \ADIOS\Widgets\Customers\Exceptions\UnknownError("Unknown error! Try refreshing the page.");
    }

    $customer = $this
      ->where('id', '=', (int) $userLoggedInfo['id'])
    ;

    $update = $customer->update(
      [
        "company_name" => $company_name,
        "company_id" => $company_id,
        "company_tax_id" => $company_tax_id,
        "company_vat_id" => $company_vat_id,
      ]
    );

    return TRUE;
  }

  public function changeBillingInfo($userLoggedInfo, $address) {
    if (empty($userLoggedInfo)) {
      throw new \ADIOS\Widgets\Customers\Exceptions\UnknownError("Unknown error! Try refreshing the page.");
    }

    $customer = $this
      ->where('id', '=', (int) $userLoggedInfo['id'])
    ;

    $update = $customer->update($address);

    return TRUE;
  }

  public function changeForgotPassword($userLoggedInfo, $password) {
    return $this->changePassword($userLoggedInfo, FALSE, $password, $password);
  }

  public function forgotPassword($email) {
    if ($email == "") {
      throw new \ADIOS\Widgets\Customers\Exceptions\EmailIsEmpty();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new \ADIOS\Widgets\Customers\Exceptions\EmailIsInvalid();
    }
  
    $customer = reset($this
      ->where("email", "=", $email)
      ->get()
      ->toArray()
    );

   
    if ($customer > 0) {
      if ($customer["is_validated"]) {
        $this->sendNotificationForForgotPassword($customer);
      } else {
        throw new \ADIOS\Widgets\Customers\Exceptions\AccountIsNotValidated();
      }
    } else {
      throw new \ADIOS\Widgets\Customers\Exceptions\UnknownAccount();
    }

    return true;
  }

  public function sendNotificationForCreateAccount($accountInfo) {
    $domain = $this->adios->websiteRenderer->currentPage['domain'];

    $subject = $this->adios->config["settings"]["web"][$domain]["emails"]['after_registration_SUBJECT'];
    $body = $this->adios->config["settings"]["web"][$domain]["emails"]['after_registration_BODY'];
    $signature = $this->adios->config["settings"]["web"][$domain]["emails"]['signature'];

    $accountValidationURL = (new \Surikata\Plugins\WAI\Customer\ValidationConfirmation($this->adios->websiteRenderer))
      ->getWebPageUrl(["email" => $accountInfo['email']])
    ;

    $body = str_replace("{% givenName %}", $accountInfo["given_name"], $body);
    $body = str_replace("{% familyName %}", $accountInfo["family_name"], $body);
    $body = str_replace("{% password %}", $this->lastCreatedAccountPassword, $body);
    $body = str_replace(
      "{% validationUrl %}",
      "{$this->adios->websiteRenderer->domain['rootUrl']}{$accountValidationURL}",
      $body
    );

    $this->adios->sendEmail(
      $accountInfo['email'],
      str_replace("{% email %}", $accountInfo['email'], $subject),
      "
        <div style='font-family:Verdana;font-size:10pt'>
          {$body}
        </div>
        <div style='font-family:Verdana;font-size:10pt;padding-top:10px;margin-top:10px;border-top:1px solid #AAAAAA'>{$signature}</div>
      ",
      ""
    );
  }

  public function sendNotificationForForgotPassword($accountInfo) {
    $domain = $this->adios->websiteRenderer->currentPage['domain'];

    $subject = $this->adios->config["settings"]["web"][$domain]["emails"]['forgot_password_SUBJECT'];
    $body = $this->adios->config["settings"]["web"][$domain]["emails"]['forgot_password_BODY'];
    $signature = $this->adios->config["settings"]["web"][$domain]["emails"]['signature'];

    $passwordRecoveryUrl = (new \Surikata\Plugins\WAI\Customer\ForgotPassword($this->adios->websiteRenderer))
      ->getWebPageUrl(["email" => $accountInfo['email']])
    ;

    $body = str_replace("{% givenName %}", $accountInfo["given_name"], $body);
    $body = str_replace("{% familyName %}", $accountInfo["family_name"], $body);
    $body = str_replace(
      "{% passwordRecoveryUrl %}",
      "{$this->adios->config["settings"]["web"][$domain]["profile"]["rootUrl"]}/{$passwordRecoveryUrl}",
      $body
    );

    $this->adios->sendEmail(
      $accountInfo['email'],
      str_replace("{% email %}", $accountInfo['email'], $subject),
      "
        <div style='font-family:Verdana;font-size:10pt'>
          {$body}
        </div>
        <div style='font-family:Verdana;font-size:10pt;padding-top:10px;margin-top:10px;border-top:1px solid #AAAAAA'>{$signature}</div>
      ",
      ""
    );
  }

}