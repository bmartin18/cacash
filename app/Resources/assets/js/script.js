let $ = require( "jquery" );

let hammerjs = require( "hammerjs" );
let Materialize = require( "materialize-css" );

let dt = require( "datatables.net" )( window, $ );
require( "datatables.net-scroller" )( window, $ );
require( "datatables.net-select" )( window, $ );
require( "datatables.net-buttons" )( window, $ );

require( "howler" );

require("./component/transactions");

$( document ).ready( function() {
    $( ".dropdown-button" ).dropdown();
} );
