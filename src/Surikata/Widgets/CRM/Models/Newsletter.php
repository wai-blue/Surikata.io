<?php

namespace ADIOS\Widgets\CRM\Models;

use ADIOS\Widgets\CRM\Exceptions\DuplicateNewsletterEmail;

class Newsletter extends \ADIOS\Core\Model {
  var $sqlName = "newsletter";
  var $urlBase = "CRM/Newsletter";
  var $tableTitle = "Newsletter Subscribers";
  var $formTitleForEditing = "Newsletter new subscribe";
  var $formTitleForInserting = "Newsletter edit subscribe";

  public function init() {

    $this->enumDomains = [];
    foreach (array_keys($this->adios->config['widgets']['Website']['domains']) as $domain) {
      $this->enumDomains = [$domain => $domain];
    }

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
        "enum_values" => $this->enumDomains,
      ],

      "created_at" => [
        "type" => "datetime",
        "title" => $this->translate("Created at"),
        "show_column" => TRUE,
      ],
    ]);
  }

  public function registerForNewsletter($email, $domain = "") {
    if ($domain === "") {
      $domain = $this->enumDomains[array_key_first($this->enumDomains)];
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
      throw new DuplicateNewsletterEmail('Duplicate email');
    }
    return $id;
  }

  // public function install() {
  //   if (!parent::install()) return FALSE;

  //   for ($i = 0; $i < 30; $i++) {
  //     $this->insertRandomRow();
  //   }

  //   return TRUE;
  // }
}