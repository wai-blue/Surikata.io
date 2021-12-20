(function() {
  var container = document.querySelector( 'div.container' ),
    triggerBttn = document.getElementById( 'trigger-overlay' ),
    overlay = document.querySelector( 'div.overlay' ),
    closeBttn = overlay.querySelector( 'button.overlay-close' );
    transEndEventNames = {
      'WebkitTransition': 'webkitTransitionEnd',
      'MozTransition': 'transitionend',
      'OTransition': 'oTransitionEnd',
      'msTransition': 'MSTransitionEnd',
      'transition': 'transitionend'
    },
    transEndEventName = transEndEventNames[ Modernizr.prefixed( 'transition' ) ],
    support = { transitions : Modernizr.csstransitions };

  function toggleOverlay() {
    if( classie.has( overlay, 'open' ) ) {
      classie.remove( overlay, 'open' );
      classie.remove( container, 'overlay-open' );
      classie.add( overlay, 'close' );
      var onEndTransitionFn = function( ev ) {
        if( support.transitions ) {
          if( ev.propertyName !== 'visibility' ) return;
          this.removeEventListener( transEndEventName, onEndTransitionFn );
        }
        classie.remove( overlay, 'close' );
      };
      if( support.transitions ) {
        overlay.addEventListener( transEndEventName, onEndTransitionFn );
      }
      else {
        onEndTransitionFn();
      }
    }
    else if( !classie.has( overlay, 'close' ) ) {
      classie.add( overlay, 'open' );
      classie.add( container, 'overlay-open' );
    }
  }

  triggerBttn.addEventListener( 'click', toggleOverlay );
  closeBttn.addEventListener( 'click', toggleOverlay );
})();

function searchKeyPressCallback(event, element) {
  var x = event.which || event.keyCode;
  if (x === 13) {
    element = $(element);
    sendSearchData(element.attr("id"));
    return false;
  }
  return false;
}

function sendSearchData(searchInput) {
  searchInput = $("#"+searchInput);
  searchValue = searchInput.val();
  var url = "{{ rootUrl }}";
  url += '/search?search='+searchValue;
  window.location.href = url;
  return false;
}

var customRenderMenu = function(ul, items){
  var self = this;
  var categoryArr = [];

  function contain(item, array) {
    var contains = false;
    $.each(array, function (index, value) {
      if (item == value) {
        contains = true;
        return false;
      }
    });
    return contains;
  }

  $.each(items, function (index, item) {
    if (! contain(item.category, categoryArr)) {
      categoryArr.push(item.category);
    }
  });

  $.each(categoryArr, function (index, category) {
    ul.append("<li class='ui-autocomplete-group'>" + category + "</li>");
    $.each(items, function (index, item) {
      if (item.category == category) {
        self._renderItemData(ul, item);
      }
    });
  });
};

$( "#headerSearch" ).autocomplete({
  source: function (request, response) {
    var requestData = {
      'action': 'searchResults',
      'value': request.term,
      '__renderOnlyPlugin': 'WAI/Misc/WebsiteSearch',
      '__output': 'json',
    }
    $.getJSON('{{ rootUrl }}/WAI/Misc/WebsiteSearch',
      requestData
    ).done(function (data) {
      if (typeof success == 'function') {
        response(data)
      }
      else {
        response(data);
      }
      console.log(data);
    });
  },
  create: function () {
    $(this).data('uiAutocomplete')._renderMenu = customRenderMenu;
    $(this).data('uiAutocomplete')._renderItem = function( ul, item ) {
      if (item.url != null) {
        return $("<li class='ui-menu-item'>")
          .attr("data-value", item.value)
          .append(
            $("<div id='ui-id-2' tabindex='-1' class='ui-menu-item-wrapper'>")
              .append(item.label)
              .attr("onclick", "window.location.href = '" + item.url + "'"))
          .appendTo(ul);
      }
      /*else {
        return $("<li class='ui-menu-item'>")
          .attr("data-value", item.value)
          .append(
            $("<div id='ui-id-2' tabindex='-1' class='ui-menu-item-wrapper'>")
              .append(item.label))
          .appendTo(ul);
      }*/
    };
  },
});

function toggleCollapsed(id) {
  $("#"+id).toggle();
}

function toggleLoginRegister(id, link) {
  $("#login").removeClass("active");
  $("#register").removeClass("active");
  $("#"+id).addClass("active");

  var siblings = $(link).parent().siblings();
  siblings = ($(siblings[0]));
  var childs = siblings.children();
  $(childs[0]).removeClass("active");

  $(link).addClass("active");
}

function sendLoginForm(formId) {
  var form = document.getElementById(formId);
  return form.reportValidity();
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
      }
      else {
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