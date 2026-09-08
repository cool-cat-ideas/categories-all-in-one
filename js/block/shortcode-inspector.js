(function (wp) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;

	wp.hooks.addFilter(
		'editor.BlockEdit',
		'categories-all-in-one/shortcode-inspector',
		function (BlockEdit) {
			return function CategoriesShortcodeInspector(props) {
				if (props.name !== 'categories-all-in-one/block') {
					return el(BlockEdit, props);
				}

				// Reuse the block serializer so the displayed code matches saved content.
				var attributes = {};
				var defaults = window.CategoriesAllInOnePluginData.dictionaries.defaults;
				Object.keys(defaults).forEach(function (name) {
					if (Object.prototype.hasOwnProperty.call(props.attributes, name)) {
						attributes[name] = props.attributes[name];
					}
				});
				var shortcode = wp.blocks.getSaveElement(props.name, attributes);
				var labels = window.CategoriesAllInOnePluginData.translations;

				return el(
					Fragment,
					null,
					el(BlockEdit, props),
					props.isSelected && el(
						wp.blockEditor.InspectorControls,
						null,
						el(
							wp.components.PanelBody,
							{ title: labels.shortcodeLabel, initialOpen: true },
							el(wp.components.TextareaControl, {
								label: labels.shortcodeLabel,
								help: labels.shortcodeHelp,
								value: typeof shortcode === 'string' ? shortcode : '',
								readOnly: true,
								rows: 6,
								onFocus: function (event) { event.target.select(); },
							})
						)
					)
				);
			};
		}
	);
})(window.wp);