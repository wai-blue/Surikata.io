function SurikataProductCatalog() {
  this.page = 0;
  this.filter = {};
  this.catalogListType = "list";
}

SurikataProductCatalog.prototype.loadUrlParams = function (varName) {
  // Do if url contain varName
  if (window.location.href.indexOf(varName)> -1) {
    let url = new URL(window.location.href);
    let urlParams = url.searchParams.get(varName).split(" ");
    this.filter[varName] = urlParams;
  }
}

SurikataProductCatalog.prototype.setPage = function (page) {
  this.page = page;
  this.filter['page'] = page;
  return this;
}

SurikataProductCatalog.prototype.getScrollId = function() {
  var searchParams = new URLSearchParams(window.location.search);
  var scrollId = searchParams.get('scrollId');
  var _this = this;

  if (scrollId != 0) {
    if (performance.navigation.type == 2) {
      $('html, body').animate({
        scrollTop: $(
          "#" + 
          _this.catalogListType + 
          "_product_" + 
          scrollId
        ).position().top
      }, 500);
    }
  }
}

SurikataProductCatalog.prototype.setCurrentProductPage = function (page, productId) {
  let _this = this;
  this.filter['page'] = page;
  this.filter['scrollId'] = productId;

  window.history.pushState({}, '', _this.getURL());
}

SurikataProductCatalog.prototype.setFilter = function (varName, varValue) {
  this.filter[varName] = varValue;
  return this;
}

SurikataProductCatalog.prototype.addValueToFilter = function (varName, varValue) {
  if (typeof this.filter[varName] == 'undefined') {
    this.filter[varName] = [];
  }

  this.filter[varName].push(varValue);

  return this;
}

SurikataProductCatalog.prototype.removeValueFromFilter = function (varName, varValue) {
  let newVar = [];
  for (i = 0; i < this.filter[varName].length; i++) {
    if (this.filter[varName][i] != varValue) {
      newVar.push(this.filter[varName][i]);
    }
  }
  this.filter[varName] = newVar;
  return this;
}

SurikataProductCatalog.prototype.getURL = function (url) {
  let currentUrl = new URL(window.location);
  let urlObject = null;

  if (typeof url == 'undefined') {
    urlObject = currentUrl;
  } else {
    try {
      urlObject = new URL(url);
    } catch (e) {
      urlObject = new URL(currentUrl.origin + url);
    }
  }

  for (let varName in this.filter) {
    //if (varName == 'page' && this.filter[varName] == 1) continue;
    if (varName == 'idProductCategory') continue;

    if (varName == 'brands') {
      urlObject.searchParams.set('brands', this.filter['brands'].join(' '));
    } else if (varName == "scrollId") {
      urlObject.searchParams.set('scrollId', this.filter['scrollId']);
    } else {
      urlObject.searchParams.set(varName, this.filter[varName]);
    }
  }

  return urlObject;
}

SurikataProductCatalog.prototype.update = function (updateDiv, url) {
  let _this = this;
  let pluginParams = null;
  
  this.setPage(1);

  pluginParams = { ...this.filter };
  window.history.pushState({}, '', _this.getURL(url));

  Surikata.renderPlugin(
    "WAI/Product/Catalog",
    pluginParams,
    function (data) {
      updateDiv.fadeOut(100, function () {
        updateDiv.replaceWith(data).fadeIn(100);
      });
    }
  );

  return this;
}

SurikataProductCatalog.prototype.updatePagination = function () {
  _this = this;

  Surikata.renderPlugin(
    'WAI/Product/Catalog',
    {
      'page': (_this.page),
      'renderOnly': 'productPagination'
    },
    function (data) {
      $('#productPagination')
        .html(data)
        .css('opacity', 1)
      ;
    }
  );
}

SurikataProductCatalog.prototype.loadNextPage = function (__this, success) {
  let _this = __this;

  Surikata.renderPlugin(
    'WAI/Product/Catalog',
    {
      'page': _this.page,
      'renderOnly': 'productsList',
    },
    function (data) {
      if (typeof success == 'function') {
        success(data);
      }
    }
  );
}

SurikataProductCatalog.prototype.loadPage = function (__this, success) {
  let _this = __this;

  Surikata.renderPlugin(
    'WAI/Product/Catalog',
    {
      'page': _this.page,
      'renderOnly': 'productsList',
    },
    function (data) {
      if (typeof success == 'function') {
        success(data);
      }
    }
  );
}

// TODO: upravit podla konvencii, ako sa deklaruju funkcie vyssie
function changeProductSorting(element) {
  element = $(element);
  var sortValue = element.val();
  console.log(window.location.search);
  var uri = window.location.protocol + "//" + window.location.host + window.location.pathname;
  if (window.location.search.length > 0) {
    uri += window.location.search;
  }

  newLocation = setQueryString(uri, "sort", sortValue);
  newLocation = setQueryString(newLocation, "page", "1");

  window.location.href = newLocation;
}

// TODO: upravit podla konvencii, ako sa deklaruju funkcie vyssie
function setQueryString(uri, key, value) {
  var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
  var separator = uri.indexOf('?') !== -1 ? "&" : "?";
  var newLocation = "";
  if (uri.match(re)) {
     return uri.replace(re, '$1' + key + "=" + value + '$2');
  }
  else {
    return uri + separator + key + "=" + value;
  }
}
