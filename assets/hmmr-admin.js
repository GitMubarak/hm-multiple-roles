(function($) {

    $(document).ready(function() {

        var row = $('select#role').closest('tr');
        var clone = row.clone();
        // clone.insertAfter( $('select#role').closest('tr') );
        row.html($('.hmmr-roles-container tr').html());
        $('.hmmr-roles-container').remove();

        $('input[name="hmmr_user_roles_general"]').change(function() {
            var checkedValue = $('input:checkbox:checked').map(function() {
                return this.value;
            }).get();
            $('#hmmr_user_roles_general').val(checkedValue);
        });

        // in general options
        //$('select#default_role').closest('tr').remove();
    })

})(jQuery);