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
            "dom": 'tiB',
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
            "buttons": [
                {
                    "text": "<i class=\"material-icons left\">add</i>Transaction",
                    "className": "btn",
                    "action": function ( e, dt, node, config ) {
                        $.get( $( "#modal" ).data( "create" ), function(response) {
                            initFormTransaction( response );
                        });
                    }
                },
                {
                    "text": "<i class=\"material-icons left\">edit</i>Modifier",
                    "className": "btn",
                    "enabled": false,
                    "action": function ( e, dt, node, config ) {
                        let id = dataTable.rows( { selected: true } ).data()[0][5];

                        $.get( $( "#modal" ).data( "edit" ) + "/" + id, function(response) {
                            initFormTransaction( response );
                        });
                    }
                },
                {
                    "text": "<i class=\"material-icons left\">check</i>Pointer",
                    "className": "btn",
                    "enabled": false,
                    "action": function ( e, dt, node, config ) {

                    }
                }
            ],
            "columnDefs": [
                { "className": "hide-on-med-and-down", "targets": [ 1 ] },
                { "className": "center-align hide-on-med-and-down", "targets": [ 3 ] },
                { "className": "amount right-align", "targets": [ 4 ] },
                { "className": "hide", "targets": [ 5 ] }
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

        let countSelectedRows = 0;

        dataTable.on( "select deselect", function () {
            countSelectedRows = dataTable.rows( { selected: true } ).count();

            dataTable.button( 1 ).enable( countSelectedRows === 1 );
            dataTable.button( 2 ).enable( countSelectedRows > 0 );
        } );

        let initFormTransaction = function( form ) {
            $( "#modal" ).html( form );

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

            $( "form[name='transaction']" ).submit(function(e) {
                e.preventDefault();

                $.post( $( this ).data( "action" ), $( this ).serialize(), function(response) {
                    if (response.success) {
                        dataTable.ajax.reload(null, false);
                        $( "#modal-transaction" ).modal( "close" );
                    }
                }, 'JSON');
            });

            Materialize.updateTextFields();

            $( ".modal" ).modal();
            $( "#modal-transaction" ).modal( "open" );
        };
    };

    $( function() {
        init();
    } );
};

module.exports = new Transactions();