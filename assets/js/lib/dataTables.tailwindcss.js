/*! DataTables Tailwind CSS integration
 */

(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD
		define(['jquery', 'datatables.net'], function ($) {
			return factory($, window, document);
		});
	}
	else if (typeof exports === 'object') {
		// CommonJS
		var jq = require('jquery');
		var cjsRequires = function (root, $) {
			if (!$.fn.dataTable) {
				require('datatables.net')(root, $);
			}
		};

		if (typeof window === 'undefined') {
			module.exports = function (root, $) {
				if (!root) {
					// CommonJS environments without a window global must pass a
					// root. This will give an error otherwise
					root = window;
				}

				if (!$) {
					$ = jq(root);
				}

				cjsRequires(root, $);
				return factory($, root, root.document);
			};
		}
		else {
			cjsRequires(window, jq);
			module.exports = factory(jq, window, window.document);
		}
	}
	else {
		// Browser
		factory(jQuery, window, document);
	}
}(function ($, window, document) {
	'use strict';
	var DataTable = $.fn.dataTable;



	/*
	 * This is a tech preview of Tailwind CSS integration with DataTables.
	 */

	// Set the defaults for DataTables initialisation
	$.extend(true, DataTable.defaults, {
		renderer: 'tailwindcss'
	});


	// Default class modification
	$.extend(true, DataTable.ext.classes, {
		container: "dt-container dt-tailwindcss justify-between font-['Outfit']",
		search: {
			input: "border text-sm text-gray-900 bg-gray-50 placeholder-gray-500 ml-2 px-3 py-2 rounded-lg border-gray-300 focus:outline-none focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:focus:border-blue-500 dark:focus:ring-blue-500 dark:placeholder-gray-400 dark:text-white"
		},
		length: {
			// select: 'items-center  ',
			select: "border px-5 py-2 rounded-lg text-gray-500 bg-white dark:bg-gray-800 hover:bg-gray-100 font-medium border-gray-300 text-sm dark:text-white dark:hover:bg-gray-700 dark:hover:border-gray-600 focus:outline-none  focus:ring-4 focus:ring-gray-100  dark:focus:ring-gray-700"
		},
		processing: {
			container: "dt-processing"
		},
		paging: {
			active: 'font-semibold bg-gray-100 dark:bg-gray-700',
			notActive: 'bg-white dark:bg-gray-800',
			button: 'relative inline-flex justify-center items-center space-x-2 border px-4 py-2 -mr-px leading-6 hover:z-10 focus:z-10 active:z-10 border-gray-200 active:border-gray-200 active:shadow-none dark:border-gray-700 dark:active:border-gray-700',
			first: 'rounded-l-lg',
			last: 'rounded-r-lg',
			enabled: 'text-gray-800 hover:text-gray-900 hover:border-gray-300 hover:shadow-sm focus:ring focus:ring-gray-300 focus:ring-opacity-25 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-gray-200 dark:focus:ring-gray-600 dark:focus:ring-opacity-40',
			notEnabled: 'text-gray-700 dark:text-gray-300'
		},
		table: 'dataTable w-full rounded-lg overflow-hidden text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 ',
		thead: {
			row: 'border-b border-gray-100 dark:border-gray-700',
			cell: 'px-3 py-4 text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400 '
		},
		tbody: {
			row: 'bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700/50',
			cell: 'p-1'
		},
		tfoot: {
			row: 'font-semibold text-gray-900 dark:text-white',
			cell: 'p-1 text-left'
		},
	});

	DataTable.ext.renderer.pagingButton.tailwindcss = function (settings, buttonType, content, active, disabled) {
		var classes = settings.oClasses.paging;
		var btnClasses = [classes.button];

		btnClasses.push(active ? classes.active : classes.notActive);
		btnClasses.push(disabled ? classes.notEnabled : classes.enabled);

		var a = $('<a>', {
			'href': disabled ? null : '#',
			'class': btnClasses.join(' ')
		})
			.html(content);

		return {
			display: a,
			clicker: a
		};
	};

	DataTable.ext.renderer.pagingContainer.tailwindcss = function (settings, buttonEls) {
		var classes = settings.oClasses.paging;

		buttonEls[0].addClass(classes.first);
		buttonEls[buttonEls.length - 1].addClass(classes.last);

		return $('<ul/>').addClass('pagination').append(buttonEls);
	};

	DataTable.ext.renderer.layout.tailwindcss = function (settings, container, items) {
		var row = $('<div/>', {
			"class": items.full ?
				'grid grid-cols-1 gap-4 mb-4' :
				'grid grid-cols-2 gap-4 mb-4'
		})
			.appendTo(container);

		DataTable.ext.renderer.layout._forLayoutRow(items, function (key, val) {
			var klass;

			// Apply start / end (left / right when ltr) margins
			if (val.table) {
				klass = 'col-span-2';
			}
			else if (key === 'start') {
				klass = 'justify-self-start';
			}
			else if (key === 'end') {
				klass = 'col-start-2 text-right';
			}
			else {
				klass = 'col-span-2 justify-self-center';
			}

			$('<div/>', {
				id: val.id || null,
				"class": klass + ' ' + (val.className || '')
			})
				.append(val.contents)
				.appendTo(row);
		});
	};


	return DataTable;
}));
