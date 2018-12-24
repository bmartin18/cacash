let Transactions = function() {
    "use strict";

    let $container;

    let init = function() {
        $container = $( ".js-transactions" );

        if ( $container.length === 0 ) {
            return;
        }

        let lastRow = 0;

        let table = $container.DataTable( {
            "dom": 'tiB',
            "ajax": $container.data( "list" ),
            "deferRender": true,
            "scroller": true,
            "scrollY": $( window ).height() - 340 + "px",
            "scrollCollapse": true,
            "pageLength": 100,
            "columns": [
                { "data": "transactionAt" },
                { "data": "hash" },
                { "data": "description" },
                { "data": "checked" },
                { "data": "debit" },
                { "data": "credit" },
                { "data": "id" },
                { "data": "timestamp" }
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
                    "action": function () {
                        $.get( $( "#modal" ).data( "create" ), function( form ) {
                            initFormTransaction( form );
                        });
                    }
                },
                {
                    "text": "<i class=\"material-icons left\">edit</i><span class='hide-on-med-and-down'>Modifier</span>",
                    "className": "btn button-edit",
                    "enabled": false,
                    "action": function () {
                        let id = table.rows( { selected: true } ).data()[0][ "id" ];

                        $.get( $( "#modal" ).data( "edit" ) + "/" + id, function( html ) {
                            initFormTransaction( html );
                        });
                    }
                },
                {
                    "text": "<i class=\"material-icons left\">check</i><span class='hide-on-med-and-down'><u>P</u>ointer</span>",
                    "className": "btn hide-on-med-and-down",
                    "enabled": false,
                    "key": {
                        "key": "p"
                    },
                    "action": function () {
                        let data = table.rows( { selected: true } ).data();

                        $( data ).each( function() {
                            $.get( $container.data( "check" ) + "/" + this[ "id" ], function( json ) {
                                updateRow( json );
                            });
                        } );

                        if ( $container.hasClass( "fart" ) ) {
                            fart.play( "fart-" + Math.floor( ( Math.random() * 3 ) + 1 ) );
                        }
                    }
                },
                {
                    "text": "<i class=\"material-icons left\">delete</i><span class='hide-on-med-and-down'>Supprimer</span>",
                    "className": "btn button-delete right red hide-on-med-and-down",
                    "enabled": false,
                    "action": function () {
                        let data = table.rows( { selected: true } ).data();

                        $( data ).each( function() {
                            $.get( $container.data( "delete" ) + "/" + this[ "id" ], function( json ) {
                                if ( json.success ) {
                                    updateRow( json );
                                }
                            } );
                        } );

                        table.rows().deselect();
                    }
                }
            ],
            "columnDefs": [
                { "className": "hide-on-med-and-down", "targets": [ 1 ] },
                { "className": "center-align hide-on-med-and-down", "targets": [ 3 ] },
                { "className": "right-align", "targets": [ 4, 5 ] },
                { "className": "hide", "targets": [ 6, 7 ] },
                {
                    targets: [ 0 ],
                    orderData: [ 7, 6 ]
                }
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
                $( row ).data( "timestamp", data.timestamp );
            },
            "initComplete": function(settings, json) {
                lastRow = json.data.length - 1;

                table.row( lastRow ).scrollTo();
            }
        } );

        let xhr = false;

        let updateRow = function( json ) {
            if ( json.success ) {
                let row =  $( "#" + json.id );

                if ( json.data ) {
                    if ( row.length > 0 ) {
                        table.row( row ).data( json.data ).select().draw( false );
                    } else {
                        table.row.add( json.data ).select().draw( false ).scrollTo();
                    }
                } else {
                    table.row( row ).remove().draw( false );
                }

                if ( json.balance ) {
                    $( ".balance" ).html( json.balance );
                }

                if (xhr) {
                    clearTimeout(xhr);
                }

                xhr = setTimeout(function() {
                    $.get( $container.data( "create" ));
                }, 3000);
            }
        };

        let $search = $( "#search" );

        $search.keyup( function() {
            search();
        });

        let search = function () {
            table.search( $search.val() ).draw();

            if ( $search.val() === "" ) {
                table.row( lastRow ).scrollTo( false );
            }
        };

        let countSelectedRows = 0;

        table.on( "select deselect", function () {
            countSelectedRows = table.rows( { selected: true } ).count();

            table.button( 1 ).enable( countSelectedRows === 1 );
            table.button( 2 ).enable( countSelectedRows > 0 );
            table.button( 3 ).enable( countSelectedRows > 0 );
        } );

        $( window ).keydown( function( e ) {
            if ( e.keyCode === 13 ) {
                table.button( ".button-edit" ).trigger();
                table.rows().deselect();

                return;
            }

            if ( e.keyCode === 46 || e.keyCode === 8 ) {
                table.button( ".button-delete" ).trigger();

                return;
            }

            if ( e.keyCode === 27 ) {
                if ( $search.val() !== "" ) {
                    table.rows().deselect();

                    $("#search").val("");
                    $search.blur();
                    search();
                }

                return;
            }

            if ( e.keyCode === 38 ||  e.keyCode === 40 ) {
                let page = table.scroller.page();
                let rows = table.rows( { selected: true } )[0];
                let row = 0;

                if ( e.keyCode === 38 ) {
                    row = parseInt(rows[0]) - 1;
                }

                if ( e.keyCode === 40 ) {
                    row = parseInt(rows[rows.length - 1]) + 1;
                }

                e.preventDefault();

                table.rows().deselect();
                table.row( row ).select();

                if ( row - 3 < page.start ) {
                    table.row( row - 3 ).scrollTo( false );
                }

                if ( row > page.end - 3 ) {
                    table.row( page.start + 1 ).scrollTo( false );
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

            $.get( $( ".autocomplete" ).data( "autocomplete" ), function( autocomplete ) {
                $('.autocomplete').autocomplete({
                    data: autocomplete,
                    limit: 5,
                    minLength: 3,
                });
            });

            $( "form[name='transaction']" ).submit(function(e) {
                e.preventDefault();

                $.post( $( this ).data( "action" ), $( this ).serialize(), function( json ) {
                    if ( json.success ) {
                        updateRow( json );
                        $( "#modal-transaction" ).modal( "close" );
                    }
                }, 'JSON');
            });

            Materialize.updateTextFields();

            $( ".modal" ).modal();
            $( "#modal-transaction" ).modal( "open" );
            $( "select" ).material_select();
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
