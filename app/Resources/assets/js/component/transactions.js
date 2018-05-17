let $ = require( "jquery" );

let Transactions = function() {
    "use strict";

    let $container;

    let init = function() {
        $container = $( ".js-transactions" );

        if ( $container.length === 0 ) {
            return;
        }

        resizeTable();

        $( window ).resize( function() {
            resizeTable();
        } );
    };

    let resizeTable = function() {
        let height = $(window).height() - 235;

        $( "tbody", $container ).css( "height", height + "px");
    };

    $( function() {
        init();
    } );
};

module.exports = new Transactions();
