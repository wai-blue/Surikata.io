class PluginWAIProductCatalogAPIClass {

  page = 0;
  filter = {};
  catalogListType = "list";
  
  loadUrlParams(varName) {
    // Do if url contain varName
    if (window.location.href.indexOf(varName)> -1) {
      let url = new URL(window.location.href);
      let urlParams = url.searchParams.get(varName).split(" ");
      urlParams = url.searchParams.get(varName).split(",");
      this.filter[varName] = urlParams;
    }
  }

  setPage(page) {
    this.page = page;
    this.filter['page'] = page;
    return this;
  }

  getScrollId() {
    var searchParams = new URLSearchParams(window.location.search);
    var scrollId = searchParams.get('scrollId');

    if (scrollId != 0) {
      if (performance.navigation.type == 2) {
        $('html, body').animate({
          scrollTop: $(
            "#" + 
            this.catalogListType + 
            "_product_" + 
            scrollId
          ).position().top
        }, 500);
      }
    }
  }

  setCurrentProductPage(page, productId) {
    this.filter['page'] = page;
    this.filter['scrollId'] = productId;

    window.history.pushState({}, '', this.getURL());
  }

  setFilter(varName, varValue) {
    this.filter[varName] = varValue;
    return this;
  }

  addValueToFilter(varName, varValue) {
    if (typeof this.filter[varName] == 'undefined') {
      this.filter[varName] = [];
    }

    this.filter[varName].push(varValue);

    return this;
  }

  removeValueFromFilter(varName, varValue) {
    let newVar = [];
    for (var i = 0; i < this.filter[varName].length; i++) {
      if (this.filter[varName][i] != varValue) {
        newVar.push(this.filter[varName][i]);
      }
    }

    this.filter[varName] = newVar;
    return this;
  }

  getURL(url) {
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
      if (varName == 'idCategory') continue;

      // If the filter does not contain a parameter delete it
      urlObject.searchParams.forEach((index, filterParam) => {
        if (!Object.keys(this.filter).includes(filterParam)) {
          urlObject.searchParams.delete(filterParam);
        }
      })

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

  update(url) {
    let pluginParams = null;

    this.setPage(1);

    pluginParams = { ...this.filter };

    window.history.pushState({}, '', this.getURL(url));

    let updateDiv = $('#productCatalogWrapperDiv');
    updateDiv.css('opacity', 0.5);

    Surikata.renderPlugin(
      "WAI/Product/Catalog",
      pluginParams,
      function (data) {
        updateDiv.fadeOut(100, function () {
          updateDiv.replaceWith(data).fadeIn(100);
          updateDiv.css('opacity', 1);
        });
      }
    );

    return this;
  }

  updatePagination(page, success) {
    Surikata.renderPlugin(
      'WAI/Product/Catalog',
      {
        'page': page,
        'renderOnly': 'productPagination'
      },
      function (data) {
        if (typeof success == 'function') {
          success(data);
        }
      }
    );

    return this;
  }

  loadPage(page, success) {
    Surikata.renderPlugin(
      'WAI/Product/Catalog',
      {
        'page': page,
        'renderOnly': 'productsList',
      },
      function (data) {
        if (typeof success == 'function') {
          success(data);
        }
      }
    );

    return this;
  }

  // TODO: upravit podla konvencii, ako sa deklaruju funkcie vyssie
  changeProductSorting(element) {
    element = $(element);
    var sortValue = element.val();

    var uri = window.location.protocol + "//" + window.location.host + window.location.pathname;
    if (window.location.search.length > 0) {
      uri += window.location.search;
    }

    var newLocation = this.setQueryString(uri, "sort", sortValue);
    newLocation = this.setQueryString(newLocation, "page", "1");

    window.location.href = newLocation;
  }

  // TODO: upravit podla konvencii, ako sa deklaruju funkcie vyssie
  setQueryString(uri, key, value) {
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

}
