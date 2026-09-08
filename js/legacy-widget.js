jQuery(document).ready(function ($) {
  const toggleLoader = ($form) => {
    if ($form) {
      const loader = $form.find('.categories-all-in-one-form .categories-all-in-one-block-loader').get(0);
      const spinner = $form.find('.categories-all-in-one-form .categories-all-in-one-block-spinner').get(0);

      if (loader) {
        loader.classList.toggle('active');
      }
      if (spinner) {
        spinner.classList.toggle('active');
      }
    }
  };

  const escapeHTML = (str) => {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  };

  const serializeWidgetForm = ($form) => {
    const result = {
      taxonomy: [],
      exclude: []
    };

    $form.find(':input').each(function () {
      const $input = $(this);
      const name = $input.attr('name');
      if (!name) return;

      const type = $input.attr('type');
      const isCheckbox = type === 'checkbox';
      const isChecked = $input.prop('checked');
      const value = isCheckbox ? isChecked : $input.val();

      const match = name.match(/\[([^\[\]]+)\]/g);
      if (!match || match.length < 2) return;

      const keys = match.map((k) => k.replace(/[\[\]]/g, ''));

      const field = keys[1];
      const subfield = keys[2];

      if ((field === 'taxonomy' || field === 'exclude') && isChecked) {
        result[field].push(subfield);
        return;
      }

      if (field !== 'taxonomy' && field !== 'exclude') {
        result[field] = value === 'true' ? true : value === 'false' ? false : value;
      }
    });

    return result;
  };

  const readTree = ($ul) => {
    return $ul
      .children('li')
      .map(function () {
        const $li = $(this);
        const id = parseInt($li.data('term-id'));
        const $subList = $li.find('> div > ul.categories-all-in-one-sortable-item-children-container');

        return {
          term_id: id,
          children: $subList.length ? readTree($subList) : []
        };
      })
      .get();
  };

  const initSortable = ($scope = $(document)) => {
    $scope
      .find('.categories-all-in-one-sortable-container, .categories-all-in-one-sortable-item-children-container')
      .each(function () {
        const $list = $(this);

        if ($list.hasClass('ui-sortable')) {
          $list.sortable('destroy');
        }

        $list.sortable({
          opacity: 1,
          placeholder: 'categories-all-in-one-sortable-item-placeholder',
          update: function () {
            const $rootUL = $list.closest('.categories-all-in-one-sortable-root');
            const newTree = btoa(JSON.stringify(readTree($rootUL)));
            $rootUL
              .closest('form')
              .find('textarea.categories-all-in-one-field-sortable')
              .val(newTree)
              .trigger('change');
          }
        });
      });
  };

  const renderSortableTree = (categories, index = 0) => {
    let html = '';

    if (index === 0) {
      html += '<ul class="categories-all-in-one-sortable-container categories-all-in-one-sortable-root">';
    }

    $.each(categories, function (_, cat) {
      html += '<li data-term-id="' + parseInt(cat.term_id) + '">';
      html += '<div class="categories-all-in-one-sortable-item">';
      html += '<svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" class="sortable-item-handle">';
      html += '<circle cx="5" cy="6" r="2"></circle>';
      html += '<circle cx="5" cy="12" r="2"></circle>';
      html += '<circle cx="5" cy="18" r="2"></circle>';
      html += '<circle cx="13" cy="6" r="2"></circle>';
      html += '<circle cx="13" cy="12" r="2"></circle>';
      html += '<circle cx="13" cy="18" r="2"></circle>';
      html += '</svg>';
      html += cat.name + ' (' + parseInt(cat.count) + ')';

      if (cat.children && cat.children.length > 0) {
        html += '<ul class="categories-all-in-one-sortable-item-children-container">';
        html += renderSortableTree(cat.children, ++index);
        html += '</ul>';
      }

      html += '</div>';
      html += '</li>';
    });

    if (index === 0) {
      html += '</ul>';
    }

    return html;
  };

  const renderExcludeTree = (categories, $form, level = 0) => {
    const widgetNumber = parseInt($form.find('input[name="widget_number"]').val() || 1);

    const excludedItems = $form
      .find('.categories-all-in-one-field-exclude:checked')
      .map(function () {
        return $(this).val();
      })
      .get();

    let html = '';

    if (level === 0) {
      html += `
        <ul class="categories-all-in-one-exclude-container categories-all-in-one-exclude-root categories-all-in-one-tree">
      `;
    }

    categories.forEach((cat) => {
      const termId = parseInt(cat.term_id);
      const escapedName = escapeHTML(cat.name);

      html += `<li key="${termId}">
        <div>
          <label for="category-${termId}">
            <input 
              type="checkbox" 
              name="widget-categories_all_in_one[${widgetNumber}][exclude][${termId}]"
              value="${termId}"
              id="category-${termId}"
              class="checkbox categories-all-in-one-field categories-all-in-one-field-exclude categories-all-in-one-field-request"
              ${excludedItems.includes(String(termId)) ? 'checked' : ''}
            />
            ${escapedName} (${parseInt(cat.count)})
          </label>
        </div>`;

      if (cat.children?.length) {
        html += `<ul class="categories-all-in-one-exclude-item-children-container">
          ${renderExcludeTree(cat.children, $form, level + 1)}
        </ul>`;
      }

      html += `</li>`;
    });

    if (level === 0) {
      html += '</ul>';
    }

    return html;
  };

  $('body').on('change', '.categories-all-in-one-field-request', function () {
    const $form = $(this).closest('form');
    const object = serializeWidgetForm($form);

    toggleLoader($form);

    const $sortableContainer = $form.find('.categories-all-in-one-sortable');
    const $textarea = $form.find('textarea.categories-all-in-one-field-sortable');

    if ($textarea) {
      $textarea.val('').trigger('change');
    }

    if ($(this).hasClass('categories-all-in-one-field-taxonomy')) {
      $form.find('.categories-all-in-one-form .categories-all-in-one-field-post').prop('checked', false);
      $form.find('.categories-all-in-one-form .categories-all-in-one-field-parent').removeAttr('disabled');
    }

    const isPostChecked = $('.categories-all-in-one-form .categories-all-in-one-field-post').is(':checked') || '';

    if (isPostChecked === true) {
      $form.find('.categories-all-in-one-form .categories-all-in-one-field-taxonomy').prop('checked', false);
      $form
        .find('.categories-all-in-one-form .categories-all-in-one-field-parent')
        .empty()
        .attr('disabled', 'disabled');
    }

    const parentField = $form.find('.categories-all-in-one-form .categories-all-in-one-field-parent');

    let params = '';
    const postId = CategoriesAllInOnePluginData.postId ? parseInt(CategoriesAllInOnePluginData.postId) : null;
    if (postId !== null) {
      params = `?post_id=${postId}`;
    }

    const parentCategory =
      $form.find('.categories-all-in-one-field-parent').val() !== ''
        ? parseInt($form.find('.categories-all-in-one-field-parent').val())
        : '';

    $.ajax({
      url: `${CategoriesAllInOnePluginData.apiUrl}/categories/${params}`,
      type: 'POST',
      contentType: 'application/json',
      headers: {
        'X-WP-Nonce': CategoriesAllInOnePluginData.nonce
      },
      data: JSON.stringify(object),
      success: function (response) {
        if (response) {
          const categoriesForSelect = response.categoriesForSelect || [];
          const categoriesForExclude = response.categoriesForExclude || [];
          const categoriesForSortable = response.categoriesForSortable || [];

          if (isPostChecked !== true && categoriesForSelect.length) {
            parentField.empty().removeAttr('disabled');
            $.each(categoriesForSelect, function (i, item) {
              const selected = item.value === parentCategory ? 'selected="selected"' : '';
              const safeValue = escapeHTML(item.value);
              const safeLabel = escapeHTML(item.label);
              parentField.append(`<option ${selected} value="${safeValue}">${safeLabel}</option>`);
            });
          }

          if (categoriesForExclude.length) {
            $form.find('.categories-all-in-one-form .exclude').html(renderExcludeTree(categoriesForExclude, $form));
          } else {
            $form
              .find('.categories-all-in-one-form .exclude')
              .html(escapeHTML(CategoriesAllInOnePluginData.translations.noCategories));
          }

          if (categoriesForSortable.length) {
            $sortableContainer.html(renderSortableTree(categoriesForSortable));
            initSortable($sortableContainer);
          } else {
            $sortableContainer.html(escapeHTML(CategoriesAllInOnePluginData.translations.noCategories));
          }

          $sortableContainer.find('.sortable-container, .children-container').sortable('destroy');

          toggleLoader($form);
        }
      },
      error: function () {
        console.error('API Error');

        toggleLoader($form);
      }
    });
  });

  $(document).on('widget-added widget-updated', function (event, widget) {
    const $widget = $(widget);
    initSortable($widget);
  });

  $('body').on('click', '.categories-all-in-one-insert-shortcode', function (e) {
    e.preventDefault();

    const $form = $(this).closest('form');
    const object = serializeWidgetForm($form);

    let shortcode = '[categories_all_in_one';

    const flattenObject = (obj, prefix = '') => {
      const result = {};
      for (const key in obj) {
        if (!obj.hasOwnProperty(key)) continue;

        const val = obj[key];
        const newKey = prefix ? `${prefix}_${key}` : key;

        if (typeof val === 'object' && val !== null && !Array.isArray(val)) {
          Object.assign(result, flattenObject(val, newKey));
        } else {
          result[newKey] = val;
        }
      }
      return result;
    };

    const flatAttributes = flattenObject(object);

    const taxonomies = [];
    const excludes = [];

    for (const key in flatAttributes) {
      let value = flatAttributes[key];

      if (Array.isArray(value)) value = value.join(',');
      if (typeof value === 'boolean') value = value ? 'true' : 'false';

      if (key.startsWith('exclude_')) {
        excludes.push(key.replace('exclude_', ''));
      } else if (key === 'taxonomy') {
        taxonomies.push(value);
      } else {
        shortcode += ` ${key}="${value}"`;
      }
    }

    if (taxonomies.length) {
      shortcode += ` taxonomy="${taxonomies.join(',')}"`;
    }

    if (excludes.length) {
      shortcode += ` exclude="${excludes.join(',')}"`;
    }

    const $sortableRoot = $form.find('.categories-all-in-one-sortable-root');
    if ($sortableRoot.length) {
      const tree = readTree($sortableRoot);
      const encodedTree = btoa(JSON.stringify(tree));
      shortcode += ` sortable="${encodedTree}"`;
    }

    shortcode += ']';

    if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor) {
      tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
    }

    tb_remove();
  });
});
