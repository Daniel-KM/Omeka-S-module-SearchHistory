(function() {
    $(document).ready(function() {

        var hasOmeka = typeof Omeka !== 'undefined';
        var advancedSearchPopUp = $('.advanced-search-menu');

        var advancedSearchSave = $('.save-search-button');
        var saveSearchPopUp = $('.popup-search-save');
        var saveSearchPopUpBackground = $('.popup-background');
        var saveSearchButton = $('.popup-search-save input[type=submit]');
        var saveSearchName = $('.popup-search-save input[type=text]');
        var deleteSearchButton = $('.search-history-delete');

        function centerPopUpVertically(element) {
            var browserHeight = window.innerHeight || document.body.clientHeight;
            var top = Math.max( 20, ( browserHeight - element.innerHeight() ) * 0.5 - 50);
            element.css('top', top + 'px');
        }

        var openAdvancedSearchPopUp = function() {
            advancedSearchPopUp.removeClass('menu-closed');
            $('body').addClass('with-advanced-search-on-top');
            centerPopUpVertically($('.advanced-search-menu-panel'));
        };

        var closeAdvancedSearchPopUp = function() {
            advancedSearchPopUp.removeClass('menu-closed').addClass('menu-closed');
            $('body').removeClass('with-advanced-search-on-top');
        };

        $('.popup-header-close-button').on('click', function(e){
            e.preventDefault();
            e.stopPropagation();
            $('.popup').removeClass('is-opening');
        });

        // Open popup to save search.
        advancedSearchSave.on('click', function(e){
            e.preventDefault();
            e.stopPropagation();

            var query = window.location.search;
            if (!query) {
                var msg = 'No query is set.';
                alert(hasOmeka ? Omeka.jsTranslate(msg) : msg);
                return;
            }

            closeAdvancedSearchPopUp();
            saveSearchName.val('');
            saveSearchPopUp.removeClass('with-message');
            saveSearchPopUp.addClass('is-opening');
        });

        // Save search request.
        saveSearchButton.on('click', function(e){
            e.preventDefault();
            e.stopPropagation();

            var messageHtml;

            var query = window.location.search.substring(1);
            if (query) {
                $.ajax({
                    url: advancedSearchSave.attr('href'),
                    data: {
                        comment: $('input[name=search-request-comment]').val(),
                        engine: advancedSearchSave.data('engine'),
                        query: query,
                    },
                })
                .done(function(data) {
                    if (data.status === 'success') {
                        $(advancedSearchSave).addClass('hidden');
                        $(deleteSearchButton).closest('.search-request').removeClass('hidden');
                        $(deleteSearchButton).attr('href', data.data.url_delete);
                    }
                });

                messageHtml = hasOmeka
                    ? Omeka.jsTranslate('Your search is saved.') + '<br/>' + Omeka.jsTranslate('You can find it in your account.')
                    : 'Your search is saved.' + '<br/>' + 'You can find it in your account.';
                $('.popup-message', popup).html(messageHtml);
            }

            var popup = $(e.target).closest('.popup');
            popup.addClass('with-message');
            setTimeout( function() {
                saveSearchPopUp.removeClass('is-opening');
                if ( $('.filters-and-results-panel').length === 0 ) {
                    openAdvancedSearchPopUp();
                }
            }, 2000 );
        });

        // Delete search request.
        deleteSearchButton.on('click', function(e){
            e.preventDefault();
            e.stopPropagation();

            $.ajax(deleteSearchButton.attr('href'))
            .done(function(data) {
                if (data.status === 'success') {
                    $(deleteSearchButton).closest('.search-request').addClass('hidden');
                    $(advancedSearchSave).removeClass('hidden');
                }
            });
        });

    });
})();
