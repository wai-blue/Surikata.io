<?php

namespace ADIOS\Widgets\CRM\Models;

use ADIOS\Widgets\CRM\Exceptions\AlreadyRegisteredForNewsletter;
use ADIOS\Widgets\CRM\Exceptions\EmailIsInvalid;

class Newsletter extends \ADIOS\Core\Widget\Model {
  var $sqlName = "newsletter";
  var $urlBase = "CRM/Newsletter";

  public function init() {

    $this->tableTitle = $this->translate("Newsletter Subscribers");
    $this->formTitleForInserting = $this->translate("Newsletter new subscriber");
    $this->formTitleForEditing = $this->translate("Newsletter edit subscriber");

  }

  public function columns(array $columns = []) {
    return parent::columns([
      "email" => [
        "type" => "varchar",
        "title" => $this->translate("Email"),
        "show_column" => TRUE,
      ],

      "domain" => [
        "type" => "varchar",
        "title" => $this->translate("Domain"),
        "show_column" => TRUE,
        "enum_values" => $this->adios->getEnumValuesForListOfDomains(),
      ],

      "created_at" => [
        "type" => "datetime",
        "title" => $this->translate("Created at"),
        "show_column" => TRUE,
      ],
    ]);
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "domain" => [
        "type" => "index",
        "columns" => ["domain"],
      ],
    ]);
  }

  public function registerForNewsletter($email, $domain = "") {
    if ($domain === "") {
      $domain = reset($this->adios->getEnumValuesForListOfDomains());
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new EmailIsInvalid();
    }

    $newsletter = $this
      ->where('email', '=', $email)
      ->get()
      ->toArray()
    ;

    if (count($newsletter) == 0) {
      $id = $this->insertRow(
        ['email' => $email, 'domain' => $domain, 'created_at' => "SQL:now()"]
      );
    }
    else {
      throw new AlreadyRegisteredForNewsletter();
    }
    return $id;
  }

}