<?php

namespace ADIOS\Widgets\CRM\Models;

class Newsletter extends \ADIOS\Core\Model {
  var $sqlName = "newsletter";
  var $urlBase = "CRM/Newsletter";
  var $tableTitle = "Newsletter Subscribers";
  var $formTitleForEditing = "Newsletter new subscribe";
  var $formTitleForInserting = "Newsletter edit subscribe";

  public function columns(array $columns = []) {
    return parent::columns([
      "email" => [
        "type" => "varchar",
        "title" => $this->translate("Email"),
        "show_column" => TRUE,
      ],

      "created_at" => [
        "type" => "datetime",
        "title" => $this->translate("Created at"),
        "show_column" => TRUE,
      ],
    ]);
  }

  public function registerForNewsletter($email) {
    $newsletter = $this
      ->where('email', '=', $email)
      ->get()
      ->toArray()
    ;

    if (count($newsletter) == 0) {
      $id = $this->insertRow(
        ['email' => $email, 'created_at' => "SQL:now()"]
      );
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