tinymce.PluginManager.add(
  'categories_all_in_one_button',
  function (editor, url) {
    editor.addButton('categories_all_in_one_button', {
      title: 'Categories All In One',
      image: url + '/../images/shortcode-icon.png',
      onclick: function () {
        var config = window.CategoriesAllInOneClassicEditor || {};
        var windowWidth = jQuery(window).width();
        var windowHeight = jQuery(window).height() - 150;

        var width = windowWidth > 720 ? 750 : windowWidth - 50;
        var ajaxUrl = config.ajaxUrl || 'admin-ajax.php';

        tb_show(
          'Categories All In One',
          ajaxUrl +
            '?action=categories_all_in_one_shortcode_generator&_ajax_nonce=' +
            encodeURIComponent(config.nonce || '') +
            '&width=' +
            width +
            '&height=' +
            windowHeight
        );
      },
    });
  }
);
