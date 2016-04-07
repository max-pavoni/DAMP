$(document).ready(function () {


    // QUESTO METODO VIENE INVOCATO OGNI VOLTA CHE INSERISCI UN CARATTERE NELL'INPUT
    // OGNI VOLTA CHE VIENE INVOCATO CHIAMA LA ACTION PREDICTION.PHP CHE RITORNA IL RISULTATO DELLA QUERY SUGGEST
    // ALLA FINE IL RISULTATO VIENE MOSTRATO NELLA PAGINA HTML



    $('#search').keyup(function() {

        $.get('actions/prediction.php', 'q=' + $(this).val(), function(data) {


            var opzioni = JSON.parse(data);


            $( "#search" ).autocomplete({
                source: opzioni,
                minLength :0,
                appendTo: '#input-container',
                select: function(event, ui) {
                    if(ui.item){
                        $('#search').val(ui.item.value);
                    }
                    $('#search-form').submit();
                }
            });
            $( "#search" ).autocomplete("search", "");

        });
    });

});
