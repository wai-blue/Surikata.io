<?php

namespace ADIOS\Widgets\Products\Models;

class Product extends \ADIOS\Core\Model {
  const PRICE_CALCULATION_METHOD_CUSTOM_PRICE  = 1;
  const PRICE_CALCULATION_METHOD_PRICE_LIST    = 2;
  const PRICE_CALCULATION_METHOD_PLUGIN        = 3;
  
  var $sqlName = "products";
  var $lookupSqlValue = "concat({%TABLE%}.number, ' ', {%TABLE%}.name_lang_1)";
  var $urlBase = "Products";

  public function init() {
    $this->tableTitle = $this->translate("Products");

    $this->enumValuesSalePriceCalculationMethod = [
      self::PRICE_CALCULATION_METHOD_CUSTOM_PRICE => $this->translate("Custom price: You can enter your custom price"),
      self::PRICE_CALCULATION_METHOD_PRICE_LIST => $this->translate("Price list: Margins and discounts from price list will be applied"),
      self::PRICE_CALCULATION_METHOD_PLUGIN => $this->translate("Plugin: Plugin will be called to calculate price"),
    ];
  }

  public function columns(array $columns = []) {
    $translatedColumns = [];
    $domainLanguages = $this->adios->config['widgets']['Website']['domainLanguages'];

    foreach ($domainLanguages as $languageIndex => $languageName) {
      $translatedColumns["name_lang_{$languageIndex}"] = [
        "type" => "varchar",
        "title" => $this->translate("Name")." (".$this->translate($languageName).")",
        "show_column" => ($languageIndex == 1),
        "is_searchable" => ($languageIndex == 1),
      ];
      $translatedColumns["brief_lang_{$languageIndex}"] = [
        "type" => "varchar",
        "title" => $this->translate("Short description")." ({$languageName})",
        "show_column" => FALSE,
        "is_searchable" => ($languageIndex == 1),
      ];
      $translatedColumns["description_lang_{$languageIndex}"] = [
        "type" => "text",
        "title" => $this->translate("Description")." ({$languageName})",
        "interface" => "formatted_text",
        "show_column" => FALSE,
        "is_searchable" => ($languageIndex == 1),
      ];
      $translatedColumns["gift_lang_{$languageIndex}"] = [
        "type" => "varchar",
        "title" => $this->translate("Gift")." ({$languageName})",
        "show_column" => FALSE,
        "is_searchable" => ($languageIndex == 1),
      ];
    }

    return parent::columns(array_merge(
      $translatedColumns,
      [
        "number" => [
          "type" => "varchar",
          "title" => $this->translate("Product number"),
          "required" => TRUE,
          "show_column" => TRUE,
        ],

        "ean" => [
          "type" => "varchar",
          "title" => $this->translate("EAN"),
        ],

        "weight" => [
          "type" => "float",
          "title" => $this->translate("Weight (per unit)"),
          "unit" => "g",
          "show_column" => FALSE,
        ],

        "price_calculation_method" => [
          "type" => "int",
          "title" => $this->translate("Which price will be displayed on the web?"),
          "enum_values" => $this->enumValuesSalePriceCalculationMethod,
        ],

        "sale_price_custom" => [
          "type" => "float",
          "sql_data_type" => "decimal",
          "decimals" => 4,
          "title" => $this->translate("Custom sale price"),
          "unit" => $this->adios->locale->currencySymbol(),
          "show_column" => FALSE,
        ],

        "full_price_custom" => [
          "type" => "float",
          "sql_data_type" => "decimal",
          "decimals" => 4,
          "title" => $this->translate("Custom full price"),
          "unit" => $this->adios->locale->currencySymbol(),
          "show_column" => FALSE,
        ],

        "sale_price_cached" => [
          "type" => "float",
          "title" => $this->translate("Calculated sale price"),
          "description" => $this->translate("WARNING: Final sale price of the product will be updated after save."),
          "readonly" => TRUE,
          "show_column" => FALSE,
        ],

        "full_price_cached" => [
          "type" => "float",
          "title" => $this->translate("Calculated sale price"),
          "description" => $this->translate("WARNING: Final sale price of the product will be updated after save."),
          "readonly" => TRUE,
          "show_column" => FALSE,
        ],

        "id_delivery_unit" => [
          "type" => "lookup",
          "input_style" => "select",
          "title" => $this->translate("Delivery unit"),
          "model" => "Widgets/Settings/Models/Unit",
        ],

        "stock_quantity" => [
          "type" => "float",
          "title" => $this->translate("Stock quantity"),
          "Description" => $this->translate("Number of units of product available on stock and ready for immediate delivery."),
          "show_column" => TRUE,
        ],

        "delivery_day" => [
          "type" => "int",
          "title" => $this->translate("Delivery day"),
          "Description" => $this->translate("Duration of delivery in days. '1' means that the product will be delivered in the next day after the order."),
        ],

        "delivery_time" => [
          "type" => "time",
          "title" => $this->translate("Delivery time"),
          "Description" => $this->translate("Date and time of the delivery if the product is ordered before the order deadline. Leave empty for default value."),
        ],

        "order_deadline" => [
          "type" => "time",
          "title" => $this->translate("Order deadline"),
          "Description" => $this->translate("Date and time of the delivery if the product is ordered before the order deadline. Leave empty for default value."),
        ],

        "extended_warranty" => [
          "type" => "int",
          "title" => $this->translate("Extended warranty"),
          "unit" => $this->translate("month(s)"),
        ],

        "vat_percent" => [
          "type" => "int",
          "title" => $this->translate("VAT"),
          "unit" => "%",
          "show_column" => TRUE,
        ],

        "id_category" => [
          "type" => "lookup",
          "model" => "Widgets/Products/Models/ProductCategory",
          "title" => $this->translate("Main product category"),
          "show_column" => TRUE,
        ],

        "id_brand" => [
          "type" => "lookup",
          "model" => $this->translate("Widgets/Products/Models/Brand"),
          "title" => $this->translate("Brand"),
          "show_column" => TRUE,
        ],

        "image" => [
          'type' => 'image',
          'title' => $this->translate("Main image"),
          'show_column' => TRUE,
          "subdir" => "products"
        ],

        "product_info" => [
          "type" => "file",
          "subdir" => "produkty/info",
          "title" => $this->translate("Product information PDF"),
        ],

        "is_on_sale" => [
          "type" => "boolean",
          "title" => $this->translate("On sale product"),
          "show_column" => TRUE,
        ],

        "is_sale_out" => [
          "type" => "boolean",
          "title" => $this->translate("Sale out"),
          "show_column" => TRUE,
        ],

        "is_new" => [
          "type" => "boolean",
          "title" => $this->translate("New product"),
          "show_column" => TRUE,
        ],

        "is_recommended" => [
          "type" => "boolean",
          "title" => $this->translate("Recommended product"),
          "show_column" => TRUE,
        ],

        "is_top" => [
          "type" => "boolean",
          "title" => $this->translate("Top product"),
          "show_column" => TRUE,
        ],

        "is_used" => [
          "type" => "boolean",
          "title" => $this->translate("Used product"),
          "show_column" => TRUE,
        ],

        "is_unpacked" => [
          "type" => "boolean",
          "title" => $this->translate("Unpacked product"),
          "show_column" => TRUE,
        ],

        "currently_viewed" => [
          "type" => "int",
          "title" => $this->translate("Currently viewed"),
        ],

        "count_sold" => [
          "type" => "int",
          "title" => $this->translate("Count of sold products"),
        ],

        "last_buy" => [
          "type" => "datetime",
          "title" => $this->translate("Time of last buy"),
        ],

        "end_of_sale" => [
          "type" => "date",
          "title" => $this->translate("Time of end of sale"),
        ],

        "order_index" => [
          "type" => "int",
          "title" => $this->translate("Order index"),
        ],

        "is_hidden" => [
          "type" => "boolean",
          "title" => $this->translate("Hide from list"),
          "show_column" => TRUE,
        ],

      ]
    ));
  }

