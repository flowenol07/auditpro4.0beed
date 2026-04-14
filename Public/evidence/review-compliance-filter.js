$(document).ready(function () {

    const targetStatus = 3;
    let showingAll = false;

    function filterCompliance() {
        $('.reviewer-action .form-select').each(function () {
            const $select = $(this);
            const selectedVal = parseInt($select.val());
            const $questionRow = $select.closest('tr');

            const $blockRows = $questionRow.nextUntil('.question-ans-tr, .header-tr, .header-thtr');

            if (selectedVal === targetStatus) {
                $questionRow.removeClass('d-none');
                $blockRows.removeClass('d-none');
            } else {
                $questionRow.addClass('d-none');
                $blockRows.addClass('d-none');
            }
        });

        removeEmptyContainers();
    }

//     function removeEmptyContainers() {
//         // Hide headers if no visible question rows
//         $('.header-tr').each(function () {
//             const $header = $(this);
//             const $block = $header.nextUntil('.header-tr, .category-tr, .menu-tr');

// const hasVisibleQuestions =
//     $block.filter('tr:not(.header-thtr):not(.d-none)').length > 0;

//             if (!hasVisibleQuestions) {
//                 $header.addClass('d-none');
//                 $block.filter('.header-thtr').addClass('d-none'); // hide sub-header row like # Question | Audit Point
//             } else {
//                 $header.removeClass('d-none');
//             }
//         });

//         // Hide empty categories
//         $('.category-tr').each(function () {
//             const $category = $(this);
//             const hasVisibleHeaders = $category.nextUntil('.category-tr, .menu-tr')
//                 .filter('.header-tr:not(.d-none), .acc-tr:not(.d-none)').length > 0;

//             if (!hasVisibleHeaders) {
//                 $category.addClass('d-none');
//             } else {
//                 $category.removeClass('d-none');
//             }
//         });

//         // Hide empty menus
//         $('.menu-tr').each(function () {
//             const $menu = $(this);
//             const hasVisibleCategories = $menu.nextUntil('.menu-tr')
//                 .filter('.category-tr:not(.d-none)').length > 0;

//             if (!hasVisibleCategories) {
//                 $menu.addClass('d-none');
//             } else {
//                 $menu.removeClass('d-none');
//             }
//         });
//     }

//     // Default filter on load
//     filterCompliance();

    // Toggle all
    // $(document).on('click', '#toggleAllCompliance', function () {
    //     if (showingAll) {
    //         filterCompliance();
    //         $(this).text('Show All Compliance Points');
    //         showingAll = false;
    //     } else {
    //         $('#content').find('.d-none').removeClass('d-none');
    //         $(this).text('Show RE COMPLIANCE');
    //         showingAll = true;
    //     }
    // });
});
