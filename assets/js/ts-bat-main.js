(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        var noticeBoard = $('#ts_bat_notice_holder');
        
        noticeBoard.hide();
        noticeBoard.css( 'visibility', 'visible' );
        
        $('#submit_bulk_terms').click(function() {
            
            if( $('input[name="ts_bat_taxonomy_select"]:checked').length < 1 ) {
                alert( locale_strings.notax );
                return;
            }
            
            if( !$('#bulk_term_input').val() ) {
                alert( locale_strings.noterm );
                return;
            }
            
            var securityCheck = $('#ts_bat_add_terms_ajax_security').val(),
                selTax = $('input[name="ts_bat_taxonomy_select"]:checked').val(),
                input = $('#bulk_term_input').val();
            
            if( $('input[name="ts_bat_taxonomy_select"]:checked').length > 1 ) {
                selTax = [];
                $('input[name="ts_bat_taxonomy_select"]:checked').each(function() {
                    var val = $(this).val();
                    selTax.push(val);
                });
            }
            
            if( !confirm( locale_strings.confirm ) ) {
                return;
            }
            
            $.ajax({
                url: ajaxurl,
				method: 'POST',
                data: {
                    'action': 'ts_bat_add_new_terms',
                    'security': securityCheck,
                    'taxonomy': selTax,
                    'terms': input
                },
                success: function() {
                    
                    if( !$('#bulk_term_input').hasClass('keep-txt') ) {
                        $('#bulk_term_input').val('');
                    }
                    
                    noticeBoard.children('span').html( locale_strings.success );
                    
                    noticeBoard.fadeIn();
                    
                    setTimeout(function() {
                        noticeBoard.fadeOut();
                    }, 3500);
                    
                },
                error: function() {
                    
                    noticeBoard.children('span').html( locale_strings.failed );
                    
                    noticeBoard.fadeIn();
                    
                    setTimeout(function() {
                        noticeBoard.fadeOut();
                    }, 3500);
                    
                }
            });
            
        });
        
        $('#get_bulk_terms').click(function() {
            
            if( $('input[name="ts_bat_taxonomy_select"]:checked').length < 1 ) {
                alert( locale_strings.notax );
                return;
            }
            
            var securityCheck = $('#ts_bat_add_terms_ajax_security').val(),
                selTax = $('input[name="ts_bat_taxonomy_select"]:checked').val();
            
            if( $('input[name="ts_bat_taxonomy_select"]:checked').length > 1 ) {
                alert( locale_strings.selonetax );
				return;
            }
            
            $.ajax({
                url: ajaxurl,
				method: 'POST',
                data: {
                    'action': 'ts_bat_show_the_terms',
                    'security': securityCheck,
                    'taxonomy': selTax
                },
                success: function(data) {
					
					$('#bulk_term_input').val(data.data);
                    
                },
                error: function() {
                    
                    noticeBoard.children('span').html( locale_strings.failed );
                    
                    noticeBoard.fadeIn();
                    
                    setTimeout(function() {
                        noticeBoard.fadeOut();
                    }, 3500);
                    
                }
            });
            
        });
        
        $('#remove_bulk_terms').click(function() {
            
            if( $('input[name="ts_bat_taxonomy_select"]:checked').length < 1 ) {
                alert( locale_strings.notax );
                return;
            }
            
            var securityCheck = $('#ts_bat_add_terms_ajax_security').val(),
                selTax = $('input[name="ts_bat_taxonomy_select"]:checked').val();
            
            if( $('input[name="ts_bat_taxonomy_select"]:checked').length > 1 ) {
                alert( locale_strings.selonetax );
				return;
            }
            
            if( !confirm( locale_strings.confirmrem ) ) {
                return;
            }
            
            $.ajax({
                url: ajaxurl,
				method: 'POST',
                data: {
                    'action': 'ts_bat_remove_the_terms',
                    'security': securityCheck,
                    'taxonomy': selTax
                },
                success: function(data) {
                    
                    noticeBoard.children('span').html( locale_strings.successdel );
                    
                    noticeBoard.fadeIn();
                    
                    setTimeout(function() {
                        noticeBoard.fadeOut();
                    }, 3500);
                    
                },
                error: function() {
                    
                    noticeBoard.children('span').html( locale_strings.failed );
                    
                    noticeBoard.fadeIn();
                    
                    setTimeout(function() {
                        noticeBoard.fadeOut();
                    }, 3500);
                    
                }
            });
            
        });
        
        $('#reset_bulk_terms').click(function() {
            $('#bulk_term_input').val('');
        });
        
    });
    
})(jQuery);

// foo|baz|ki[as]|kick[lope|ax[ac[vd]]]