  public function indexes(array $indexes = []) {
    return parent::indexes([
      "number" => [
        "type" => "unique",
        "columns" => ["number"],
      ],
    ]);
  }

  public function upgrades() : array {
    return [
      0 => [], // upgrade to version 0 is the same as installation
      1 => [
        "alter table `{$this->gtp}_products` add column `order_index` int(8) default 0 after `end_of_sale`",
      ],
      2 => [
        "alter table `{$this->gtp}_products` add column `is_hidden` boolean after `order_index`",
      ],
    ];
  }

  public function unit() {
    return $this->hasOne(\ADIOS\Widgets\Settings\Models\Unit::class, "id", "id_delivery_unit");
  }

  // Eloquent relationships
  public function gallery() {
    return $this->hasMany(\ADIOS\Widgets\Products\Models\ProductGallery::class, 'id_product');
  }

  public function extensions() {
    return $this->hasMany(\ADIOS\Widgets\Products\Models\ProductExtension::class, 'id_product');
  }

  public function priceList() {
    return $this->hasOne(\ADIOS\Widgets\Products\Models\ProductPrice::class, 'id_product');
  }

  public function priceListDiscounts() {
    return $this->hasMany(\ADIOS\Widgets\Products\Models\ProductDiscount::class, 'id_product');
  }

