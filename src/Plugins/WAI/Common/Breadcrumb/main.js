function Breadcrumb() {}

Breadcrumb.prototype.update = function (updateDiv) {

  Surikata.renderPlugin(
    "WAI/Common/Breadcrumb",
    {},
    function (data) {
      updateDiv.replaceWith(data).fadeIn(100);
    }
  );

  return this;
}