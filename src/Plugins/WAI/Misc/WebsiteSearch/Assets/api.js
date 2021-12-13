class PluginWAIMiscWebsiteSearchAPIClass {
  search(query) {
    var url = "{{ rootUrl }}/hladat?search=" + query;
    window.location.href = url;
    return false;
  }
}