  public function priceListDiscountsForCategory() {
    return $this->hasMany(\ADIOS\Widgets\Products\Models\ProductDiscount::class, 'id_product_category', 'id_category');
  }

  public function priceListDiscountsForBrand() {
    return $this->hasMany(\ADIOS\Widgets\Products\Models\ProductDiscount::class, 'id_brand', 'id_brand');
  }

  public function priceListDiscountsForSupplier() {
    return $this->hasMany(\ADIOS\Widgets\Products\Models\ProductDiscount::class, 'id_supplier', 'id_supplier');
  }

  public function priceListMargins() {
    return $this->hasMany(\ADIOS\Widgets\Products\Models\ProductMargin::class, 'id_product');
  }

  public function priceListMarginsForCategory() {
    return $this->hasMany(\ADIOS\Widgets\Products\Models\ProductMargin::class, 'id_product_category', 'id_category');
  }

  public function priceListMarginsForBrand() {
    return $this->hasMany(\ADIOS\Widgets\Products\Models\ProductMargin::class, 'id_brand', 'id_brand');
  }

  public function priceListMarginsForSupplier() {
    return $this->hasMany(\ADIOS\Widgets\Products\Models\ProductMargin::class, 'id_supplier', 'id_supplier');
  }

  public function brand() {
    return $this->belongsTo(\ADIOS\Widgets\Products\Models\Brand::class, 'id_brand');
  }

  public function supplier() {
    return $this->belongsTo(\ADIOS\Widgets\Products\Models\Supplier::class, 'id_supplier');
  }

  public function features() {
    return $this
      ->belongsToMany(
        \ADIOS\Widgets\Products\Models\ProductFeature::class,
        GTP."_products_features_assignment",
        'id_product',
        'id_feature'
      )
      ->withPivot('value_number', 'value_text', 'value_boolean')
    ;
  }

  public function accessories() {
    return $this
      ->belongsToMany(
        \ADIOS\Widgets\Products\Models\Product::class,
        GTP."_products_accessories",
        'id_product',
        'id_accessory'
      )
    ;
  }

  public function related() {
    return $this
      ->belongsToMany(
        \ADIOS\Widgets\Products\Models\Product::class,
        GTP."_products_related",
        'id_product',
        'id_related'
      )
    ;
  }

  public function services() {
    return $this
      ->belongsToMany(
        \ADIOS\Widgets\Products\Models\Service::class,
        GTP."_products_services_assignment",
        'id_product',
        'id_service'
      )
    ;
  }

  // routing
  public function routing(array $routing = []) {
    return parent::routing([
      '/^Products\/(All|New|OnSale|SellOut|Recommended|Used|Unpacked)$/' => [
        "action" => "UI/Table",
        "params" => [
          "model" => "Widgets/Products/Models/Product",
          "filter_type" => '$1',
        ]
      ],
    ]);
  }

  ////////////////////////////////////////////////////////////////
  // ADIOS UI METHODS

  public function tableParams($params) {
    $params['show_search_button'] = TRUE;

    switch ($params['filter_type']) {
      case "All":
        $params["title"] = $this->translate("All products");
      break;
      case "New":
        $params["title"] = $this->translate("Products - News");
        $params['where'] = $this->getFullTableSQLName().".is_new = 1";
      break;
      case "OnSale":
        $params["title"] = $this->translate("Products - On sale");
        $params['where'] = $this->getFullTableSQLName().".is_on_sale = 1";
      break;
      case "SellOut":
        $params["title"] = $this->translate("Products - Sale out");
        $params['where'] = $this->getFullTableSQLName().".is_sale_out = 1";
      break;
      case "Recommended":
        $params["title"] = $this->translate("Products - Recommended");
        $params['where'] = $this->getFullTableSQLName().".is_recommended = 1";
      break;
      case "Used":
        $params["title"] = $this->translate("Products - Used");
        $params['where'] = $this->getFullTableSQLName().".is_used = 1";
      break;
      case "Unpacked":
        $params["title"] = $this->translate("Products - Unpacked");
        $params['where'] = $this->getFullTableSQLName().".is_unpacked = 1";
      break;
    }
    return $params;
  }

