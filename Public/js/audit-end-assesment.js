$(document).ready(function () {

    const questionClass = '.audit-question-container-row';

    function remove_empty_container(remove = true)
    {
        $('.ans-given-question-tr').addClass('d-none');

        if($('.ans-given-question-tr').length > 0)
        {
            $('.ans-given-question-tr').each(function() {
                
                var $agqt = $(this);

                if($agqt.attr('data-acc') !== undefined)
                    $('.' + $agqt.attr('data-acc')).addClass('d-none');                
            });
        }

        if($('.header-tr').length > 0)
        {
            $('.header-tr').each(function() {
                var $header = $(this);
                var hasVisibleQuestions = false;
                
                $header.nextUntil('.header-tr, .category-tr, .menu-tr').each(function() {
                    if ($(this).hasClass('question-ans-tr') && !$(this).hasClass('d-none')) {
                        hasVisibleQuestions = true;
                        return false;
                    }
                });
                if (!hasVisibleQuestions) {
                    $header.addClass('d-none');
                    $header.nextUntil('.header-tr, .category-tr, .menu-tr').filter('.header-thtr').addClass('d-none');
                }
            });
        }

        if($('.category-tr').length > 0)
        {
            $('.category-tr').each(function() {
                var $category = $(this);
                var hasVisibleHeaders = $category.nextUntil('.category-tr, .menu-tr').is('.header-tr:not(.d-none)') || $category.nextUntil('.category-tr, .menu-tr').is('.acc-tr:not(.d-none)');

                if (!hasVisibleHeaders) {
                    $category.addClass('d-none');
                }
            });
        }

        if($('.menu-tr').length > 0)
        {
            $('.menu-tr').each(function() {
                var $menu = $(this);
                var hasVisibleCategories = $menu.nextUntil('.menu-tr').is('.category-tr:not(.d-none)');
                if (!hasVisibleCategories) {
                    $menu.addClass('d-none');
                }
            });
        }
    }

    if( $('#showPending').length > 0 )
    {
        $('#showPending').on('click', function() {
            
            let allQuestionsDangerLength = $(questionClass + '.text-danger').length;

            if(!allQuestionsDangerLength > 0)
            {
                alert('No pending audit points founds!');
                return;
            }

            $( questionClass + ':not(.text-danger)' ).addClass('d-none');

            // function call
            remove_empty_container();

            alert('Filter applied!');

        });
    }

    if( $('#showAll').length > 0 )
    {
        $('#showAll').on('click', function() {
            $('#content').find('.d-none').removeClass('d-none');
            alert('Filter removed applied!');
        });
    }

});