/**
 * Read information from a column of select (drop down) menus and return an
 * array to use as a basis for sorting.
 *
 *  @summary Sort based on the value of the `dt-tag select` options in a column
 *  @name Select menu data source
 *  @requires DataTables 1.10+
 *  @author [Allan Jardine](http://sprymedia.co.uk)
 */

$.fn.dataTable.ext.order['dom-select'] = function(settings, col) {
    return this.api().column(col, {order: 'index'}).nodes().map(function(td, i) {
        return $('select', td).val();
    });
};

// Custom order for the team dropdown
var teamOrder = {
    "Unsorted": 0,
    "Baratheon": 1,
    "Lannister": 2,
    "Martell": 3,
    "Stark": 4
};
$.fn.dataTable.ext.order['team-select'] = function(settings, col) {
    return this.api().column(col, {order: 'index'}).nodes().map(function(td, i) {
        return teamOrder[$('select', td).attr('value')];
    });
};

// Custom order for badge cells
var badgeOrder = {
    "Basic": 0,
    "Premium Add-on": 1,
    "Premium": 2,
    "Premium Replacement": 3
};
$.fn.dataTable.ext.order['badge-cell'] = function(settings, col) {
    return this.api().column(col, {order: 'index'}).nodes().map(function(td, i) {
        return badgeOrder[$(td).text()];
    });
};