  // $initiatingModel = model formulara, v ramci ktoreho je lookup generovany
  // $initiatingColumn = nazov stlpca, z ktoreho je lookup generovany
  // $formData = aktualne data formulara
  public function lookupSqlWhere($initiatingModel = NULL, $initiatingColumn = NULL, $formData = [], $params = []) {
    if (
      $initiatingModel == "Widgets/Products/Models/ProductAccessory"
      && $initiatingColumn == "id_accessory"
    ) {
      return "`{$this->table}`.id != ".(int) $formData['id_product'];
    } else {
      return "TRUE";
    }
  }

  public function formParams($data, $params) {
    if ($data['id'] <= 0) {
      $params['title'] = $this->translate("New product");
    } else {
      $params['title'] = "{$data['number']} {$data['name_lang_1']}";
      $params['subtitle'] = $this->translate("Product");
    }

    $params["columns"]["table_categories_assignment"] = [
      "type" => "table",
      "table" => $this->adios->getModel("Widgets/Products/Models/ProductCategoryAssignment")->table,
      "input_style" => "autocomplete",
      "title" => $this->translate("Zaradenie do vedľajších kategórií"),
      "order" => "name_lang_1 asc"
    ];

    $tabTranslations = [];
    $domainAssignmentValues = [];

    $domains = $this->adios->config['widgets']['Website']['domains'];
    $domainLanguages = $this->adios->config['widgets']['Website']['domainLanguages'];

    $i = 1;
    foreach ($domainLanguages as $languageIndex => $languageName) {
      if ($i > 1) {
        $tabTranslations[] = ["html" => "<b style='color:var(--cl-main)'>".hsc($languageName)."</b>"];
        $tabTranslations[] = "name_lang_{$languageIndex}";
        $tabTranslations[] = "brief_lang_{$languageIndex}";
        $tabTranslations[] = "description_lang_{$languageIndex}";
        $tabTranslations[] = "gift_lang_{$languageIndex}";
      }
      $i++;
    }

    foreach ($domains as $domain => $domainInfo) {
      $domainAssignmentValues[$domain] = $domainInfo["name"];
    }

    if (count($tabTranslations) == 0) {
      $tabTranslations[] = ["html" => "No translations available."];
    }

    $params["columns"]["price_calculation_method"]["onchange"] = "{$params['uid']}_price_calculation_method_onchange();";

    $params["javascript"] = "
      function {$params['uid']}_price_calculation_method_onchange(el) {
        let input = $('#{$params['uid']}_price_calculation_method');
        let value = input.val();
        let thisRow = $(input).closest('.subrow');
        let rowFullPricePriceList = thisRow.next('.subrow');
        let rowSalePricePriceList = rowFullPricePriceList.next('.subrow');
        let rowPriceListOpen = rowSalePricePriceList.next('.subrow');
        let rowFullPricePlugin = rowPriceListOpen.next('.subrow');
        let rowSalePricePlugin = rowFullPricePlugin.next('.subrow');
        let rowFullPriceCustom = rowSalePricePlugin.next('.subrow');
        let rowSalePriceCustom = rowFullPriceCustom.next('.subrow');

        rowFullPricePriceList.hide();
        rowSalePricePriceList.hide();
        rowPriceListOpen.hide();
        rowFullPricePlugin.hide();
        rowSalePricePlugin.hide();
        rowFullPriceCustom.hide();
        rowSalePriceCustom.hide();

        if (value == ".self::PRICE_CALCULATION_METHOD_PRICE_LIST.") {
          rowFullPricePriceList.show();
          rowSalePricePriceList.show();
          rowPriceListOpen.show();
        } else if (value == ".self::PRICE_CALCULATION_METHOD_PLUGIN.") {
          rowFullPricePlugin.show();
          rowSalePricePlugin.show();
        } else if (value == ".self::PRICE_CALCULATION_METHOD_CUSTOM_PRICE.") {
          rowFullPriceCustom.show();
          rowSalePriceCustom.show();
        }
      }

      {$params['uid']}_price_calculation_method_onchange();
    ";

    $priceInfoPriceList = $this->getPriceInfoForSingleProduct($data, self::PRICE_CALCULATION_METHOD_PRICE_LIST, FALSE);
    $priceInfoPlugin = $this->getPriceInfoForSingleProduct($data, self::PRICE_CALCULATION_METHOD_PLUGIN, FALSE);

    $templateTabs = [
      $this->translate("General") => [
        "number",
        "ean",
        "name_lang_1",
        "brief_lang_1",
        "description_lang_1",
        "vat_percent",
        "weight",
        "id_supplier",
        "id_brand",
        "product_info",
        "extended_warranty",
      ],
      $this->translate("Price") => [
        "price_calculation_method",
        [
          "title" => $this->translate("Full price calculated from price list"),
          "input" => "
            <input
              type='text'
              class='adios ui Input ui_input_type_float'
              disabled
              value='".number_format($priceInfoPriceList["fullPrice"], 4, ".", " ")."'
            />
            ".$this->adios->locale->currencySymbol()."
          ",
        ],
        [
          "title" => $this->translate("Sale price calculated from price list"),
          "input" => "
            <input
              type='text'
              class='adios ui Input ui_input_type_float'
              disabled
              value='".number_format($priceInfoPriceList["salePrice"], 4, ".", " ")."'
            />
            ".$this->adios->locale->currencySymbol()."
          ",
        ],
        [
          "html" => "
            <a
              href='javascript:void(0)'
              class='btn btn-icon-split btn-light'
              style='margin-top:1em;'
              onclick='window_render(\"Products/{$data['id']}/Prices\");'
            >
              <span class=\"icon\"><i class=\"fas fa-euro-sign\"></i></span>
              <span class=\"text\">Open price list</span>
            </a>
          ",
        ],
        [
          "title" => $this->translate("Full price calculated by plugin"),
          "input" => "
            <input
              type='text'
              class='adios ui Input ui_input_type_float'
              disabled
              value='".number_format($priceInfoPlugin["fullPrice"], 4, ".", " ")."'
            />
            ".$this->adios->locale->currencySymbol()."
          ",
        ],
        [
          "title" => $this->translate("Sale price calculated by plugin"),
          "input" => "
            <input
              type='text'
              class='adios ui Input ui_input_type_float'
              disabled
              value='".number_format($priceInfoPlugin["salePrice"], 4, ".", " ")."'
            />
            ".$this->adios->locale->currencySymbol()."
          ",
        ],
        "full_price_custom",
        "sale_price_custom",
        // "full_price_cached",
        // "sale_price_calculated",
      ],
      $this->translate("Stock & Delivery") => [
        "stock_quantity",
        "id_delivery_unit",
        "delivery_day",
        "delivery_time",
        "order_deadline",
      ],
      $this->translate("Visibility") => [
        [
          "title" => $this->translate("Domain visibility"),
          "description" => $this->translate("If no domain is selected, product will be visible on all domains."),
          "input" => (new \ADIOS\Core\UI\Input\CheckboxField(
            $this->adios,
            "{$params['uid']}_ProductDomainAssignment",
            [
              "model" => "Widgets/Products/Models/ProductDomainAssignment",
              "key_column" => "id_product",
              "key_value" => $data['id'],
              "value_column" => "domain",
              "values" => $domainAssignmentValues,
              "columns" => 2,
            ]
          ))->render(),
        ],
        "end_of_sale",
        "order_index",
        "is_hidden",
      ],
      $this->translate("Translations") => $tabTranslations,
      $this->translate("Categories") => [
        "id_category",
        [
          "title" => $this->translate("Additional categories"),
          "input" => (new \ADIOS\Core\UI\Input\ManyToMany(
            $this->adios,
            "{$params['uid']}_ProductCategoryAssignment",
            [
              "model" => "Widgets/Products/Models/ProductCategoryAssignment",
              "relation" => ["id_product", "id_category"],
              "constraints" => [
                "id_product" => $data['id'],
              ],
              "order" => "name_lang_1 asc"
            ]
          ))->render(),
        ],
      ],
      $this->translate("Filters") => [
        "is_on_sale",
        "is_sale_out",
        "is_new",
        "is_recommended",
        "is_top",
        "is_used",
        "is_unpacked",
      ],
    ];

    if ($data['id'] > 0) {
      $templateTabs[$this->translate("Services")] = [
        [
          "title" => "",
          "input" => (new \ADIOS\Core\UI\Input\ManyToMany(
            $this->adios,
            "{$params['uid']}_ProductServiceAssignment",
            [
              "formUid" => $params['uid'],
              "model" => "Widgets/Products/Models/ProductServiceAssignment",
              "relation" => ["id_product", "id_service"],
              "constraints" => [
                "id_product" => $data['id'],
              ],
              "order" => "name_lang_1 asc"
            ]
          ))->render(),
        ],
      ];
      $templateTabs[$this->translate("Gallery")] = [
        "action" => "UI/Cards",
        "params" => [
          "model"    => "Widgets/Products/Models/ProductGallery",
          "id_product" => $data['id'],
        ]
      ];
      $templateTabs[$this->translate("Features")] = [
        "action" => "UI/Table",
        "params" => [
          "model"    => "Widgets/Products/Models/ProductFeatureAssignment",
          "id_product" => $data['id'],
        ]
      ];
      $templateTabs[$this->translate("Extensions")] = [
        "action" => "UI/Table",
        "params" => [
          "model"    => "Widgets/Products/Models/ProductExtension",
          "id_product" => $data['id'],
        ]
      ];
      $templateTabs[$this->translate("Accessories")] = [
        "action" => "UI/Table",
        "params" => [
          "model"    => "Widgets/Products/Models/ProductAccessory",
          "id_product" => $data['id'],
        ]
      ];
      $templateTabs[$this->translate("Related")] = [
        "action" => "UI/Table",
        "params" => [
          "model"    => "Widgets/Products/Models/ProductRelated",
          "id_product" => $data['id'],
        ]
      ];
    }

    $params["template"] = [
      "columns" => [
        [
          "class" => "col-md-9 pl-0",
          "tabs" => $templateTabs,
        ],
        [
          "class" => "col-md-3 pr-0",
          "rows" => [
            "image",
          ],
        ],
      ],
    ];

    $this->adios->dispatchEventToPlugins("onProductDetailSidebarButtons", [
      "model" => $this,
      "params" => $params,
      "data" => $data,
    ]);

    return parent::formParams($data, $params);
  }

