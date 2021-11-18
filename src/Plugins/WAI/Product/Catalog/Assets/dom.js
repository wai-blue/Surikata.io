class PluginWAIProductCatalogDOMClass extends PluginWAIProductCatalogAPIClass {

  updatePagination() {
    PluginWAIProductCatalogAPIClass.prototype.updatePagination(
      this.page,
      function (data) {
        $('#productPagination')
          .html(data)
          .css('opacity', 1)
        ;
      }
    );
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
  
  
  loadNextPage() {
    var _this = this;
    _this.page++;

    PluginWAIProductCatalogAPIClass.prototype.loadPage(
      _this.page,
      function (data) {
  
        let scrollPosition = $(document).scrollTop();
        let url = new URL(window.location);
        let div = $('<div></div>').html(data);
  
        $('.tab-content').append(div);
        _this.setCatalogListType(_this.catalogListType);
  
        url.searchParams.set('page', _this.page);
        window.history.pushState({}, '', url);
  
        $(document).scrollTop(scrollPosition);
      }
    );
  
    return this;
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
  
    PluginWAIProductCatalogAPIClass.prototype.loadPage(
      this.page,
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
  
  setFilter() {
    $('#accordionExample a').removeClass('sidebar-active');
    $('#accordionExample span').removeClass('sidebar-active');
  }

}