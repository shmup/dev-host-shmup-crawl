/* globals */
var rows = 9;
var cols = 29;
var rowId = 1;
var startingX = Math.round(rows/2);
var startingY = Math.round(cols/2);

/*
    y k u
    h + l
    b j n
*/

var keys = {
    /* h */ '72': [ 0, -1, 'h'],
    /* l */ '76': [ 0,  1, 'l'],
    /* k */ '75': [-1,  0, 'k'],
    /* j */ '74': [ 1,  0, 'j'],
    /* y */ '89': [-1, -1, 'y'],
    /* n */ '78': [ 1,  1, 'n'],
    /* u */ '85': [-1,  1, 'u'],
    /* b */ '66': [ 1, -1, 'b']
};

var marker_location = function() {
    var row = $('.marker').closest('tr').attr('id');
    var col = $('.marker').attr('class').split(' ')[0];
    return [parseInt(row, 10), parseInt(col, 10)];
};

var formatCordString = function(coords) {
    cleanCoords = '#' + coords[0] + '>.' + coords[1];
    return cleanCoords;
};

var addLetters = function(l) {
    var x, y, k;

    for (var key in keys) {
        k = keys[key];
        x = parseInt(k[0], 10) + l[0];
        y = parseInt(k[1], 10) + l[1];
        cleanString = formatCordString([x,y]);
        if ($(cleanString).length) {
            $(cleanString).html(k[2]);
        }
    }
};

var set_marker = function(coords) {
    $('.marker').removeClass('marker');
    $('#'+coords[0]+'>.'+coords[1]).addClass('marker');
    $('td').html('');
    addLetters(coords);
};

var check_marker = function(movement) {
    var coords = marker_location();
    var newX = coords[0] + movement[0];
    var newY = coords[1] + movement[1];
    if (newX > rows || newX === 0) { return (false); }
    if (newY > cols || newY === 0) { return (false); }
    set_marker([coords[0] + movement[0], coords[1] + movement[1]]);
};

$(document).ready(function(){
    var table = $('<table id="grid"><tbody>');

    for(var r = 0; r < rows; r++)
    {
        var tr = $('<tr id=' + rowId + '>');
        rowId++;

        var colId = 1;

        for (var c = 0; c < cols; c++) {
            $('<td class=' + colId + '></td>').appendTo(tr);
            colId++;
        }
        tr.appendTo(table);
    }

    table.appendTo('#board');

    addLetters([startingX, startingY]);

    $('#'+startingX+'>.'+startingY).addClass('marker');
});

$(document).keydown(function(e, tot){
    for (var key in keys) {
        if (e.keyCode == key) {
            check_marker(keys[key]);
            return false;
        }
    }
});