  /**
   * Add product to Order, return product or false
   * @param $productId
   * @return array|boolean
   */
  public function addProductToOrder($productId) {
    $product = $this->getById($productId);
    if ($product["id_delivery_unit"] > 0) {
      $delivery_unit = (new \ADIOS\Widgets\Settings\Models\Unit($this->adios))
        ->getById($product["id_delivery_unit"]);
      $product["DELIVERY_UNIT"] = $delivery_unit;
    }
    return $product;
  }

  ////////////////////////////////////////////////////////////////
  // HOOKS AND CALLBACKS

  public function recalculatePriceForSingleProduct($productOrIdProduct) {
    if (is_numeric($productOrIdProduct)) {
      $idProduct = $productOrIdProduct;
    } else {
      $idProduct = $productOrIdProduct['id'];
    }

    $tmp = $this->getPriceInfoForSingleProduct($productOrIdProduct, NULL, FALSE);

    $this
      ->where('id', $idProduct)
      ->update([
        "full_price_cached" => $tmp['fullPrice'],
        "sale_price_cached" => $tmp['salePrice'],
      ]
    );
  }

  public function recalculateAllPrices() {
    $this->beginTransaction();
    $products = $this->getForDetail()->get()->toArray();
    foreach ($products as $product) {
      $this->recalculatePriceForSingleProduct($product);
    }
    $this->commit();
  }

