<?php

namespace Surikata\Core\AdminPanel;

class Loader extends \ADIOS\Core\Loader {
  const USER_ROLE_ADMINISTRATOR                              = 1;
  const USER_ROLE_PRODUCT_MANAGER                            = 2;
  const USER_ROLE_SALES                                      = 3;
  const USER_ROLE_ONLINE_MARKETING                           = 4;
  const USER_ROLE_PRODUCT_MANAGER_AND_SALES                  = 5;
  const USER_ROLE_PRODUCT_MANAGER_AND_ONLINE_MARKETING       = 6;
  const USER_ROLE_SALES_AND_ONLINE_MARKETING                 = 7;
  const USER_ROLE_PRODUCT_MANAGER_SALES_AND_ONLINE_MARKETING = 8;

  public $websiteRenderer;

  public function __construct($config, $mode, $websiteRenderer = NULL) {

    $this->websiteRenderer = $websiteRenderer;

    if (is_object($this->websiteRenderer)) {
      $this->websiteRenderer->adminPanel = $this;
    }

    $this->assetsUrlMap["surikata/assets/"] = __DIR__."/../Assets/";

    // parent::__construct
    parent::__construct($config, $mode);

    $this->locale = new \Surikata\Core\AdminPanel\Locale($this);

    // override console to log DB errors
    // 2021-09-09 deprecated. Default ADIOS console is used.
    // $this->console = new \Surikata\Core\AdminPanel\Console($this);

    if (is_object($this->twig)) {
      $this->twig->addFilter(new \Twig\TwigFilter(
        'formatPrice',
        function ($string) {
          return $this->locale->formatPrice($string);
        }
      ));
    }

    if (is_object($this->websiteRenderer)) {
      try {
        $this->websiteRenderer->pages = $this->websiteRenderer->loadPublishedPages();
      } catch (\Illuminate\Database\QueryException $e) {
        // during the installation the SQL table with pages may not exist
      }
    }
  }

  public function onAfterConfigLoaded() {
    $this->translatedColumnIndex = $this->getTranslatedColumnIndexByLanguage();
  }
  
  public function onBeforePluginsLoaded() {
    parent::onBeforePluginsLoaded();
    $this->registerPluginFolder(__DIR__."/../../../Plugins");
  }

  /**
   * Surikata's implementation of ADIOS checkPermissionsForAction.
   * Throws exception when signed user does not have permission for rendering
   * requested action.
   *
   * @throws \ADIOS\Core\NotEnoughPermissionsException
   *
   * @param  mixed $action
   * @param  mixed $params
   * @return void
   */
  public function checkPermissionsForAction($action, $params = NULL) {
    if ($action != "Desktop") {
      if (!$this->hasUserRole(self::USER_ROLE_PRODUCT_MANAGER)) {
        if (strpos($params['model'], "Widgets/Products/Models") !== FALSE) {
          throw new \ADIOS\Core\Exceptions\NotEnoughPermissionsException("You don't have permissions to manage products.");
        }
      }
    }
  }

  public function sendEmail($to, $subject, $bodyHtml, $bodyPlain) {
    $email = new \Surikata\Lib\Email(
      $this->config['smtp_host'],
      $this->config['smtp_port']
    );

    $email
      ->setLogin($this->config['smtp_login'], $this->config['smtp_password'])
      ->setFrom($this->config['smtp_from'])
      ->setSubject($subject)
      ->setHtmlMessage($bodyHtml)
      ->setTextMessage($bodyPlain)
      ->addTo($to)
    ;

    if ($this->config['smtp_protocol'] == 'ssl') {
      $email->setProtocol(\Surikata\Lib\Email::SSL);
    }

    if ($this->config['smtp_protocol'] == 'tls') {
      $email->setProtocol(\Surikata\Lib\Email::TLS);
    }

    $email->send();

  }

  public function setUserRole($role) {
    if (!empty($this->userProfile)) {
      $this->userProfile['id_role'] = $role;
    }
  }

  public function hasUserRole($role) {
    if ($this->userProfile['id_role'] == self::USER_ROLE_ADMINISTRATOR) {
      return TRUE;
    } else {
      switch ($role) {
        case self::USER_ROLE_ADMINISTRATOR:
          return FALSE;
        break;
        case self::USER_ROLE_PRODUCT_MANAGER:
          return in_array($this->userProfile['id_role'], [
            self::USER_ROLE_PRODUCT_MANAGER,
            self::USER_ROLE_PRODUCT_MANAGER_AND_SALES,
            self::USER_ROLE_PRODUCT_MANAGER_AND_ONLINE_MARKETING,
            self::USER_ROLE_PRODUCT_MANAGER_SALES_AND_ONLINE_MARKETING
          ]);
        break;
        case self::USER_ROLE_SALES:
          return in_array($this->userProfile['id_role'], [
            self::USER_ROLE_SALES,
            self::USER_ROLE_PRODUCT_MANAGER_AND_SALES,
            self::USER_ROLE_SALES_AND_ONLINE_MARKETING,
            self::USER_ROLE_PRODUCT_MANAGER_SALES_AND_ONLINE_MARKETING
          ]);
        break;
        case self::USER_ROLE_ONLINE_MARKETING:
          return in_array($this->userProfile['id_role'], [
            self::USER_ROLE_ONLINE_MARKETING,
            self::USER_ROLE_PRODUCT_MANAGER_AND_ONLINE_MARKETING,
            self::USER_ROLE_SALES_AND_ONLINE_MARKETING,
            self::USER_ROLE_PRODUCT_MANAGER_SALES_AND_ONLINE_MARKETING,
          ]);
        break;
        default:
          return FALSE;
        break;
      }
    }
  }

