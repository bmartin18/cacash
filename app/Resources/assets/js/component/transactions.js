let Transactions = function() {
    "use strict";

    let $container;

    let init = function() {
        $container = $( ".js-transactions" );

        if ( $container.length === 0 ) {
            return;
        }

        let lastRow = 0;

        let dataTable = $container.DataTable( {
            "ajax": $container.data( "list" ),
            "deferRender": true,
            "scroller": true,
            "scrollY": $(window).height() - 340 + "px",
            "scrollCollapse": true,
            "ordering": false,
            "pageLength": 100,
            "select": {
                "style": "os",
                "blurable": true
            },
            "columnDefs": [
                { "className": "hide-on-med-and-down", "targets": [ 1 ] },
                { "className": "center-align hide-on-med-and-down", "targets": [ 3 ] },
                { "className": "amount right-align", "targets": [ 4 ] }
            ],
            "language": {
                "sProcessing":     "Traitement en cours...",
                "sSearch":         "Rechercher",
                "sInfo":           "Transactions _START_ à _END_ sur _TOTAL_",
                "sInfoEmpty":      "",
                "sInfoFiltered":   "",
                "sInfoPostFix":    "",
                "sLoadingRecords": "Chargement en cours...",
                "sZeroRecords":    "Aucun résultat",
                "sEmptyTable":     "Aucune transaction",
                select: {
                    rows: {
                        _: " (%d transactions sélectionnées)",
                        0: "",
                        1: " (1 transaction sélectionnée)"
                    }
                }
            },
            "createdRow": function (row, data) {
                if ( data[4].charAt(0) !== "-" ) {
                    $( row ).addClass( "credit" );
                }
            },
            "initComplete": function(settings, json) {
                lastRow = json.data.length - 1;

                dataTable.row( lastRow ).scrollTo();
            }
        } );

        $( "#search" ).keyup( function() {
            dataTable.search( $( this ).val() ).draw();

            if ( $( this ).val() === "" ) {
                dataTable.row( lastRow ).scrollTo( false );
            }
        });
    };

    $( function() {
        init();
    } );
};

module.exports = new Transactions();