  public function onAfterSave($data, $returnValue) {
    $this->adios->widgets['Website']->rebuildSitemapForAllDomains();
    
    if (!empty($data['ProductCategoryAssignment'])) {
      $categories = @json_decode($data['ProductCategoryAssignment'], TRUE);
      if (is_array($categories)) {
        $model = $this->adios->getModel("Widgets/Products/Models/ProductCategoryAssignment");

        foreach ($categories as $idCategory) {
          $model->assign($data['id'], $idCategory);
        }

        $model->deleteUnassigned($data['id'], $categories);
      }
    }

    if (!empty($data['ProductDomainAssignment'])) {
      $domains = @json_decode($data['ProductDomainAssignment'], TRUE);
      if (is_array($domains)) {
        $model = $this->adios->getModel("Widgets/Products/Models/ProductDomainAssignment");

        foreach ($domains as $domain) {
          $model->assign($data['id'], $domain);
        }

        $model->deleteUnassigned($data['id'], $domains);
      }
    }

    if (!empty($data['ProductServiceAssignment'])) {
      $services = @json_decode($data['ProductServiceAssignment'], TRUE);
      if (is_array($services)) {
        $model = $this->adios->getModel("Widgets/Products/Models/ProductServiceAssignment");

        foreach ($services as $idService) {
          $model->assign($data['id'], $idService);
        }

        $model->deleteUnassigned($data['id'], $services);
      }
    }

    $this->recalculatePriceForSingleProduct($data['id']);

    return parent::onAfterSave($data, $returnValue);
  }

