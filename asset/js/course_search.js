$(function () {
    var $searchInput = $('#course_search_input');
    var $results = $('#course_search_results');
    var $catalogCards = $('#course_catalog_cards');
    var searchTimer = null;
    var lastQuery = '';

    if ($searchInput.length === 0 || $results.length === 0) {
        return;
    }

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function hideResultsIfNeeded() {
        $results.addClass('null');
    }

    function renderResults(items, query) {
        if (!Array.isArray(items) || items.length === 0) {
            $results.html(
                '<div class="learning-search-empty">' +
                    '<strong>Aucun résultat proche</strong>' +
                    '<span>Essaie avec un autre mot-clé ou une formulation plus large.</span>' +
                '</div>'
            ).removeClass('null');
            return;
        }

        var cardsHtml = '';

        $.each(items, function (_, item) {
            var badge = item.type === 'item' ? 'Contenu' : 'Cours';

            cardsHtml +=
                '<a class="learning-search-result-card" href="' + escapeHtml(item.url) + '">' +
                    '<div class="learning-search-result-card__top">' +
                        '<span class="learning-search-result-card__badge">' + escapeHtml(badge) + '</span>' +
                        '<i class="fa-solid fa-arrow-right"></i>' +
                    '</div>' +
                    '<strong>' + escapeHtml(item.title) + '</strong>' +
                    '<p>' + escapeHtml(item.subtitle) + '</p>' +
                '</a>';
        });

        $results.html(
            '<div class="learning-search-results__head">' +
                '<strong>Résultats suggérés</strong>' +
                '<span>Recherche : ' + escapeHtml(query) + '</span>' +
            '</div>' +
            '<div class="learning-search-results__list">' + cardsHtml + '</div>'
        ).removeClass('null');
    }

    function runSearch() {
        var query = $.trim($searchInput.val());

        if (query === '') {
            lastQuery = '';
            $results.empty().addClass('null');
            $catalogCards.removeClass('learning-catalog-dimmed');
            return;
        }

        lastQuery = query;
        $catalogCards.addClass('learning-catalog-dimmed');

        $results.html(
            '<div class="learning-search-loading">' +
                '<i class="fa-solid fa-spinner rotate"></i>' +
                '<span>Recherche des cours les plus proches...</span>' +
            '</div>'
        ).removeClass('null');

        $.post(
            '/fonctions/course_api.php',
            {
                action: 'search_courses',
                q: query
            },
            function (data) {
                // On ignore les réponses obsolètes si l'utilisateur continue à taper.
                if (lastQuery !== query) {
                    return;
                }

                if (!data || data.result !== 'ok') {
                    $results.html(
                        '<div class="learning-search-empty">' +
                            '<strong>Recherche indisponible</strong>' +
                            '<span>La liste des cours n\'a pas pu être chargée.</span>' +
                        '</div>'
                    ).removeClass('null');
                    return;
                }

                renderResults(data.results || [], query);
            },
            'json'
        ).fail(function () {
            if (lastQuery !== query) {
                return;
            }

            $results.html(
                '<div class="learning-search-empty">' +
                    '<strong>Recherche indisponible</strong>' +
                    '<span>Une erreur est survenue pendant la recherche.</span>' +
                '</div>'
            ).removeClass('null');
        });
    }

    // Temporisation légère pour limiter les requêtes pendant la frappe ou le collage.
    $searchInput.on('input paste', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(runSearch, 180);
    });

    $(document).on('click', function (event) {
        if (!$(event.target).closest('.learning-search-box').length) {
            hideResultsIfNeeded();
        }
    });

    $searchInput.on('focus', function () {
        if ($results.children().length > 0) {
            $results.removeClass('null');
        }
    });
});
