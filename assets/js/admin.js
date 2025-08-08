/**
 * BD Dynamic Pricing Pro - Modern Admin JavaScript
 * Enhanced with BD Design System interactions
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize date pickers with modern styling
    if (window.flatpickr) {
        flatpickr("#start", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            theme: "light",
            locale: {
                firstDayOfWeek: 1
            }
        });
        flatpickr("#end", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            theme: "light",
            locale: {
                firstDayOfWeek: 1
            }
        });
    }

    // Initialize Select2 with modern styling
    if (window.jQuery) {
        const $ = jQuery;
        
        // Products Select2
        $('#products').select2({
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        action: 'bd_dp_search_products',
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            },
            placeholder: $('#products').data('placeholder') || 'Velg produkter',
            minimumInputLength: 1,
            width: '100%',
            language: {
                searching: function() {
                    return '🔍 Laster produkter...';
                },
                inputTooShort: function() {
                    return 'Skriv inn minst én bokstav for å søke';
                },
                errorLoading: function() {
                    return '❌ Kunne ikke laste produkter';
                },
                noResults: function() {
                    return 'Ingen produkter funnet';
                }
            }
        });

        // Categories Select2
        $('#categories').select2({
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        action: 'bd_dp_search_categories',
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            },
            placeholder: $('#categories').data('placeholder') || 'Velg kategorier',
            minimumInputLength: 1,
            width: '100%',
            language: {
                searching: function() {
                    return '🔍 Laster kategorier...';
                },
                inputTooShort: function() {
                    return 'Skriv inn minst én bokstav for å søke';
                },
                errorLoading: function() {
                    return '❌ Kunne ikke laste kategorier';
                },
                noResults: function() {
                    return 'Ingen kategorier funnet';
                }
            }
        });

        // Modern BD Design System Enhancements
        $(document).ready(function() {
            
            // Tab switching with smooth animations
            $('.nav-tab').click(function(e) {
                e.preventDefault();
                var target = $(this).data('tab');
                
                // Update active tab with animation
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                // Fade out current content
                $('.tab-content.active').fadeOut(200, function() {
                    $(this).removeClass('active');
                    // Fade in new content
                    $('#' + target).addClass('active').fadeIn(300);
                });
            });

            // Smooth form submission with loading states
            $("form").submit(function() {
                var $button = $(this).find(".button-primary");
                var $form = $(this);
                
                // Add loading state
                $button.addClass("bd-loading").prop("disabled", true);
                
                var originalText = $button.text();
                $button.text("💾 Lagrer...");
                
                // Add loading animation to form
                $form.css('opacity', '0.8');
                
                // Reset after 3 seconds if no page reload
                setTimeout(function() {
                    $button.removeClass("bd-loading").prop("disabled", false);
                    $button.text(originalText);
                    $form.css('opacity', '1');
                }, 3000);
            });
            
            // Auto-hide success messages with fade effect
            setTimeout(function() {
                $(".notice.updated, .bd-success-notice").fadeOut(500);
            }, 5000);
            
            // Enhanced hover effects for cards
            $(".bd-campaign-card, .bd-help-card").hover(
                function() { 
                    $(this).css("transform", "translateY(-8px)"); 
                },
                function() { 
                    $(this).css("transform", "translateY(0)"); 
                }
            );
            
            // Smooth scrolling for anchor links
            $("a[href^='#']").click(function(e) {
                var target = $(this.hash);
                if (target.length) {
                    e.preventDefault();
                    $("html, body").animate({
                        scrollTop: target.offset().top - 32
                    }, 500);
                }
            });
            
            // Form validation with visual feedback
            $("input[required], textarea[required], select[required]").blur(function() {
                var $field = $(this);
                var value = $field.val();
                
                if (value === '' || value === null) {
                    $field.css({
                        'border-color': '#ef4444',
                        'box-shadow': '0 0 0 3px rgba(239, 68, 68, 0.1)'
                    });
                } else {
                    $field.css({
                        'border-color': '#10b981',
                        'box-shadow': '0 0 0 3px rgba(16, 185, 129, 0.1)'
                    });
                }
            });
            
            // Reset field styling on focus
            $("input, textarea, select").focus(function() {
                $(this).css({
                    'border-color': '#667eea',
                    'box-shadow': '0 0 0 3px rgba(102, 126, 234, 0.1)'
                });
            });
            
            // Tooltip functionality for help text
            $('[data-tooltip]').hover(function() {
                var tooltip = $(this).data('tooltip');
                var $tooltip = $('<div class="bd-tooltip">' + tooltip + '</div>');
                
                $tooltip.css({
                    position: 'absolute',
                    background: 'linear-gradient(135deg, #1f2937 0%, #374151 100%)',
                    color: 'white',
                    padding: '8px 12px',
                    borderRadius: '6px',
                    fontSize: '12px',
                    fontWeight: '500',
                    zIndex: '9999',
                    boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                    opacity: '0',
                    transform: 'translateY(5px)',
                    transition: 'all 0.2s ease'
                });
                
                $('body').append($tooltip);
                
                // Position tooltip
                var offset = $(this).offset();
                $tooltip.css({
                    top: offset.top - $tooltip.outerHeight() - 8,
                    left: offset.left + ($(this).outerWidth() / 2) - ($tooltip.outerWidth() / 2)
                });
                
                // Animate in
                setTimeout(function() {
                    $tooltip.css({
                        opacity: '1',
                        transform: 'translateY(0)'
                    });
                }, 10);
                
            }, function() {
                $('.bd-tooltip').remove();
            });
            
            // Confirm delete with modern modal-style confirm
            $('.bd-delete-btn').click(function(e) {
                e.preventDefault();
                var href = $(this).attr('href');
                var campaignName = $(this).closest('.bd-campaign-card').find('h4').text().replace(/^[^\s]+\s/, ''); // Remove emoji
                
                if (confirm('🗑️ Er du sikker på at du vil slette kampanjen "' + campaignName + '"?\n\nDenne handlingen kan ikke angres.')) {
                    // Add loading state to the card
                    $(this).closest('.bd-campaign-card').addClass('bd-loading');
                    window.location.href = href;
                }
            });
            
            // Dynamic rule type help text
            $('#rule_type').change(function() {
                var ruleType = $(this).val();
                var helpText = '';
                var emoji = '';
                
                switch(ruleType) {
                    case 'percent':
                        emoji = '📊';
                        helpText = 'Prosentvis rabatt: Angi prosent (f.eks. 20 for 20% rabatt)';
                        break;
                    case 'fixed':
                        emoji = '💵';
                        helpText = 'Fast rabatt: Angi beløp i kroner (f.eks. 100 for 100 kr rabatt)';
                        break;
                    case '3for2':
                        emoji = '🎁';
                        helpText = '3 for 2: Kunden betaler for 2 produkter og får det tredje gratis';
                        break;
                    case 'bulk':
                        emoji = '📦';
                        helpText = 'Mengderabatt: Angi minimum antall for å utløse rabatten';
                        break;
                }
                
                // Remove existing help text
                $('#rule_type').next('.bd-rule-help').remove();
                
                // Add new help text
                if (helpText) {
                    var $helpDiv = $('<div class="bd-rule-help" style="margin-top: 8px; padding: 12px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 6px; border-left: 3px solid #0ea5e9; font-size: 13px; color: #0369a1;">' + emoji + ' ' + helpText + '</div>');
                    $('#rule_type').after($helpDiv);
                    
                    // Animate in
                    $helpDiv.css({opacity: 0, transform: 'translateY(-10px)'});
                    setTimeout(function() {
                        $helpDiv.css({
                            opacity: 1,
                            transform: 'translateY(0)',
                            transition: 'all 0.3s ease'
                        });
                    }, 10);
                }
            });
            
            // Trigger rule type help on page load if editing
            if ($('#rule_type').val()) {
                $('#rule_type').trigger('change');
            }
            
            // Add loading animation to AJAX calls
            $(document).ajaxStart(function() {
                $('.select2-selection').addClass('bd-loading');
            }).ajaxStop(function() {
                $('.select2-selection').removeClass('bd-loading');
            });
            
            // Keyboard shortcuts
            $(document).keydown(function(e) {
                // Ctrl/Cmd + S to save form
                if ((e.ctrlKey || e.metaKey) && e.which === 83) {
                    e.preventDefault();
                    $('form').first().submit();
                }
                
                // Escape to cancel editing
                if (e.which === 27 && window.location.href.indexOf('edit=') > -1) {
                    if (confirm('❌ Vil du avbryte redigeringen og gå tilbake til oversikten?')) {
                        window.location.href = window.location.href.split('&edit=')[0].split('?edit=')[0];
                    }
                }
            });
            
            // Add visual feedback for successful actions
            if (window.location.href.indexOf('updated') > -1 || $('.notice.updated').length > 0) {
                // Add success animation
                $('body').prepend('<div class="bd-success-overlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(16, 185, 129, 0.1); z-index: 9998; pointer-events: none; opacity: 0;"></div>');
                $('.bd-success-overlay').animate({opacity: 1}, 200).delay(800).animate({opacity: 0}, 400, function() {
                    $(this).remove();
                });
            }
            
            // Initialize any existing campaigns with fade-in animation
            $('.bd-campaign-card').each(function(index) {
                $(this).css({
                    opacity: 0,
                    transform: 'translateY(20px)'
                });
                
                setTimeout(() => {
                    $(this).css({
                        opacity: 1,
                        transform: 'translateY(0)',
                        transition: 'all 0.4s ease'
                    });
                }, index * 100);
            });
        });
    }
});

// Global utility functions for BD Dynamic Pricing
window.BDDynamicPricing = {
    // Show notification
    showNotification: function(message, type = 'success') {
        const emoji = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
        const bgColor = type === 'success' ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 
                       type === 'error' ? 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)' : 
                       'linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%)';
        
        const notification = jQuery('<div class="bd-notification" style="position: fixed; top: 20px; right: 20px; background: ' + bgColor + '; color: white; padding: 15px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 9999; font-weight: 500; opacity: 0; transform: translateX(100px);">' + emoji + ' ' + message + '</div>');
        
        jQuery('body').append(notification);
        
        // Animate in
        notification.animate({
            opacity: 1,
            transform: 'translateX(0)'
        }, 300);
        
        // Auto remove
        setTimeout(function() {
            notification.animate({
                opacity: 0,
                transform: 'translateX(100px)'
            }, 300, function() {
                notification.remove();
            });
        }, 4000);
    },
    
    // Smooth scroll to element
    scrollTo: function(element) {
        jQuery('html, body').animate({
            scrollTop: jQuery(element).offset().top - 32
        }, 500);
    }
};
