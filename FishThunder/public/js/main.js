$.fn.resetInput = function() {
    this.map(function() {
        switch (this.type) {
            case 'password':
            case 'text':
            case 'textarea':
            case 'file':
            case 'select-one':
            case 'select-multiple':
                jQuery(this).val('');
                break;
            case 'checkbox':
            case 'radio':
                this.checked = false;
                break;
        }
    });
};

function userModeChange() {
    var $controlBounceback = jQuery(".control_bounceback_status, .control_bounceback_limit, .control_bounceback_params");
    switch (jQuery(this).val()) {
        case 'sweepstakes':
            $controlBounceback.show();
            break;
        default:
            $controlBounceback.hide().find(':input').resetInput();
    }
}

$(document).ready(function() {
    $("form").one('submit', function(e) {
        $(this).find(":submit").prop("disabled", true);
        return true;
    });

    $(".pin-code").one('click', function() {
        $(this).removeAttr('role').text($(this).data('pin-code'));
    });
});

$(document).ready(function() {
    var $checks = $('input[type="checkbox"][data-single]');
    $checks.click(function() {
        $checks.filter('[data-single="' + $(this).attr('data-single') + '"]').not(this).prop("checked", false);
    });
});

$(document).on('click', '.copy-to-clipboard-btn', function(e) {
    const el = document.createElement('textarea');
    el.value = $(this).data('text');
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    $(this).attr('title', 'Copied').tooltip('show');
    window.setTimeout(function() {
        $(this).attr('title', null).tooltip('destroy')
    }.bind(this), 500);
});