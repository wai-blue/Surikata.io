class PluginWAIProductCatalogDOMClass extends PluginWAIProductCatalogAPIClass {

  updatePagination() {
    super.updatePagination(
      this.page,
      function (data) {
        $('#productCatalogPaginationDiv')
          .replaceWith(data)
          .css('opacity', 1)
        ;
      }
    );

    return this;
  }

  setCatalogListType(type) {
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

  onCatalogHtmlBeforeLoad() {
    $('#productCatalogProductListDiv').css('opacity', 0.5);
    $('html, body').animate({
      scrollTop: $("#productCatalogProductListDiv").offset().top
    }, 200);
  }

  onCatalogHtmlReady(html) {
    $('#productCatalogProductListDiv').replaceWith(html);
    $('#productCatalogProductListDiv').css('opacity', 1);

  }

  loadPage(page) {
    var _this = this;
    switch (page) {
      case '-':
        this.page -= 1;
      break;
      case '+':
        this.page += 1;
      break;
      default:
        this.page = page;
      break;
    }

    if (page <= 0) {
      page = 1;
    }
  
    _this.onCatalogHtmlBeforeLoad();

    super.loadPage(
      this.page,
      function (data) {
        _this.onCatalogHtmlReady(data);
  
        let url = new URL(window.location);
        url.searchParams.set('page', _this.page);
        window.history.pushState({}, '', url);
      }
    );
  
    return this;
  }
  
  setFilter(varName, varValue) {
    $('#accordionExample a').removeClass('sidebar-active');
    $('#accordionExample span').removeClass('sidebar-active');

    super.setFilter(varName, varValue);
  }

}
