window.onpopstate = function (e) {
    window.location.reload();
  }
  
  ////////////////////////////////////////
  // AbeloThemeProductCatalog
  function AbeloThemeProductCatalog() { SurikataProductCatalog.call(this); }
  AbeloThemeProductCatalog.prototype = Object.create(SurikataProductCatalog.prototype);

  AbeloThemeProductCatalog.prototype.setCatalogListType = function (type) {
    this.catalogListType = type;

    if (type == 'list') {
      var showType = 'list';
      var hideType = 'grid';
    } else {
      var showType = 'grid';
      var hideType = 'list';
    }

    $('.shopType-' + showType).addClass('active');
    $('.shopType-' + hideType).removeClass('active');

    document.cookie = "catalogListType=" + type;
  }

  
  AbeloThemeProductCatalog.prototype.loadNextPage = function () {
    let _this = this;
    _this.page++

    SurikataProductCatalog.prototype.loadNextPage(
      _this,
      function (data) {
        let url = new URL(window.location);
        let div = $('<div></div>').html(data).hide();
  
        $('.tab-content').append(div);
        _this.setCatalogListType(_this.catalogListType);
        div.slideDown();

        url.searchParams.set('page', _this.page);
        window.history.pushState({}, '', url);
      }
    );

    return this;
  }

  AbeloThemeProductCatalog.prototype.loadPage = function (page) {
    let _this = this;

    switch (page) {
      case '-':
        _this.page -= 1;
      break;
      case '+':
        _this.page += 1;
      break;
      default:
        _this.page = page;
      break;
    }

    SurikataProductCatalog.prototype.loadPage(
      _this,
      function (data) {
        let url = new URL(window.location);

        $('.tab-content').html(data).hide().fadeIn();
        _this.setCatalogListType(_this.catalogListType);

        $('html, body').animate({
          scrollTop: $("#productCatalogDefaultContainerDiv").offset().top
        }, 500);

        url.searchParams.set('page', _this.page);
        window.history.pushState({}, '', url);
      }
    );

    return this;
  }
  
  AbeloThemeProductCatalog.prototype.setFilter = function() {
    $('#accordionExample a').removeClass('sidebar-active');
    $('#accordionExample span').removeClass('sidebar-active');
  }

  var AbeloThemeProductCatalog = new AbeloThemeProductCatalog();
  
  ////////////////////////////////////////
  // AbeloThemeCart
  function AbeloThemeCart() { SurikataCart.call(this); }
  AbeloThemeCart.prototype = Object.create(SurikataCart.prototype);

  AbeloThemeCart.prototype.updatePrice = function (data) {
    var spolu = (data.itemUpdated['quantity'] * data.itemUpdated['unit_price']).toFixed(2).toString();
    var spolu_text = spolu.replace(".", ",");

    $('#product-subtotal_' + data.itemUpdated['id_product']).text(spolu_text).fadeIn();
  }

  /*AbeloThemeCart.prototype.updateNavigationCart = function(product) {
    let cartTotal = $('#cartTotal .amount').text();
    let newCartTotal = (parseFloat(cartTotal) - parseFloat(product.itemRemoved[0]['unit_price'] * product.itemRemoved[0]['quantity'])).toFixed(2);

    if (newCartTotal != 0) {
      $('#cartTotal .amount').text(newCartTotal);
      $('.minicart-product-list #cartProduct_' + product.itemRemoved[0]['id_product']).hide();
    } else {
      AbeloThemeCart.emptyNavigationCart();
    }
  }*/

  AbeloThemeCart.prototype.updateHeaderOverview = function (html) {
    $('#navigationCartOverview').html(html);

    let count = $('#navigationCart li').length;

    $('.cart-info a').fadeOut(function () {
      $('.cart-info a').attr('cart-count', count).fadeIn();
    })
  }

  AbeloThemeCart.prototype.initPlusMinusButtons = function() {
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
  }

  AbeloThemeCart.prototype.updateDetailedOverview = function() {
    let _this = this;
    Surikata.renderPlugin(
      'WAI/Order/CartOverview',
      {},
      function (html) {
        $('.cart-main-area').replaceWith(function() {
          return $(html).hide().fadeIn(1000);
        });
        _this.initPlusMinusButtons();
      }
    );
  }
  
  AbeloThemeCart.prototype.updateCheckoutOverview = function() {
    let data = this.serializeOrderData();
    data['renderOnly'] = 'orderOverview';
  
    Surikata.renderPlugin(
      'WAI/Order/Checkout',
      data,
      function (data) {
        $('#order-area')
          .html(data)
          .css('opacity', 1)
        ;
      }
    );
  }
  
  AbeloThemeCart.prototype.placeOrder = function() {
    $('#orderDataForm input').removeClass('required-empty');
    $('#orderDataForm label').removeClass('required-empty');
  
    SurikataCart.prototype.placeOrder(
      function (dataSuccess) {
        if (dataSuccess.status == 'OK') {
          window.location.href = dataSuccess.orderConfirmationUrl;
        }
      },
      function (dataFail) {
        if (dataFail.exception == 'ADIOS\\Widgets\\Orders\\Exceptions\\EmptyRequiredFields') {

          let emptyFields = dataFail.error.split(',');

          $('html, body').animate({
            scrollTop: $('#orderDataForm input[name=' + emptyFields[0] + ']').offset().top
          }, 500);

          for (var i in emptyFields) {
            if (emptyFields[i] == "gdpr_consent" || emptyFields[i] == "general_terms_and_conditions") {
              $('#' + emptyFields[i] + '_label').addClass('required-empty')
            } else {
              $('#orderDataForm input[name=' + emptyFields[i] + ']').addClass('required-empty');
            }
          }
        } else if (dataFail.exception != '') {
          console.log(dataFail);
          $('#unknownErrorDiv').fadeIn();
        }
      }
    );
  }

  AbeloThemeCart.prototype.closeModals = function(modals) {
    modals.forEach((element) => {
      $(element).modal('hide');
    });
    return false;
  }
  
  var AbeloThemeCart = new AbeloThemeCart();
  
  ////////////////////////////////////////
  // AbeloThemePopup
  
  var AbeloThemePopup = {
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
    },
  }
  
  // AbeloThemeCustomer
  
  function AbeloThemeCustomer() { SurikataCustomer.call(this); }
  AbeloThemeCustomer.prototype = Object.create(SurikataCustomer.prototype);
  
  AbeloThemeCustomer.prototype.createAccount = function() {
    $('#registrationForm input').removeClass('required-empty');

    $('#emailIsEmptyOrInvalidErrorDiv').hide();
    $('#accountAlreadyExistsErrorDiv').hide();
    $('#unknownErrorDiv').hide();
    $('#privacyPolicyTermsErrorText').hide();

    if (!$('#privacyPolicyTermsConfirmation').is(':checked')) {
      $('#privacyPolicyTermsErrorDiv').addClass("error");
      $('#privacyPolicyTermsErrorText').show();
    } else {
      $('#privacyPolicyTermsErrorDiv').removeClass("error");
      SurikataCustomer.prototype.createAccount(
        function (dataSuccess) {
          if (dataSuccess.status == 'OK') {
            window.location.href = dataSuccess.registrationConfirmationUrl;
          }        
        },
        function (dataFail) {
          if (dataFail.exception == 'ADIOS\\Widgets\\Customers\\Exceptions\\EmptyRequiredFields') {

            let emptyFields = dataFail.error.split(',');
  
            for (var i in emptyFields) {
              $('#registrationForm input[name=' + emptyFields[i] + ']').addClass('required-empty');
            }
          } else if(dataFail.exception == 'ADIOS\\Widgets\\Customers\\Exceptions\\EmailIsInvalid') {
            $("#registrationForm input[name=email]").addClass('required-empty');
            $('#emailIsEmptyOrInvalidErrorDiv').fadeIn();
          } else if (dataFail.exception == 'ADIOS\\Widgets\\Customers\\Exceptions\\AccountAlreadyExists') {
            $('#accountAlreadyExistsErrorDiv').fadeIn();
            $("#registrationForm input[name=email]").addClass('required-empty');
          } else {
            $('#unknownErrorDiv').fadeIn();
          }
        }
      );
    }
  }

  AbeloThemeCustomer.prototype.removeAddress = function (idAddress) {
    SurikataCustomer.prototype.removeAddress(
      {
        'idAddress': idAddress,
        'customerAction': 'removeAddress'
      },
      function(dataSuccess) {
        if (dataSuccess.status == 'OK') {
          location.reload();
        } 
      },
      function(dataFail) {
        console.log(dataFail);
      }
    );
  }

  AbeloThemeCustomer.prototype.forgotPassword = function () {
    $('#unknownAccount').hide();
    $('#emailIsEmpty').hide();
    $('#emailIsInvalid').hide();
    $('#accountIsNotValidated').hide();
    $('#forgotPasswordDiv input[name=email]').removeClass('required-empty');
    SurikataCustomer.prototype.forgotPassword(
      function(dataSuccess) {
        if (dataSuccess.status == 'OK') {
          $("#forgotPasswordDiv").hide();
          $("#recoveryPasswordSent").fadeIn();
        } 
      },
      function(dataFail) {
        $('#forgotPasswordDiv input[name=email]').addClass('required-empty');
        if (dataFail.exception == "ADIOS\\Widgets\\Customers\\Exceptions\\UnknownAccount") {
          $('#unknownAccount').show();
        } else if(dataFail.exception == "ADIOS\\Widgets\\Customers\\Exceptions\\EmailIsEmpty") {
          $('#emailIsEmpty').show();
        } else if (dataFail.exception == "ADIOS\\Widgets\\Customers\\Exceptions\\EmailIsInvalid") {
          $('#emailIsInvalid').show();
        } else if (dataFail.exception == "ADIOS\\Widgets\\Customers\\Exceptions\\AccountIsNotValidated") {
          $('#accountIsNotValidated').show();
        } else {
          console.log(dataFail);
        }
      }
    );
  }

  var AbeloThemeCustomer = new AbeloThemeCustomer();

  // AbeloTheme Blog
  function AbeloThemeBlogCatalog() { SurikataBlogCatalog.call(this); }
  AbeloThemeBlogCatalog.prototype = Object.create(SurikataBlogCatalog.prototype);

  var AbeloThemeBlogCatalog = new AbeloThemeBlogCatalog();

  // AbeloTheme Breadcrumb
  function AbeloThemeBreadcrumb() { Breadcrumb.call(this); }
  AbeloThemeBreadcrumb.prototype = Object.create(Breadcrumb.prototype);

  var AbeloThemeBreadcrumb = new AbeloThemeBreadcrumb();
