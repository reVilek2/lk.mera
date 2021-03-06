window._ = require('lodash');
window.$ = window.jQuery = require('jquery');
require('admin-lte/bower_components/bootstrap/dist/js/bootstrap');
require('admin-lte/dist/js/adminlte');


/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */
window.axios = require('axios');

window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.Laravel.csrfToken;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


import './app_vue';

window.Noty = require('noty');

$(function () {
    // const checkout = YandexCheckout('611290');
    // checkout.tokenize({
    //     number: '5555555555554444',
    //     cvc: '111',
    //     month: '10',
    //     year: '22'
    // }).then(res => {
    //     if (res.status === 'success') {
    //         const { paymentToken } = res.data.response;
    //
    //         return paymentToken;
    //     }
    //     console.log(res);
    // });




    // auto update token
    let lifetime_csrf = $('meta[name="csrf-token"]').attr('data-lifetime');
    if (lifetime_csrf) {
        setInterval(refreshCsrf, (lifetime_csrf * 60 * 1000) - (1000*60)); //минус 1 минута
        function refreshCsrf() {
            let $meta_csrf = $('meta[name="csrf-token"]');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $meta_csrf.attr('content')
                }
            });
            $.ajax({
                url: '/refresh-csrf',
                type: 'post',
            }).then(function (result) {
                $meta_csrf.attr('content', result);
            }).fail(function(){
                console.log('Error: refresh-csrf');
            });
        }
    }

    $('.js-noty').each(function () {
        let noty = $(this);

        new Noty({
            type: noty.data('type') || 'info',
            text: noty.data('text'),
            layout: 'topRight',
            timeout: 5000,
            progressBar: true,
            theme: 'metroui',
        }).show();
    });

    let $input_avatar = $('.js-input-avatar');
    if ($input_avatar.length) {
        $input_avatar.change(function () {
            $(this).parents("form")[0].submit();
        });
    }



    function updateChatList(notification) {
        let js_chat = $('#js-chat-'+notification.sender.id);
        let $chat_list = $('#js-chat-list', js_chat);
        if ($chat_list.length) {
            $chat_list.append(notification.message_html);
            $chat_list.stop().animate({
                scrollTop: $chat_list[0].scrollHeight
            }, 800);
        }
    }

    let Chat = (function () {
        function initChatMethods() {
            let $form_sender = $('#js-chat-send-message');
            let $chat_list = $('#js-chat-list');

            $form_sender.on('submit', function(e) {
                e.preventDefault();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                let form = $(this);
                let data = form.serialize();

                $.ajax({
                    type: 'post',
                    url: '/message/send',
                    dataType: 'json',
                    data: data,
                    success: function (response) {
                        if (response.status === 'success') {
                            $chat_list.append(response.html);
                            $chat_list.stop().animate({
                                scrollTop: $chat_list[0].scrollHeight
                            }, 800);
                            form[0].reset();
                        }
                    },
                    error: function (jqXHR) {
                        // console.log(jqXHR.responseText);
                        // let response = $.parseJSON(jqXHR.responseText);

                    }
                });
            });
        }
        return {
            init: function () {
                // let $load_user_chat = $('.js-load-user-chat');
                // let $container_messages = $('.js-chat-messages');
                //
                // $load_user_chat.on('click', function (e) {
                //     e.preventDefault();
                //     //снимаем выделение
                //     $load_user_chat.parent().removeClass('active');
                //     //выделяем активный чат
                //     $(this).parent().addClass('active');
                //
                //     $.ajaxSetup({
                //         headers: {
                //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                //         }
                //     });
                //     let _href = $(this).attr('href');
                //     let chat_id = $(this).attr('data-chat');

                    // $.ajax({
                    //     type: 'get',
                    //     url: _href,
                    //     dataType: 'json',
                    //     success: function (response) {
                    //         if (response.html) {
                    //             $container_messages.html(response.html);
                    //             let $chat_list = $('#js-chat-list');
                    //             $chat_list.stop().animate({
                    //                 scrollTop: $chat_list[0].scrollHeight
                    //             }, 0);
                    //             initChatMethods();
                    //             // set active chat
                    //             window.location.hash = "chat:"+chat_id;
                    //         }
                    //     },
                    //     error: function (jqXHR) {
                    //         console.log(jqXHR.responseText);
                    //         // let response = $.parseJSON(jqXHR.responseText);
                    //
                    //     }
                    // });
                // });

                // let chat_id = location.hash.replace(/^#chat:/, '');
                // if (chat_id) {
                    //если есть параметр активного чата то запускаем его
                    // let active_user_chat = $('.js-load-user-chat[data-chat="'+chat_id+'"]');
                    // active_user_chat.parent().addClass('active');
                    // active_user_chat.click();
                // }
            }
        }
    })();
    Chat.init();
});
