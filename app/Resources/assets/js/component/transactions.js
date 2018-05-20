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
            "scrollY": $(window).height() - 330 + "px",
            "scrollCollapse": true,
            "ordering": false,
            "pageLength": 100,
            "columns": [
                { "data": "transactionAt" },
                { "data": "hash" },
                { "data": "description" },
                { "data": "checked" },
                { "data": "amount" }
            ],
            "rowId": "id",
            "select": {
                "style": "os",
                "blurable": true
            },
            "buttons": [
                {
                    "text": "<i class=\"material-icons left\">add</i><span class='hide-on-med-and-down'><u>N</u>ouveau</span>",
                    "className": "btn",
                    "key": {
                        "key": "n"
                    },
                    "action": function ( e, dt, node, config ) {
                        $.get( $( "#modal" ).data( "create" ), function(response) {
                            initFormTransaction( response );
                        });
                    }
                },
                {
                    "text": "<i class=\"material-icons left\">edit</i><span class='hide-on-med-and-down'>Modifier</span>",
                    "className": "btn button-edit",
                    "enabled": false,
                    "action": function ( e, dt, node, config ) {
                        let id = dataTable.rows( { selected: true } ).data()[0][ "id" ];

                        $.get( $( "#modal" ).data( "edit" ) + "/" + id, function(response) {
                            initFormTransaction( response );
                        });
                    }
                },
                {
                    "text": "<i class=\"material-icons left\">check</i><span class='hide-on-med-and-down'><u>P</u>ointer</span>",
                    "className": "btn",
                    "enabled": false,
                    "key": {
                        "key": "p"
                    },
                    "action": function ( e, dt, node, config ) {
                        let data = dataTable.rows( { selected: true } ).data();

                        $( data ).each( function() {
                            $.get( $container.data( "check" ) + "/" + this[ "id" ], function() {
                                reloadDataTable();
                            });
                        } );

                        if ( $container.hasClass( "fart" ) ) {
                            fart.play( "fart-" + Math.floor( ( Math.random() * 3 ) + 1 ) );
                        }
                    }
                },
                {
                    "text": "<i class=\"material-icons left\">delete</i><span class='hide-on-med-and-down'>Supprimer</span>",
                    "className": "btn button-delete right red",
                    "enabled": false,
                    "action": function ( e, dt, node, config ) {
                        let data = dataTable.rows( { selected: true } ).data();

                        $( data ).each( function() {
                            $.get( $container.data( "delete" ) + "/" + this[ "id" ], function() {
                                reloadDataTable();
                            });
                        } );
                    }
                }
            ],
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
                "select": {
                    "rows": {
                        _: " (%d transactions sélectionnées)",
                        0: "",
                        1: " (1 transaction sélectionnée)"
                    }
                }
            },
            "createdRow": function (row, data) {
                if ( data[ "amount" ].charAt(0) !== "-" ) {
                    $( row ).addClass( "credit" );
                }
            },
            "initComplete": function(settings, json) {
                lastRow = json.data.length - 1;

                dataTable.row( lastRow ).scrollTo();
            }
        } );

        let reloadDataTable = function() {
            dataTable.ajax.reload(function ( json ) {
                $( ".balance" ).html( json.balance );
            }, false);
        };

        let $search = $( "#search" );

        $search.keyup( function() {
            search();
        });

        let search = function () {
            dataTable.search( $search.val() ).draw();

            if ( $search.val() === "" ) {
                dataTable.row( lastRow ).scrollTo( false );
            }
        };

        let countSelectedRows = 0;

        dataTable.on( "select deselect", function () {
            countSelectedRows = dataTable.rows( { selected: true } ).count();

            dataTable.button( 1 ).enable( countSelectedRows === 1 );
            dataTable.button( 2 ).enable( countSelectedRows > 0 );
            dataTable.button( 3 ).enable( countSelectedRows > 0 );
        } );

        $( window ).keydown( function( e ) {
            if ( e.keyCode === 13 ) {
                dataTable.button( ".button-edit" ).trigger();
                dataTable.rows().deselect();

                return;
            }

            if ( e.keyCode === 46 || e.keyCode === 8 ) {
                dataTable.button( ".button-delete" ).trigger();

                return;
            }

            if ( e.keyCode === 27 ) {
                dataTable.rows().deselect();

                $( "#search" ).val( "" );
                $search.blur();
                search();

                return;
            }

            if ( e.keyCode === 38 ||  e.keyCode === 40 ) {
                let page = dataTable.scroller.page();
                let rows = dataTable.rows( { selected: true } )[0];
                let row = 0;

                if ( e.keyCode === 38 ) {
                    row = parseInt(rows[0]) - 1;
                }

                if ( e.keyCode === 40 ) {
                    row = parseInt(rows[rows.length - 1]) + 1;
                }

                e.preventDefault();

                dataTable.rows().deselect();
                dataTable.row(row).select();

                if (row - 3 < page.start ) {
                    dataTable.row(row - 3).scrollTo(false);
                }

                if (row > page.end - 3) {
                    dataTable.row(page.start + 1).scrollTo(false);
                }
            }
        } );

        $( window ).keyup( function( e ) {
            if ( e.keyCode === 82 ) {
                if ( $( "input:focus" ).length > 0 ) {
                    return;
                }

                $search.focus();
            }
        } );

        let initFormTransaction = function( form ) {
            $( "#modal" ).html( form );

            $( ".datepicker" ).pickadate({
                selectMonths: true,
                selectYears: 15,
                today: "Aujourd'hui",
                clear: "Effacer",
                close: "OK",
                closeOnSelect: false,
                container: undefined,
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
                        reloadDataTable();
                        $( "#modal-transaction" ).modal( "close" );
                    }
                }, 'JSON');
            });

            Materialize.updateTextFields();

            $( ".modal" ).modal();
            $( "#modal-transaction" ).modal( "open" );
            $( "#transaction_description" ).focus();
        };

        let fart = new Howl({
            src: [$container.data("fart") + ".ogg", $(this).data("fart") + ".mp3"],
            sprite: {
                "fart-1": [0, 549],
                "fart-2": [549, 679],
                "fart-3": [1228, 340]
            }
        });
    };

    $( function() {
        init();
    } );
};

module.exports = new Transactions();