  ////////////////////////////////////////////////////////////////
  // GETTERS

  public function getForPriceInfo() {
    return $this
      ->with('priceList')
      ->with('priceListMargins')
      ->with('priceListMarginsForCategory')
      ->with('priceListMarginsForBrand')
      ->with('priceListMarginsForSupplier')
      ->with('priceListDiscounts')
      ->with('priceListDiscountsForCategory')
      ->with('priceListDiscountsForBrand')
      ->with('priceListDiscountsForSupplier')
    ;
  }

  public function getForDetail() {
    return $this->getForPriceInfo()
      ->with('gallery')
      ->with('extensions')
      ->with('brand')
      ->with('supplier')
      ->with('features')
      ->with('related')
      ->with('accessories')
      ->with('services')
    ;
  }

  public function getById($id) {
    return $this->getDetailedInfoForSingleProduct($id);
  }

  ////////////////////////////////////////////////////////////////
  // METHODS FOR DATA PROCESSING OF A SINGLE PRODUCT

  public function getDetailedInfoForSingleProduct($idProduct) {
    $product = $this->unifyProductInformationForSingleProduct(
      reset($this->getForDetail()->where('id', $idProduct)->get()->toArray())
    );

    $product['PRICE'] = $this->getPriceInfoForSingleProduct($product);

    return $product;

  }

  public function unifyProductInformationForSingleProduct($product) {

    $keyConversionTable = [
      "brand" => "BRAND",
      "gallery" => "GALLERY",
      "extensions" => "EXTENSIONS",
      "supplier" => "SUPPLIER",
      "features" => "FEATURES",
      "related" => "RELATED",
      "accessories" => "ACCESSORIES",
      "services" => "SERVICES",
      "price_list" => "PRICELIST",
    ];

    foreach ($keyConversionTable as $from => $to) {
      if (isset($product[$from])) {
        $product[$to] = $product[$from];
        unset($product[$from]);
      }
    }

    if (is_array($product["PRICELIST"])) {

      if (!empty($product["price_list_margins"])) {
        $product["PRICELIST"]["MARGINS"] = $product["price_list_margins"];
        unset($product["price_list_margins"]);
      }
      if (!empty($product["price_list_margins_for_category"])) {
        $product["PRICELIST"]["MARGINS"]["CATEGORY"] = $product["price_list_margins_for_category"];
        unset($product["price_list_margins_for_category"]);
      }
      if (!empty($product["price_list_margins_for_brand"])) {
        $product["PRICELIST"]["MARGINS"]["BRAND"] = $product["price_list_margins_for_brand"];
        unset($product["price_list_margins_for_brand"]);
      }
      if (!empty($product["price_list_margins_for_supplier"])) {
        $product["PRICELIST"]["MARGINS"]["SUPPLIER"] = $product["price_list_margins_for_supplier"];
        unset($product["price_list_margins_for_supplier"]);
      }

      if (!empty($product["price_list_discounts"])) {
        $product["PRICELIST"]["DISCOUNTS"] = $product["price_list_discounts"];
        unset($product["price_list_discounts"]);
      }
      if (!empty($product["price_list_discounts_for_category"])) {
        $product["PRICELIST"]["DISCOUNTS"]["CATEGORY"] = $product["price_list_discounts_for_category"];
        unset($product["price_list_discounts_for_category"]);
      }
      if (!empty($product["price_list_discounts_for_brand"])) {
        $product["PRICELIST"]["DISCOUNTS"]["BRAND"] = $product["price_list_discounts_for_brand"];
        unset($product["price_list_discounts_for_brand"]);
      }
      if (!empty($product["price_list_discounts_for_supplier"])) {
        $product["PRICELIST"]["DISCOUNTS"]["SUPPLIER"] = $product["price_list_discounts_for_supplier"];
        unset($product["price_list_discounts_for_supplier"]);
      }
    }


    return $product;
  }

