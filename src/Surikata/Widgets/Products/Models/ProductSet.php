<?php

namespace ADIOS\Widgets\Products\Models;

class ProductSet extends \ADIOS\Core\Model {
  var $sqlName = "products_sets";
  var $urlBase = "Products/Sets";
  var $tableTitle = "Product sets";
  var $formTitleForInserting = "New product set";
  var $formTitleForEditing = "Product Set";
  var $lookupSqlValue = "{%TABLE%}.name_lang_1";

  public function columns(array $columns = []) {
    return parent::columns([
      "name_lang_1" => ["type" => "varchar", "title" => "Product set name, language 1", "show_column" => TRUE],
      "name_lang_2" => ["type" => "varchar", "title" => "Product set name, language 2", "show_column" => FALSE],
      "name_lang_3" => ["type" => "varchar", "title" => "Product set name, language 3", "show_column" => FALSE],
      "name_lang_4" => ["type" => "varchar", "title" => "Product set name, language 4", "show_column" => FALSE],
      "name_lang_5" => ["type" => "varchar", "title" => "Product set name, language 5", "show_column" => FALSE],
      "name_lang_6" => ["type" => "varchar", "title" => "Product set name, language 6", "show_column" => FALSE],
      "name_lang_7" => ["type" => "varchar", "title" => "Product set name, language 7", "show_column" => FALSE],
      "name_lang_8" => ["type" => "varchar", "title" => "Product set name, language 8", "show_column" => FALSE],
      "name_lang_9" => ["type" => "varchar", "title" => "Product set name, language 9", "show_column" => FALSE],
    ]);
  }

  public function tableParams($params) {
    $params['header'] = "
      Product sets are used to group products that you want to sell as a set with a special price
      or discount.
      <br/>
      This list only for managing product set information, like names, prices and/or discount rates.
      If you want to add a product to the set, go to the product catalog, open desired product
      and scroll to tab 'Sets'.
    ";
    return $params;
  }
}