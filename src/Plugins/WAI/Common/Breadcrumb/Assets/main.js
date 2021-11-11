class PluginWAICommonBreadcrumbClass {
  update(updateDiv) {

    Surikata.renderPlugin(
      "WAI/Common/Breadcrumb",
      {},
      function (data) {
        updateDiv.replaceWith(data).fadeIn(100);
      }
    );
  
    return this;
  }
}