  public function getPriceInfoForSingleProduct($productOrIdProduct, $calculationMethod = NULL, $useCache = TRUE) {
    $priceInfo = [];

    if (is_array($productOrIdProduct)) {
      $product = $productOrIdProduct;
      $idProduct = $productOrIdProduct['id'];
    } else if (is_numeric($productOrIdProduct)) {
      $idProduct = $productOrIdProduct;
      $product = $this->getById($idProduct);
    } else {
      return NULL;
    }

    if ($calculationMethod === NULL) {
      $calculationMethod = (int) $product['price_calculation_method'];
    }

    // SALE PRICE && FULL PRICE

    switch ($calculationMethod) {
      case self::PRICE_CALCULATION_METHOD_PRICE_LIST:
        if ($useCache) {
          $priceInfo = [
            "salePrice" => $product['sale_price_cached'],
            "fullPrice" => $product['full_price_cached'],
          ];
        } else {
          $purchasePrice = (float) $product['PRICELIST']['purchase_price'] ?? 0;
          $recommendedPrice = (float) $product['PRICELIST']['recommended_price'] ?? 0;

          $salePrice = $purchasePrice;

          // applying margins

          foreach ($product['PRICELIST']['MARGINS'] as $margin) {
            $salePrice = $salePrice * (1 + $margin['margin'] / 100);
          }
          foreach ($product['PRICELIST']['MARGINS']['CATEGORY'] as $margin) {
            $salePrice = $salePrice * (1 + $margin['margin'] / 100);
          }
          foreach ($product['PRICELIST']['MARGINS']['BRAND'] as $margin) {
            $salePrice = $salePrice * (1 + $margin['margin'] / 100);
          }
          foreach ($product['PRICELIST']['MARGINS']['SUPPLIER'] as $margin) {
            $salePrice = $salePrice * (1 + $margin['margin'] / 100);
          }

          $priceWithoutDiscounts = $salePrice;

          // applying discounts

          foreach ($product['PRICELIST']['DISCOUNTS'] as $discount) {
            $salePrice = $salePrice * (1 - $discount['discount_percentage'] / 100);
          }
          foreach ($product['PRICELIST']['DISCOUNTS']['CATEGORY'] as $discount) {
            $salePrice = $salePrice * (1 - $discount['discount_percentage'] / 100);
          }
          foreach ($product['PRICELIST']['DISCOUNTS']['BRAND'] as $discount) {
            $salePrice = $salePrice * (1 - $discount['discount_percentage'] / 100);
          }
          foreach ($product['PRICELIST']['DISCOUNTS']['SUPPLIER'] as $discount) {
            $salePrice = $salePrice * (1 - $discount['discount_percentage'] / 100);
          }

          $priceInfo = [
            "salePrice" => $salePrice,
            "fullPrice" => ($recommendedPrice == 0 ? $priceWithoutDiscounts : $recommendedPrice),
          ];
        }
      break;

      case self::PRICE_CALCULATION_METHOD_PLUGIN:
        $priceInfo = $this->adios->dispatchEventToPlugins("onProductGetPriceInfoForSingleProduct", [
          "model" => $this,
          "idProduct" => $idProduct,
          "priceInfo" => $priceInfo,
        ])["priceInfo"];
      break;

      case self::PRICE_CALCULATION_METHOD_CUSTOM_PRICE:
      default:
        $priceInfo = [
          "salePrice" => $product['sale_price_custom'],
          "fullPrice" => $product['full_price_custom'],
        ];
      break;

    }

    // DISCOUNT

    $priceInfo["discount"] = $priceInfo["fullPrice"] - $priceInfo["salePrice"];

    return $priceInfo;


  }

  public function translateSingleProductForWeb($product, $languageIndex) {
    $product["TRANSLATIONS"]["name"] = $product["name_lang_{$languageIndex}"];
    $product["TRANSLATIONS"]["brief"] = $product["brief_lang_{$languageIndex}"];
    $product["TRANSLATIONS"]["description"] = $product["description_lang_{$languageIndex}"];

    return $product;
  }

  ////////////////////////////////////////////////////////////////
  // METHODS FOR DATA PROCESSING OF LIST OF PRODUCTS

  public function getDetailedInfoForListOfProducts($idProducts) {
    return $this->unifyProductInformationForListOfProduct(
      $this->getForDetail()->whereIn('id', $idProducts)->get()->toArray()
    );
  }

  public function unifyProductInformationForListOfProduct($products) {
    foreach ($products as $key => $product) {
      $products[$key] = $this->unifyProductInformationForSingleProduct($product);
    }
    return $products;
  }

  public function addPriceInfoForListOfProducts($products, $useCache = TRUE) {
    foreach ($products as $key => $product) {
      $products[$key]['PRICE'] = $this->getPriceInfoForSingleProduct($product, NULL, $useCache);
    }

    return $products;
  }

  public function translateForWeb($products, $languageIndex) {
    foreach ($products as $key => $value) {
      $products[$key] = $this->translateSingleProductForWeb($value, $languageIndex);
    }

    return $products;
  }

}