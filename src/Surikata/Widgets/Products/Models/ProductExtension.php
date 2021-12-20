<?php

namespace ADIOS\Widgets\Products\Models;

class ProductExtension extends \ADIOS\Core\Widget\Model {
  var $sqlName = "products_extensions";
  var $urlBase = "Products/{{ id_product }}/Extensions";
  var $tableTitle = "Product extensions";

  public function init() {
    $this->formTitleForInserting = $this->translate("New product extension");
    $this->formTitleForEditing = $this->translate("Product extensions");
  }

  public function columns(array $columns = []) {
    $translatedColumns = [];
    $domainLanguages = $this->adios->config['widgets']['Website']['domainLanguages'];

    foreach ($domainLanguages as $languageIndex => $languageName) {
      $translatedColumns["name_lang_{$languageIndex}"] = [
        "type" => "varchar",
        "title" => $this->translate("Name")." ({$languageName})",
        "show_column" => ($languageIndex == $this->adios->translatedColumnIndex),
        "is_searchable" => ($languageIndex == $this->adios->translatedColumnIndex),
      ];
      $translatedColumns["description_lang_{$languageIndex}"] = [
        "type" => "text",
        "title" => $this->translate("Description")." ({$languageName})",
        "interface" => "formatted_text",
        "show_column" => FALSE,
        "is_searchable" => ($languageIndex == $this->adios->translatedColumnIndex),
      ];
    }

    return parent::columns(array_merge(
      [
        "id_product" => [
          "type" => "lookup",
          "model" => "Widgets/Products/Models/Product",
          "title" => $this->translate("Product"),
          "readonly" => TRUE,
          "show_column" => FALSE,
        ],
      ],
      $translatedColumns,
      [
        "description" => [
          "type" => "text",
          "title" => $this->translate("Description"),
        ],

        "sale_price" => [
          "type" => "float",
          "title" => $this->translate("Price"),
          "unit" => $this->adios->locale->currencySymbol(),
          "show_column" => TRUE,
        ],

        "image" => [
          "type" => "image",
          "title" => $this->translate("Image"),
          "show_column" => TRUE,
          "subdir" => "products",
          "description" => $this->translate("Supported image extensions: jpg, gif, png, jpeg"),
        ],
      ]
    ));
  }

  public function tableParams($params) {
    $params["where"] = "`{$this->table}`.`id_product` = ".(int) $params['id_product'];
    $params['show_search_button'] = FALSE;
    $params['show_controls'] = FALSE;
    $params['show_filter'] = FALSE;
    $params['title'] = " ";
    $params['header'] = $this->translate("Product extensions are not separate products. They can be used to extend the product's detail page to sell special extensions to the product. The visitor can check desired extensions at the product's detail page before adding the product to the shopping cart.");

    return $params;
  }

  public function formParams($data, $params) {
    $params['default_values'] = ['id_product' => (int) $params['id_product']];
    return $params;
  }

}