  public function installDefaultUsers() {
    $adminPanelUserModel = new \ADIOS\Core\Models\User($this);
    $adminPanelUserRoleModel = new \ADIOS\Core\Models\UserRole($this);

    // dolezite: poradie musi byt rovnake, ako "const" na zaciatku
    $adminPanelUserRoleModel->insertRow(["name" => "Administrator"]);
    $adminPanelUserRoleModel->insertRow(["name" => "Product manager"]);
    $adminPanelUserRoleModel->insertRow(["name" => "Sales"]);
    $adminPanelUserRoleModel->insertRow(["name" => "Online marketing"]);
    $adminPanelUserRoleModel->insertRow(["name" => "Product manager + Sales"]);
    $adminPanelUserRoleModel->insertRow(["name" => "Product manager + Online marketing"]);
    $adminPanelUserRoleModel->insertRow(["name" => "Sales + Online marketing"]);
    $adminPanelUserRoleModel->insertRow(["name" => "Product manager + Sales + Online marketing"]);

    $adminPanelUserModel->insertRow([
      "name" => "Administrator",
      "login" => "administrator",
      "password" => "administrator",
      "password_1" => "administrator",
      "password_2" => "administrator",
      "active" => 1,
      "surname" => "Default",
      "id_role" => self::USER_ROLE_ADMINISTRATOR,
    ]);
    $adminPanelUserModel->insertRow([
      "name" => "Product Manager",
      "login" => "product.manager",
      "password" => "product.manager",
      "password_1" => "product.manager",
      "password_2" => "product.manager",
      "active" => 1,
      "surname" => "Default",
      "id_role" => self::USER_ROLE_PRODUCT_MANAGER,
    ]);
    $adminPanelUserModel->insertRow([
      "name" => "Sales",
      "login" => "sales",
      "password" => "sales",
      "password_1" => "sales",
      "password_2" => "sales",
      "active" => 1,
      "surname" => "Default",
      "id_role" => self::USER_ROLE_SALES,
    ]);
    $adminPanelUserModel->insertRow([
      "name" => "Online marketing",
      "login" => "online.marketing",
      "password" => "online.marketing",
      "password_1" => "online.marketing",
      "password_2" => "online.marketing",
      "active" => 1,
      "surname" => "Default",
      "id_role" => self::USER_ROLE_ONLINE_MARKETING,
    ]);
  }
  
  /**
   * Creates required folder in the project's root dir
   *
   * @throws \Exception When failed to create at least one folder.
   *
   * @return void
   */
  public function createMissingFolder($folder) {
    if (is_string($folder) && !is_dir($folder)) {
      if (!mkdir($folder, 0755, TRUE)) {
        throw new \Exception('Wrong permissions to create directory: "' . $folder. '"');
      }
    }
  }
  
  /**
   * Creates required folders in the project if they are missing
   *
   * @throws \Exception When failed to create at least one folder.
   *
   * @return void
   */
  public function createMissingFolders() {
    $this->createMissingFolder(LOG_DIR);
    $this->createMissingFolder(DATA_DIR);
    $this->createMissingFolder(CACHE_DIR);
    $this->createMissingFolder(TWIG_CACHE_DIR);
    $this->createMissingFolder(UPLOADED_FILES_DIR);
    $this->createMissingFolder(UPLOADED_FILES_DIR."/csv-import");
  }
  
  /**
   * Checks whether required folders have proper permissions
   *
   * @throws \Exception When failed to create at least one folder.
   *
   * @return void
   */
  public function checkFoldersPermissions() {
    foreach (get_defined_constants(true)['user'] as $const => $value) {
      if (
        '_DIR' === substr($const, -4) 
        && is_string($value)
        && !empty($value)
        && is_dir($value)
      ) {
        if (substr(sprintf('%o', fileperms($value)), -4) !== '0775' and !chmod($value, 0775)) {
          throw new \Exception('Wrong access permissions for folder: "' . $value. '"');
        }
      }
    }
  }

  public function getAvailableDomains() {
    return $this->config['widgets']['Website']['domains'] ?: [];
  }

  public function getDomainInfo($domainName) {
    foreach ($this->getAvailableDomains() as $domainInfo) {
      if ($domainInfo['name'] == $domainName) {
        return $domainInfo;
      }
    }

    return NULL;
  }

  public function getPluginSettings($pluginName) {
    $settings = $this->config["settings"]["plugins"];
    foreach (explode("/", $pluginName) as $tmpPath) {
      $settings = $settings[$tmpPath];
    }
    return $settings;
  }

  public function getTranslatedColumnIndexByLanguage(string $languageShort = "") {
    if (empty($languageShort)) {
      $languageShort = $this->config["language"];
    }

    $translatedColumnIndex = 1;
    $languages = $this->config['widgets']['Website']['domainLanguagesShort'];
    foreach ($languages as $tmpColumnIndex => $tmpLanguageShort) {
      if ($languageShort == $tmpLanguageShort) {
        $translatedColumnIndex = $tmpColumnIndex;
      }
    }

    return $translatedColumnIndex;
  }
}