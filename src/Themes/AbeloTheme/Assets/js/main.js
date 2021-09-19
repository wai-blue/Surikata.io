$(function () {
  "use strict";

  /*---------------------------
     Commons Variables
  ------------------------------ */
  var $window = $(window),
    $body = $("body");

  /*---------------------------
     Menu Fixed On Scroll Active
  ------------------------------ */
  $(window).scroll(function () {
    var window_top = $(window).scrollTop() + 1;
    if (window_top > 250) {
      $(".sticky-nav").addClass("menu_fixed animated fadeInDown");
    } else {
      $(".sticky-nav").removeClass("menu_fixed animated fadeInDown");
    }
  });

  $('[data-bg-image]').each(function () {
    var $this = $(this),
      $image = $this.data('bg-image');
    $this.css('background-image', 'url(' + $image + ')');
  });

  /*---------------------------
     Menu menu-content
  ------------------------------ */

  $(".header-menu-vertical .menu-title").on("click", function (event) {
    $(".header-menu-vertical .menu-content").slideToggle(500);
  });

  $(".menu-content").each(function () {
    var $ul = $(this),
      $lis = $ul.find(".menu-item:gt(7)"),
      isExpanded = $ul.hasClass("expanded");
    $lis[isExpanded ? "show" : "hide"]();

    if ($lis.length > 0) {
      $ul.append(
        $(
          '<li class="expand">' +
          (isExpanded ? '<a href="javascript:;"><span><i class="ion-android-remove"></i>Close Categories</span></a>' : '<a href="javascript:;"><span><i class="ion-android-add"></i>More Categories</span></a>') +
          "</li>"
        ).on("click", function (event) {
          var isExpanded = $ul.hasClass("expanded");
          event.preventDefault();
          $(this).html(isExpanded ? '<a href="javascript:;"><span><i class="ion-android-add"></i>More Categories</span></a>' : '<a href="javascript:;"><span><i class="ion-android-remove"></i>Close Categories</span></a>');
          $ul.toggleClass("expanded");
          $lis.toggle(300);
        })
      );
    }
  });

  /*---------------------------------
      Off Canvas Function
  -----------------------------------*/
  (function () {
    var $offCanvasToggle = $(".offcanvas-toggle"),
      $offCanvas = $(".offcanvas"),
      $offCanvasOverlay = $(".offcanvas-overlay"),
      $mobileMenuToggle = $(".mobile-menu-toggle");
    $offCanvasToggle.on("click", function (e) {
      e.preventDefault();
      var $this = $(this),
        $target = $this.attr("href");
      $body.addClass("offcanvas-open");
      $($target).addClass("offcanvas-open");
      $offCanvasOverlay.fadeIn();
      if ($this.parent().hasClass("mobile-menu-toggle")) {
        $this.addClass("close");
      }
    });
    $(".offcanvas-close, .offcanvas-overlay").on("click", function (e) {
      e.preventDefault();
      $body.removeClass("offcanvas-open");
      $offCanvas.removeClass("offcanvas-open");
      $offCanvasOverlay.fadeOut();
      $mobileMenuToggle.find("a").removeClass("close");
    });
  })();

  /*----------------------------------
      Off Canvas Menu
  -----------------------------------*/
  function mobileOffCanvasMenu() {
    var $offCanvasNav = $(".offcanvas-menu, .overlay-menu"),
      $offCanvasNavSubMenu = $offCanvasNav.find(".sub-menu");

    /*Add Toggle Button With Off Canvas Sub Menu*/
    $offCanvasNavSubMenu.parent().prepend('<span class="menu-expand"></span>');

    /*Category Sub Menu Toggle*/
    $offCanvasNav.on("click", "li a, .menu-expand", function (e) {
      var $this = $(this);
      if ($this.attr("href") === "#" || $this.hasClass("menu-expand")) {
        e.preventDefault();
        if ($this.siblings("ul:visible").length) {
          $this.parent("li").removeClass("active");
          $this.siblings("ul").slideUp();
          $this.parent("li").find("li").removeClass("active");
          $this.parent("li").find("ul:visible").slideUp();
        } else {
          $this.parent("li").addClass("active");
          $this.closest("li").siblings("li").removeClass("active").find("li").removeClass("active");
          $this.closest("li").siblings("li").find("ul:visible").slideUp();
          $this.siblings("ul").slideDown();
        }
      }
    });
  }

  mobileOffCanvasMenu();

  /*------------------------------
          Hero Slider
  -----------------------------------*/

  $('.hero-slider-wrapper').slick({
    infinite: true,
    slidesToShow: 1,
    arrows: false,
    slidesToScroll: 1,
    speed: 500,
    fade: true,
    cssEase: 'linear',
    dots: true,
    autoplay: true,
    autoplaySpeed: 5000,
  });

  /*------------------------------
          Feature Slider
  -----------------------------------*/

  $('.feature-slider-wrapper').slick({
    infinite: true,
    slidesToShow: 3,
    arrows: true,
    slidesToScroll: 1,
    prevArrow: '<span class="prev"><i class="ion-ios-arrow-left"></i></span>',
    nextArrow: '<span class="next"><i class="ion-ios-arrow-right"></i></span>',
    speed: 800,
    cssEase: 'linear',
    dots: false,
    responsive: [{
      breakpoint: 992,
      Settings: {
        slidesToShow: 2,
        slidesToScroll: 1
      }
    },
      {
        breakpoint: 767,
        Settings: {
          slidesToShow: 2,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 479,
        Settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      }
    ]
  });


  /*------------------------------
          Hot Deal Slider
  -----------------------------------*/

  $('.hot-deal-slider-wrapper').slick({
    infinite: true,
    slidesToShow: 1,
    arrows: true,
    slidesToScroll: 1,
    prevArrow: '<span class="prev"><i class="ion-ios-arrow-left"></i></span>',
    nextArrow: '<span class="next"><i class="ion-ios-arrow-right"></i></span>',
    speed: 800,
    cssEase: 'linear',
    dots: false,
  });
  /*------------------------------
        Hot Deal-2 Slider
  -----------------------------------*/

  $('.hot-deal-slider-wrapper-2').slick({
    infinite: true,
    slidesToShow: 4,
    arrows: true,
    slidesToScroll: 1,
    prevArrow: '<span class="prev"><i class="ion-ios-arrow-left"></i></span>',
    nextArrow: '<span class="next"><i class="ion-ios-arrow-right"></i></span>',
    speed: 800,
    cssEase: 'linear',
    dots: false,
    responsive: [{
      breakpoint: 1200,
      Settings: {
        slidesToShow: 4,
        slidesToScroll: 1
      }
    },
      {
        breakpoint: 992,
        Settings: {
          slidesToShow: 3,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 767,
        Settings: {
          slidesToShow: 2,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 492,
        Settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      },
    ]
  });


  /*------------------------------
            Best Sell Slider
    -----------------------------------*/

  $('.best-sell-area-wrapper').slick({
    infinite: true,
    slidesToShow: 2,
    arrows: true,
    slidesToScroll: 1,
    prevArrow: '<span class="prev"><i class="ion-ios-arrow-left"></i></span>',
    nextArrow: '<span class="next"><i class="ion-ios-arrow-right"></i></span>',
    speed: 800,
    cssEase: 'linear',
    dots: false,
    responsive: [{
      breakpoint: 992,
      Settings: {
        slidesToShow: 1,
        slidesToScroll: 1
      }
    },
      {
        breakpoint: 767,
        Settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 479,
        Settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      }
    ]
  });
  /*------------------------------
            Arrivel Slider
    -----------------------------------*/

  $('.arrival-slider-wrapper').slick({
    infinite: true,
    slidesToShow: 5,
    arrows: true,
    loop: true,
    slidesToScroll: 1,
    prevArrow: '<span class="prev"><i class="ion-ios-arrow-left"></i></span>',
    nextArrow: '<span class="next"><i class="ion-ios-arrow-right"></i></span>',
    speed: 800,
    cssEase: 'linear',
    dots: false,
    responsive: [{
      breakpoint: 1200,
      Settings: {
        slidesToShow: 4,
        slidesToScroll: 1
      }
    },
      {
        breakpoint: 992,
        Settings: {
          slidesToShow: 3,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 767,
        Settings: {
          slidesToShow: 2,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 479,
        Settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      }
    ]
  });

  /*------------------------------
          Category Slider
  -----------------------------------*/

  $('.category-slider-wraper').slick({
    infinite: true,
    slidesToShow: 1,
    arrows: true,
    slidesToScroll: 1,
    prevArrow: '<span class="prev"><i class="ion-ios-arrow-left"></i></span>',
    nextArrow: '<span class="next"><i class="ion-ios-arrow-right"></i></span>',
    speed: 800,
    cssEase: 'linear',
    dots: false,
  });
  /*------------------------------
          Brand Slider
  -----------------------------------*/

  $('.brand-slider').slick({
    infinite: true,
    slidesToShow: 5,
    arrows: false,
    slidesToScroll: 1,
    speed: 800,
    cssEase: 'linear',
    dots: false,
    responsive: [{
      breakpoint: 1200,
      Settings: {
        slidesToShow: 4,
        slidesToScroll: 1
      }
    },
      {
        breakpoint: 992,
        Settings: {
          slidesToShow: 3,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 767,
        Settings: {
          slidesToShow: 2,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 479,
        Settings: {
          slidesToShow: 2,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 400,
        Settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      }
    ]
  });
  /*------------------------------
          popular Category Slider
  -----------------------------------*/

  $('.popular-category-slider-wrapper').slick({
    infinite: true,
    slidesToShow: 4,
    arrows: true,
    slidesToScroll: 1,
    prevArrow: '<span class="prev"><i class="ion-ios-arrow-left"></i></span>',
    nextArrow: '<span class="next"><i class="ion-ios-arrow-right"></i></span>',
    speed: 800,
    cssEase: 'linear',
    dots: false,
    responsive: [{
      breakpoint: 1200,
      Settings: {
        slidesToShow: 4,
        slidesToScroll: 1
      }
    },
      {
        breakpoint: 992,
        Settings: {
          slidesToShow: 3,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 767,
        Settings: {
          slidesToShow: 2,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 479,
        Settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      },
    ]
  });
  /*------------------------------
          Footer Blog Slider
  -----------------------------------*/

  $('.footer-blog-slider-wrapper').slick({
    infinite: true,
    slidesToShow: 1,
    arrows: true,
    slidesToScroll: 1,
    prevArrow: '<span class="prev"><i class="ion-ios-arrow-left"></i></span>',
    nextArrow: '<span class="next"><i class="ion-ios-arrow-right"></i></span>',
    speed: 800,
    cssEase: 'linear',
    dots: false,
  });

  /*------------------------------
          Quickview Slider
  -----------------------------------*/
  $('.gallery-top').slick({
    autoplay: false,
    autoplaySpeed: 1000,
    pauseOnHover: true,
    arrows: false,
    dots: false,
    infinite: true,
    fade: true,
    asNavFor: '.gallery-thumbs',
  });
  $('.gallery-thumbs').slick({
    slidesToShow: 4,
    slidesToScroll: 1,
    arrows: true,
    prevArrow: '<span class="prev"><i class="ion-ios-arrow-left"></i></span>',
    nextArrow: '<span class="next"><i class="ion-ios-arrow-right"></i></span>',
    dots: false,
    infinite: true,
    focusOnSelect: true,
    loop: true,
    asNavFor: '.gallery-top',
  });


  /*----------------------------
      All Category toggle
   ------------------------------*/

  $(".category-toggle").on("click", function (e) {
    $(".category-menu").slideToggle("slow");
  });
  $(".menu-item-has-children-1").on("click", function (e) {
    $(".category-mega-menu-1").slideToggle("slow");
  });
  $(".menu-item-has-children-2").on("click", function (e) {
    $(".category-mega-menu-2").slideToggle("slow");
  });
  $(".menu-item-has-children-3").on("click", function (e) {
    $(".category-mega-menu-3").slideToggle("slow");
  });
  $(".menu-item-has-children-4").on("click", function (e) {
    $(".category-mega-menu-4").slideToggle("slow");
  });
  $(".menu-item-has-children-5").on("click", function (e) {
    $(".category-mega-menu-5").slideToggle("slow");
  });
  $(".menu-item-has-children-6").on("click", function () {
    $(".category-mega-menu-6").slideToggle("slow");
  });

  /*-----------------------------
            Category more toggle
      -------------------------------*/

  $(".category-menu li.hidden").hide();
  $("#more-btn").on("click", function (e) {
    e.preventDefault();
    $(".category-menu li.hidden").toggle(500);
    var htmlAfter = '<i class="ion-ios-minus-empty" aria-hidden="true"></i> Less Categories';
    var htmlBefore = '<i class="ion-ios-plus-empty" aria-hidden="true"></i> More Categories';

    if ($(this).html() == htmlBefore) {
      $(this).html(htmlAfter);
    } else {
      $(this).html(htmlBefore);
    }
  });

  /*---------------------
      Countdown
  --------------------- */
  $("[data-countdown]").each(function () {
    var $this = $(this),
      finalDate = $(this).data("countdown");
    $this.countdown(finalDate, function (event) {
      $this.html(event.strftime('<span class="cdown day">%-D <p>Days</p></span> <span class="cdown hour">%-H <p>Hours</p></span> <span class="cdown minutes">%M <p>Mins</p></span> <span class="cdown second">%S <p>Sec</p></span>'));
    });
  });

  /*---------------------
      Scroll Up
  --------------------- */
  $.scrollUp({
    scrollText: '<i class="ion-android-arrow-up"></i>',
    easingType: "linear",
    scrollSpeed: 900,
    animation: "fade",
  });

  /*----------------------------
      Cart Plus Minus Button
  ------------------------------ */
  var CartPlusMinus = $(".cart-plus-minus");
  CartPlusMinus.prepend('<div class="dec qtybutton">-</div>');
  CartPlusMinus.append('<div class="inc qtybutton">+</div>');
  $(".qtybutton").on("click", function () {
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

  /*--------------------------
          Product Zoom
  ---------------------------- */

  var zoomOptions = {
    zoomType: "inner",
    cursor: "crosshair",
    easing: true,
    responsive: true,
  };

  $(".zoompro-wrap").slick({
    asNavFor: ".product-dec-slider-2",
    slidesToShow: 1,
    arrows: false,
    dots: false,
    fade: true,
  });
  $(".product-dec-slider-2").slick({
    slidesToShow: 4,
    slidesToScroll: 1,
    arrows: true,
    prevArrow: '<span class="prev"><i class="ion-ios-arrow-left"></i></span>',
    nextArrow: '<span class="next"><i class="ion-ios-arrow-right"></i></span>',
    dots: false,
    infinite: true,
    loop: true,
    asNavFor: ".zoompro-wrap",
    focusOnSelect: true
  });
  $(".zoompro-wrap .slick-current img").elevateZoom(zoomOptions);
  $(".zoompro-wrap").on("beforeChange", function (
    event,
    slick,
    currentSlide,
    nextSlide
  ) {
    $.removeData(currentSlide, "elevateZoom");
    $(".zoomContainer").remove();
  });
  $(".zoompro-wrap").on("afterChange", function () {
    $(".zoompro-wrap .slick-current img").elevateZoom(zoomOptions);
  });

  /*--------------------------
          Product Zoom
  ---------------------------- */

  var zoomOptions = {
    zoomType: "inner",
    cursor: "crosshair",
    easing: true,
    responsive: true,
    zoomWindowWidth: 300,
    zoomWindowHeight: 100,
  };

  $(".zoompro-wrap-2").slick({
    asNavFor: ".product-dec-slider-3",
    slidesToShow: 1,
    arrows: false,
    dots: false,
    fade: true,
  });
  $(".product-dec-slider-3").slick({
    slidesToShow: 4,
    slidesToScroll: 1,
    arrows: true,
    prevArrow: '<span class="prev"><i class="ion-ios-arrow-left"></i></span>',
    nextArrow: '<span class="next"><i class="ion-ios-arrow-right"></i></span>',
    dots: false,
    infinite: true,
    loop: true,
    vertical: true,
    asNavFor: ".zoompro-wrap-2",
    focusOnSelect: true
  });
  $(".zoompro-wrap-2 .slick-current img").elevateZoom(zoomOptions);
  $(".zoompro-wrap-2").on("beforeChange", function (
    event,
    slick,
    currentSlide,
    nextSlide
  ) {
    $.removeData(currentSlide, "elevateZoom");
    $(".zoomContainer").remove();
  });
  $(".zoompro-wrap-2").on("afterChange", function () {
    $(".zoompro-wrap-2 .slick-current img").elevateZoom(zoomOptions);
  });

  /*------------------------------
          popular Category Slider
  -----------------------------------*/

  $('.single-product-slider-active').slick({
    infinite: true,
    slidesToShow: 4,
    arrows: true,
    slidesToScroll: 1,
    prevArrow: '<span class="prev"><i class="ion-ios-arrow-left"></i></span>',
    nextArrow: '<span class="next"><i class="ion-ios-arrow-right"></i></span>',
    speed: 800,
    cssEase: 'linear',
    dots: false,
    responsive: [{
      breakpoint: 1200,
      Settings: {
        slidesToShow: 4,
        slidesToScroll: 1
      }
    },
      {
        breakpoint: 992,
        Settings: {
          slidesToShow: 3,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 767,
        Settings: {
          slidesToShow: 2,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 479,
        Settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      },
    ]
  });
  /*---------------------------
     Blog Gallery Slider
  ------------------------------ */

  $('.blog-gallery').slick({
    dots: false,
    infinite: true,
    arrows: true,
    prevArrow: '<span class="prev"><i class="ion-ios-arrow-left"></i></span>',
    nextArrow: '<span class="next"><i class="ion-ios-arrow-right"></i></span>',
    speed: 800,
    slidesToShow: 1,
    slidesToScroll: 1,
  });

  /*-------------------------------
      Create an account toggle
  ---------------------------------*/
  $(".checkout-toggle2").on("click", function () {
    $(".open-toggle2").slideToggle(1000);
  });

  $(".checkout-toggle").on("click", function () {
    $(".open-toggle").slideToggle(1000);
  });


});

function validateProductQty(input) {
  input = $(input);
  var value = input.val();
  var reg = new RegExp('^[0-9]$');

  if (!reg.test(value)) {
    input.val('1');
    return;
  }
  if (parseInt(value) < 1) {
    input.val('1');
  }
}

function sendLoginForm(formId) {
  var form = document.getElementById(formId);
  if (!form.reportValidity()) {
    return false;
  }
  var test = true;
  form = $("#" + formId);
  form.find('input[name=loginEmail]').each(function () {
    var element = $(this);
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    test = regex.test(element.val());
  });
  if (test) {
    document.getElementById(formId).submit();
  }
}

function sendContactForm(formId) {
  var form = $(formId);
  data = {};

  form.find('input, select, textarea').each(function () {
    var element = $(this);
    if (!validateInputs(this)) {
      return false;
    }
    if (element.prop('disabled') !== true && element.attr("name") !== undefined && element.attr("name") !== null) {
      if (element.val() !== undefined && element.val() instanceof Array && element.val().length > 0) {
        element.val().forEach(function (value, index) {
          data[element.attr("name")] = value;
        })
      } else {
        data[element.attr("name")] = element.val();
      }
    }
  });
  data['__renderOnlyPlugin'] = 'WAI/Misc/ContactForm';
  data['__output'] = 'json';
  let urlToSend = window.location.href;

  $.getJSON(urlToSend,
    data
  ).done(function (response) {
    try {
      if (response["status"] === "success") {
        Swal.fire({
          width: '40rem',
          title: '<strong>Message was send</strong>',
          text: '',
          icon: 'success',
          confirmButtonText: 'OK',
          customClass: {
            confirmButton: 'swal-confirm-button',
          },
          timer: 10000,
        });
        clearInputs(formId);
      } else {
        Swal.fire({
          title: 'Error!',
          text: 'Input fields were filled out incorrectly',
          icon: 'error',
          confirmButtonText: 'Try again'
        });
      }
      console.log(response);
    } catch (e) {
      console.log(response);
      return;
    }
    console.log(response);
  });

  return false;
}

function validateInputs(element) {
  element = $(element);
  var value = element.val();
  var test = false;
  switch (element.attr('name')) {
    case "email":
      var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
      test = regex.test(value);
      break;
    case "name":
    case "contact_message":
      test = value.length > 2;
      break;
    case "phone_number":
      test = value.length > 9;
      break;
    default:
      test = true;
      break;
  }
  if (!test) {
    element.addClass("error-input");
  } else {
    element.removeClass("error-input");
  }
  return test;
}

function clearInputs(formId) {
  var form = $(formId);
  form.find('input, select, textarea').each(function () {
    var element = $(this);

    if (element.prop('disabled') !== true && element.attr("name") !== undefined && element.attr("name") !== null) {
      if (element.val() !== undefined && element.val() instanceof Array && element.val().length > 0) {
        element.val().forEach(function (value, index) {
          element.val("");
        })
      } else {
        element.val("");
      }
    }
  });
}

function openQuickModal(element) {
  element = $(element);
  var requestData = {};
  var pluginName = "WAI\\Product\\Detail"
  requestData.__renderOnlyPlugin = pluginName;
  requestData.__output = 'json';
  requestData.productAction = element.attr("data-link-action");
  requestData.idProduct = element.attr("data-link-id");

  $.getJSON(
    '',
    requestData
  ).done(function (data) {
    $("#quickViewDocument").html(data.productModalContent);
  }).fail(function (data) {
    console.log('renderPluginJSON Error', pluginName, requestData, data);
  });
}

function openAddAddressModal(element) {
  element = $(element);
  var requestData = {};
  var pluginName = "WAI\\Customer\\Home"
  requestData.__renderOnlyPlugin = pluginName;
  requestData.__output = 'json';
  requestData.customerAction = element.attr("data-link-action");
  requestData.idAddress = element.attr("data-link-id");

  $.getJSON(
    '',
    requestData
  ).done(function (data) {
    $("#addAddressDocument").html(data.addressModalContent);
  }).fail(function (data) {
    console.log('renderPluginJSON Error', pluginName, requestData, data);
  });
}

function validateEmail(input, elementId) {
  input = $(input);
  var element;
  var value = input.val();
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  var test = regex.test(value);

  if (elementId.length === 0) {
    element = input;
  } else {
    element = $("#" + elementId);
  }

  if (!test) {
    element.addClass("error-input");
  } else {
    element.removeClass("error-input");
  }
  return test;
}

function subscribeNewsletter(formId) {
  var form = $("#" + formId);
  var requestData = {};

  form.find('input').each(function () {
    var element = $(this);
    if (element.attr("name") === 'newsletterEmail') {
      if (!validateEmail(element, formId)) {
        return false;
      }
      requestData.email = element.val();
    }
  });

  var pluginName = "WAI\\Misc\\Newsletter";
  requestData.__renderOnlyPlugin = pluginName;
  requestData.__output = 'json';
  requestData.action = 'subscribe';

  $.getJSON(
    '',
    requestData
  ).done(function (data) {
    Swal.fire({
      width: '40rem',
      title: '<strong>Your email is subscribe</strong>',
      text: '',
      icon: 'success',
      confirmButtonText: 'OK',
      customClass: {
        confirmButton: 'swal-confirm-button',
      },
      timer: 10000,
    });
    clearInputs("#"+formId);
  }).fail(function (data) {
    console.log('renderPluginJSON Error', pluginName, requestData, data);
    Swal.fire({
      width: '40rem',
      title: '<strong>' + data.responseText + '</strong>',
      text: '',
      icon: 'error',
      confirmButtonText: 'OK',
      customClass: {
        confirmButton: 'swal-confirm-button',
      },
      timer: 10000,
    });
  });
}