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

import Vue from 'vue';
import Echo from "laravel-echo";
window.Pusher = require('pusher-js');


window.Echo = new Echo({
    broadcaster: 'pusher',
    key: Laravel.pusherKey,
    cluster: Laravel.pusherCluster,
    encrypted: true
});

import ChatMessages from './components/ChatMessages.vue';
import ChatForm from './components/ChatForm.vue';
Vue.component('chat-messages', ChatMessages);
Vue.component('chat-form', ChatForm);

// const app = new Vue({
//     el: '#app',
//
//     data: {
//         messages: []
//     },
//
//     created() {
//         this.fetchMessages();
//
//         window.Echo.private('chat')
//             .listen('MessageSent', (e) => {
//                 this.messages.push({
//                     message: e.message.message,
//                     user: e.user
//                 });
//             });
//     },
//
//     methods: {
//         fetchMessages() {
//             console.log('tyt');
//             axios.get('/messages').then(response => {
//                 this.messages = response.data;
//             });
//         },
//         addMessage(message) {
//             this.messages.push(message);
//
//             axios.post('/messages', message).then(response => {});
//         }
//     }
// });

window.Noty = require('noty');

$(function () {
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

    window.Echo.private('chat.'+Laravel.chatId).listen('MessageSent', (e) => {
        let js_chat = $('#js-chat-'+e.sender.id);
        let $chat_list = $('#js-chat-list', js_chat);
        if ($chat_list.length) {
            $chat_list.append(e.message_html);
            $chat_list.stop().animate({
                scrollTop: $chat_list[0].scrollHeight
            }, 800);
        }
    });

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
                        console.log(jqXHR.responseText);
                        // let response = $.parseJSON(jqXHR.responseText);

                    }
                });
            });
        }
        return {
            init: function () {
                let $load_user_chat = $('.js-load-user-chat');
                let $container_messages = $('.js-chat-messages');

                $load_user_chat.on('click', function (e) {
                    e.preventDefault();
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    let _href = $(this).attr('href');

                    $.ajax({
                        type: 'get',
                        url: _href,
                        dataType: 'json',
                        success: function (response) {
                            if (response.html) {
                                $container_messages.html(response.html);
                                let $chat_list = $('#js-chat-list');
                                $chat_list.stop().animate({
                                    scrollTop: $chat_list[0].scrollHeight
                                }, 0);
                                initChatMethods();
                            }
                        },
                        error: function (jqXHR) {
                            console.log(jqXHR.responseText);
                            // let response = $.parseJSON(jqXHR.responseText);

                        }
                    });
                });

            }
        }
    })();
    Chat.init();

    // $('#talkSendMessage').on('submit', function(e) {
    //     e.preventDefault();
    //     var url, request, tag, data;
    //     tag = $(this);
    //     url = __baseUrl + '/ajax/message/send';
    //     data = tag.serialize();
    //
    //     request = $.ajax({
    //         method: "post",
    //         url: url,
    //         data: data
    //     });
    //
    //     request.done(function (response) {
    //         if (response.status == 'success') {
    //             $('#talkMessages').append(response.html);
    //             tag[0].reset();
    //         }
    //     });
    //
    // });
});
