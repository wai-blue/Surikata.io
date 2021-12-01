class PluginWAICommonCustomerCartDOMClass extends PluginWAICustomerCartAPIClass {

  /**
   * Theme functions
   */
  Popup = {
    show: function () {
      $('#onload-popup').modal('show', {}, 500);
      return this;
    },
  
    setImage: function (imgSrc) {
      $('#onload-popup .background_bg').css('background-image', 'url(' + imgSrc + ')');
      return this;
    },
  
    setTitle: function (title) {
      $('#onload-popup .heading_s1 h4').text(title);
      return this;
    },
  
    setSubTitle: function (subTitle) {
      $('#onload-popup .popup-text > p').text(subTitle);
      return this;
    },
  
    setConfirmButtonText: function (text) {
      $('#onload-popup .form-group .btn-confirm').attr("title", text);
      $('#onload-popup .form-group .btn-confirm').text(text);
      return this;
    },
  
    setConfirmButtonOnclick: function (onclick) {
      $('#onload-popup .form-group .btn-confirm').click(onclick);
      return this;
    },
  
    setCancelButtonText: function (text) {
      $('#onload-popup .form-group .btn-confirm').attr("title", text);
      $('#onload-popup .form-group .btn-cancel').text(text);
      return this;
    }
  };

  checkCompanyFields(element) {
    element = $(element);
    element.removeClass('required-empty');
    let isnum;
    switch (element.attr("name")) {
      case "inv_company_name":
        if (element.val().length === 0) {
          element.addClass('required-empty');
        }
        break;
      case "company_id":
        isnum = /^\d+$/.test(element.val());
        if (element.val().length !== 8 || !isnum) {
          element.addClass('required-empty');
        }
        break;
      case "company_tax_id":
        isnum = /^\d+$/.test(element.val());
        if (element.val().length > 0) {
          if (element.val().length !== 10 || !isnum) {
            element.addClass('required-empty');
          }
        }
        break;
      case "company_vat_id":
        if (element.val().length > 0 && element.val().length !== 12) {
          element.addClass('required-empty');
        }
        break;
    }
  };
  
  closeModals(modals) {
    modals.forEach((element) => {
      $(element).modal('hide');
    });
    return false;
  };

  addProductInDetail(idProduct, qty) {
    var _this = this;
    PluginWAICustomerCartAPIClass.prototype.addProduct(idProduct, qty, function(data) {
      _this.Popup
        .setImage(globalTwigParams['filesUrl'] + '/' + data['itemAdded']['urlImage'])
        .setTitle($('.pr_detail .product_title a').text())
        .setSubTitle(Surikata.translate('The product has been added to the cart.', 'Product'))
        .setConfirmButtonText(Surikata.translate('Go to order', 'Product'))
        .setConfirmButtonOnclick(function() {
          window.location.href = data['cartOverviewUrl'];
          return false;
        })
        .setCancelButtonText(Surikata.translate('Continue shopping', 'Product'))
        .show()
      ;

      $('#navigationCartOverview').html(data.cartOverviewHtml);

      let count = $('#navigationCart li').length;
    
      $('.cart-info a').fadeOut(function () {
        $('.cart-info a').attr('cart-count', count).fadeIn();
      })
    })
  };

  addProductInCatalog(idProduct, qty) {
    PluginWAICustomerCartAPIClass.prototype.addProduct(idProduct, qty, function(data) {
      $('#navigationCartOverview').html(data.cartOverviewHtml);

      let count = $('#navigationCart li').length;
    
      $('.cart-info a').fadeOut(function () {
        $('.cart-info a').attr('cart-count', count).fadeIn();
      })
    })
  };

  removeProductInCart(idProduct) {
    PluginWAICustomerCartAPIClass.prototype.removeProduct(idProduct, function(data) {
      $('#navigationCartOverview').html(data.cartOverviewHtml);

      let count = $('#navigationCart li').length;
    
      $('.cart-info a').fadeOut(function () {
        $('.cart-info a').attr('cart-count', count).fadeIn();
      })
    })
  };

  removeProductInCartOverview(idProduct) {
    PluginWAICustomerCartAPIClass.prototype.removeProduct(idProduct, function() {
      PluginWAICustomerCartAPIClass.prototype.updateDetailedOverview(function (html) {
        $('.cart-main-area').replaceWith(function() {
          return $(html).hide().fadeIn(1000);
        });

        var CartPlusMinus = $(".cart-plus-minus");
        CartPlusMinus.prepend('<div class="dec qtybutton">-</div>');
        CartPlusMinus.append('<div class="inc qtybutton">+</div>');
        $(".qtybutton").on("click", function() {
          var $button = $(this);
          var oldValue = $button.parent().find("input").val();
          if ($button.text() === "+") {
            var newVal = parseFloat(oldValue) + 1;
          } else {
            // Don't allow decrementing below zero
            if (oldValue > 1) {
              var newVal = parseFloat(oldValue) - 1;
            } else {
              newVal = 1;
            }
          }
          $button.parent().find("input").val(newVal);
          $button.parent().find("input").trigger("change");
        });
      })
    })
  };
  
  updateProductQtyInCart(idProduct, qty) {
    PluginWAICustomerCartAPIClass.prototype.updateProductQty(idProduct, qty, function() {
      PluginWAICustomerCartAPIClass.prototype.updateProductPrices(function(html) {
        $('.cart-main-area').replaceWith(function() {
          return $(html).hide().fadeIn(1000);
        });

        var CartPlusMinus = $(".cart-plus-minus");
        CartPlusMinus.prepend('<div class="dec qtybutton">-</div>');
        CartPlusMinus.append('<div class="inc qtybutton">+</div>');
        $(".qtybutton").on("click", function() {
          var $button = $(this);
          var oldValue = $button.parent().find("input").val();
          if ($button.text() === "+") {
            var newVal = parseFloat(oldValue) + 1;
          } else {
            // Don't allow decrementing below zero
            if (oldValue > 1) {
              var newVal = parseFloat(oldValue) - 1;
            } else {
              newVal = 1;
            }
          }
          $button.parent().find("input").val(newVal);
          $button.parent().find("input").trigger("change");
        });
      });
    })
  };

  placeOrderInCheckout() {
    $('#orderDataForm input').removeClass('required-empty');
    $('#orderDataForm label').removeClass('required-empty');
  
    PluginWAICustomerCartAPIClass.prototype.placeOrder(
      function (dataSuccess) {
        if (dataSuccess.status == 'OK') {
          window.location.href = dataSuccess.orderConfirmationUrl;
        }
      },
      function (dataFail) {
        if (dataFail.exception == 'ADIOS\\Widgets\\Orders\\Exceptions\\EmptyRequiredFields') {
  
          let emptyFields = dataFail.error.split(',');
  
          $('html, body').animate({
            scrollTop: $('#orderDataForm input[name=' + emptyFields[0] + ']').offset().top - 100
          }, 500);
  
          for (var i in emptyFields) {
            if (emptyFields[i] == "gdpr_consent" || emptyFields[i] == "general_terms_and_conditions") {
              $('#' + emptyFields[i] + '_label').addClass('required-empty')
            } else {
              $('#orderDataForm input[name=' + emptyFields[i] + ']').addClass('required-empty');
            }
          }
        } else if (dataFail.exception == 'ADIOS\\Widgets\\Orders\\Exceptions\\UnknownDeliveryService') {
          $('.order-delivery > label').addClass('input-required');
        } else if (dataFail.exception == 'ADIOS\\Widgets\\Orders\\Exceptions\\UnknownPaymentService') {
          $('.order-payments > label').addClass('input-required');
        } else if (dataFail.exception != '') {
  
          $('#unknownErrorDiv').fadeIn();
        }
      }
    );
  };

  updateCheckoutOverview() {
    PluginWAICustomerCartAPIClass.prototype.updateCheckoutOverview(function(data) {
      $('#order-area')
        .html(data)
        .css('opacity', 1)
      ;
    })
  }

}
