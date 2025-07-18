document.addEventListener('DOMContentLoaded', function () {
    if (window.flatpickr) {
        flatpickr("#start", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true
        });
        flatpickr("#end", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true
        });
    }

    if (window.jQuery) {
        jQuery('#products').select2({
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
            placeholder: jQuery('#products').data('placeholder') || 'Velg produkter',
            minimumInputLength: 1,
            width: '100%',
            language: {
                searching: function() {
                    return 'Laster produkter...';
                },
                inputTooShort: function() {
                    return 'Skriv inn minst én bokstav for å søke';
                },
                errorLoading: function() {
                    return 'Kunne ikke laste produkter';
                }
            }
        });

        jQuery('#categories').select2({
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
            placeholder: jQuery('#categories').data('placeholder') || 'Velg kategorier',
            minimumInputLength: 1,
            width: '100%',
            language: {
                searching: function() {
                    return 'Laster kategorier...';
                },
                inputTooShort: function() {
                    return 'Skriv inn minst én bokstav for å søke';
                },
                errorLoading: function() {
                    return 'Kunne ikke laste kategorier';
                }
            }
        });
    }
});
