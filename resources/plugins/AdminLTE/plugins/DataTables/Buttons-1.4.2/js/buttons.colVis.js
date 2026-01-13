/*!
 * Column visibility buttons for Buttons and DataTables.
 * 2016 SpryMedia Ltd - datatables.net/license
 */

(function( factory ){
	if ( typeof define === 'function' && define.amd ) {
		// AMD
		define( ['jquery', 'datatables.net', 'datatables.net-buttons'], function ( $ ) {
			return factory( $, window, document );
		} );
	}
	else if ( typeof exports === 'object' ) {
		// CommonJS
		module.exports = function (root, $) {
			if ( ! root ) {
				root = window;
			}

			if ( ! $ || ! $.fn.dataTable ) {
				$ = require('datatables.net')(root, $).$;
			}

			if ( ! $.fn.dataTable.Buttons ) {
				require('datatables.net-buttons')(root, $);
			}

			return factory( $, root, root.document );
		};
	}
	else {
		// Browser
		factory( jQuery, window, document );
	}
}(function( $, window, document, undefined ) {
'use strict';
var DataTable = $.fn.dataTable;

// Store column visibility settings in local storage
function storeColumnVisibility(settings) {
    localStorage.setItem('columnVisibilitySettings', JSON.stringify(settings));
}

// Retrieve column visibility settings from local storage
function getColumnVisibility() {
    const settings = localStorage.getItem('columnVisibilitySettings');
    return settings ? JSON.parse(settings) : null;
}

// Apply stored visibility settings on table initialization
$(document).ready(function() {
    var table = $('#example').DataTable();
    var storedSettings = getColumnVisibility();

    if (storedSettings) {
        storedSettings.forEach(function(setting, index) {
            table.column(index).visible(setting);
        });
    }

    // Update visibility settings on column visibility change
    table.on('column-visibility.dt', function(e, settings, column, state) {
        var visibilitySettings = [];
        table.columns().every(function() {
            visibilitySettings.push(this.visible());
        });
        storeColumnVisibility(visibilitySettings);
    });
});

$.extend( DataTable.ext.buttons, {
	colvis: function ( dt, conf ) {
		return {
			extend: 'collection',
			text: function ( dt ) {
				return dt.i18n( 'buttons.colvis', 'Column visibility' );
			},
			className: 'buttons-colvis',
			buttons: [ {
				extend: 'columnsToggle',
				columns: conf.columns,
				columnText: conf.columnText
			} ]
		};
	}
});

return DataTable.Buttons;
}));
