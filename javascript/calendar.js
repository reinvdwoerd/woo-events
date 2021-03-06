jQuery(function ($) {
    /**
     * Select Days, Show/Hide Event Tickets
     */
    $('.events .event').addClass('hidden')
    $('.events .event').each(function (i, el) {
        var startDate = $(el).data('start-date')
        var day = $('.day[data-start-date="' + startDate + '"]')
        day.addClass('has-event')
    })

    $(document).on('click touchstart', '.day', function () {
        var startDate = $(this).data('start-date')
        $('.day').removeClass('active')
        $(this).addClass('active')
        $('.events .event').addClass('hidden')
        $('.events .event[data-start-date="' + startDate + '"]').removeClass('hidden')
    })

    /**
     * Next/Previous Month
     */
    $(document).on('click', '#calendar-wrap #next', function () {
        $('.month.active:not(:last-child)')
            .toggleClass('active', false)
            .next()
            .toggleClass('active', true)
    })

    $(document).on('click', '#calendar-wrap #previous', function () {
        $('.month.active:not(:first-child)')
            .toggleClass('active', false)
            .prev()
            .toggleClass('active', true)
    })

    /**
     * Start with current month
     */
    var now = new Date()
    var day = pad(now.getDate())
    var month = pad(now.getMonth() + 1)
    var year = now.getFullYear()

    $('[data-month=' + month + ']' + '[data-year=' + year + ']')
        .toggleClass('active', true)

    /**
     * Highlight today
     */
    var today = year + '-' + month + '-' + day
    $('[data-start-date=' + today + ']').addClass('today')
});


function pad(num) {
    var s = "0" + num;
    return s.substr(s.length - 2);
}