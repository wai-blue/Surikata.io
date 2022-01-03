class PluginWAIBlogCatalogAPIClass {
  filter = {};

  loadUrlParams(varName) {
    // Do if url contain varName
    if (window.location.href.indexOf(varName)> -1) {
      let url = new URL(window.location.href);
      let urlParams = url.searchParams.get(varName).split(" ");
      this.filter[varName] = urlParams;
    }
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
      if (varName == 'page' && this.filter[varName] == 1) continue;

      if (varName == 'tags') {
        urlObject.searchParams.set('tags', this.filter['tags'].join(' '));
      } else {
        urlObject.searchParams.set(varName, this.filter[varName]);
      }
    }

    return urlObject;
  }

  update(updateDiv, url) {
    let _this = this;
    let pluginParams = null;

    pluginParams = { ...this.filter };

    Surikata.renderPlugin(
      "WAI/Blog/Catalog",
      pluginParams,
      function (data) {
        updateDiv.fadeOut(100, function () {
          updateDiv.replaceWith(data).fadeIn(100);
        });
        window.history.pushState({}, '', _this.getURL(url));
      }
    );

    return this;
  }
}
