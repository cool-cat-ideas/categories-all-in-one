(function (global) {
  'use strict';

  global.CategoriesAllInOneRest = {
    categoriesUrl: function (settings) {
      // WordPress supplies the complete route, including rest_route on plain permalinks.
      const url = new URL(settings.categoriesUrl, global.location.href);
      if (settings.postId) {
        url.searchParams.set('post_id', String(settings.postId));
      }
      return url.href;
    }
  };
})(window);
