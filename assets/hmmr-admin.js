(function($) {

    $(document).ready(function() {

        var row = $('select#role').closest('tr');
        var clone = row.clone();
        // clone.insertAfter( $('select#role').closest('tr') );
        row.html($('.hmmr-roles-container tr').html());
        $('.hmmr-roles-container').remove()
    })

})(jQuery);