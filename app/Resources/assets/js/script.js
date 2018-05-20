let $ = require( "jquery" );

let hammerjs = require( "hammerjs" );
let materialize = require( "materialize-css" );

let dt = require( "datatables.net" )( window, $ );
require( "datatables.net-scroller" )( window, $ );
require( "datatables.net-select" )( window, $ );

require("./component/transactions");

$( document ).ready( function() {
    $( ".dropdown-button" ).dropdown();
    $( ".modal" ).modal();

    $( ".datepicker" ).pickadate({
        selectMonths: true, // Creates a dropdown to control month
        selectYears: 15, // Creates a dropdown of 15 years to control year,
        today: "Aujourd'hui",
        clear: "Effacer",
        close: "OK",
        closeOnSelect: false, // Close upon selecting a date,
        container: undefined, // ex. 'body' will append picker to body
        monthsFull: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        monthsShort: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        weekdaysShort: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
        weekdaysLetter: ["D","L", "M", "M", "J", "V", "S"],
        format: 'dd/mm/yyyy',
    });
} );
