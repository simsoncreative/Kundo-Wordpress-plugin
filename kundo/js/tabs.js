jQuery().ready(function() {
    jQuery("#tabs").tabs({
        ajaxOptions: {
            error: function(xhr, status, index, anchor) {
                jQuery(anchor.hash).html("Kunde inte ladda informationen...");
            }
        },
		select: function(event, ui) { 
            jQuery('#loading').toggle();
        },

        load: function(event, ui) { 
            jQuery('#loading').toggle();
        },

		fx: { opacity: 'toggle' },
        
    });
});