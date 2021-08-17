<?php

namespace Surikata\Plugins\WAI\Product\Detail\Modals;

class ProductModal
{
  private $uploadDir;
  private $adios;

  public function __construct(&$adios = NULL) {
    $this->adios = $adios;
    $this->uploadDir = $this->adios->config["files_url"];
  }

  public function renderDefaultModal($product) {
    $priceString = number_format($product['sale_price'], 2, ",", " ");
    $html = "
      <div class='modal-header'>
        <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>x</span></button>
      </div>
      <div class='modal-body'>
        <div class='row'>
          <div class='col-md-6 col-sm-12 col-xs-12 mb-lm-100px mb-sm-30px'>
            <div class='quickview-wrapper'>
              <!-- slider -->
              <div class='gallery-top'>
                <div class='single-slide'>
                  <img class='img-responsive m-auto' src='./../upload/{$product['image']}' alt='{$product['name_lang_1']}'>
                </div>
              </div>
              <div class=' gallery-thumbs'>
              </div>
            </div>
          </div>
          <div class='col-md-6 col-sm-12 col-xs-12'>
            <div class='product-details-content quickview-content'>
              <h2>{$product["name_lang_1"]}</h2>
              <div class='pro-details-rating-wrap'>
              </div>
              <div class='pricing-meta'>
                <ul>
                  <li class='not-cut'>{$priceString} €</li>
                </ul>
              </div>
              <p class='quickview-para'>{$product['brief_lang_1']}</p>
              <div class='pro-details-quality'>
                <div class='cart-plus-minus'>
                  <input class='cart-plus-minus-box' type='text' name='qtybutton' value='1' disabled />
                </div>
                <div class='pro-details-cart btn-hover'>
                  <a href='javascript:void(0)' onclick='AbeloThemeCart.addProduct(
                  {$product["id"]},
                    1,
                    function(data) {
                    AbeloThemePopup
                    .setImage(\"./../upload/{$product['image']}\")
                    .setTitle($(\".pr_detail .product_title a\").text())
                    .setSubTitle(\"Produkt bol pridaný do košíka.\")
                    .setConfirmButtonText(\"Prejsť na objednávku\")
                    .setConfirmButtonOnclick(function() {
                    window.location.href = \"./kosik\";
                    return false;
                    })
                    .setCancelButtonText(\"Pokračovať v nákupe\")
                    .show()
                    ;
                    }
                    );
                    '> + Add To Cart</a>
                </div>
              </div>
              <div class='pro-details-wish-com'>
                <!--<div class='pro-details-wishlist'>
                  <a href='javascript:void(0)'><i class='ion-android-favorite-outline'></i>Add to wishlist</a>
                </div>
                <div class='pro-details-compare'>
                  <a href='javascript:void(0)'><i class='ion-ios-shuffle-strong'></i>Add to compare</a>
                </div>-->
              </div>
              <div class='pro-details-social-info'>
                <!--<span>Share</span>
                <div class='social-info'>
                  <ul>
                    <li>
                      <a href='#'><i class='ion-social-facebook'></i></a>
                    </li>
                    <li>
                      <a href='#'><i class='ion-social-twitter'></i></a>
                    </li>
                    <li>
                      <a href='#'><i class='ion-social-google'></i></a>
                    </li>
                    <li>
                      <a href='#'><i class='ion-social-instagram'></i></a>
                    </li>
                  </ul>
                </div>-->
              </div>
            </div>
          </div>
        </div>
      </div>
      ";
    return $html;
  }
}