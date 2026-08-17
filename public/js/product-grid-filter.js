(function ($) {
    'use strict';

    window.initProductGridFilter = function (options) {
        options = $.extend({
            accentClass: 'btn-primary',
            skipEnterOnKeyup: false,
        }, options);

        var selectors = {
            search: '#filter_search',
            categoryBtn: '.filter-btn',
            productItem: '.product-item',
            noResults: '#no_results_msg',
        };

        function filterProducts(category, search) {
            var totalVisible = 0;

            search = (search || '').toLowerCase();

            $(selectors.productItem).each(function () {
                var matchesCategory = (category === 'all' || $(this).attr('data-category') == category);
                var matchesSearch = (search === '' || String($(this).attr('data-search') || '').includes(search));

                if (matchesCategory && matchesSearch) {
                    $(this).show();
                    totalVisible++;
                } else {
                    $(this).hide();
                }
            });

            $(selectors.noResults).toggle(totalVisible === 0);

            return totalVisible;
        }

        window.filterProducts = filterProducts;

        $(document).on('click', selectors.categoryBtn, function () {
            var accent = options.accentClass;

            $(selectors.categoryBtn)
                .removeClass(accent + ' active')
                .addClass('filter-btn--idle');

            $(this)
                .removeClass('filter-btn--idle')
                .addClass(accent + ' active');

            filterProducts($(this).attr('data-filter'), $(selectors.search).val().toLowerCase());
        });

        $(selectors.search).on('keyup', function (e) {
            if (options.skipEnterOnKeyup && e.key === 'Enter') {
                return;
            }

            filterProducts(
                $(selectors.categoryBtn + '.active').attr('data-filter') || 'all',
                $(this).val().toLowerCase()
            );
        });

        return {
            filterProducts: filterProducts,
        };
    };
})(jQuery);
