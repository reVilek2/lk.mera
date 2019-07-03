window.$ = window.jQuery = require('jquery');
require('bootstrap');


/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */
window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

$(function () {
    $('.js-resend-phone-code').on('click', function (e) {
        e.preventDefault();
        let _this = $(this);
        let _href = _this.attr('href');
        if(!_this.hasClass('in-progress')) {
            _this.addClass('in-progress');
            axios.post(_href).then(response => {
                _this.removeClass('in-progress');
                location.reload();
            }).catch(errors => {
                _this.removeClass('in-progress');
                console.log(errors);
            });
        }
    });
    $('.js-resend-email-code').on('click', function (e) {
        e.preventDefault();
        let _this = $(this);
        let container = _this.parent('#resend-link');
        let invalid_feedback = container.find('.invalid-feedback');
        let valid_feedback = container.find('.valid-feedback');
        let preloader = container.find('.js-preloader');

        if(!_this.hasClass('in-progress')) {

            invalid_feedback.removeClass('d-block').html('');
            valid_feedback.removeClass('d-block').html('');
            _this.hide();
            _this.addClass('in-progress');
            preloader.show();

            axios.post(_this.attr('href')).then(response => {
                _this.show();
                _this.removeClass('in-progress');
                preloader.hide();
                if (response.data.status === 'success') {
                    valid_feedback.addClass('d-block').html('Письмо отправлено.');
                }
                if (response.data.status === 'exception') {
                    invalid_feedback.addClass('d-block').html(response.data.message);
                }
            }).catch(errors => {
                _this.show();
                _this.removeClass('in-progress');
                preloader.hide();
                console.log(errors);
            });
        }
    });
});

