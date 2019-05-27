import Echo from "laravel-echo";
window.Pusher = require('pusher-js');
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: Laravel.pusherKey,
    cluster: Laravel.pusherCluster,
    encrypted: true
});

import Vue from 'vue';
window.axios = require('axios');
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.Laravel.csrfToken;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import sklonyator from './plugins/sklonyator'
Vue.use(sklonyator);
Vue.directive('scroll', {
    inserted: function(el, binding) {
        let f = function(evt) {
            if (binding.value(evt, el)) {
                window.removeEventListener('scroll', f);
            }
        };
        window.addEventListener('scroll', f);
    },
});

import ChatsList from './components/ChatsList.vue';
import NotificationMessages from './components/NotificationMessages.vue';

new Vue({
    el: '#app',
    components: {
        ChatsList,
        NotificationMessages
    },
    data: {},
    mounted() {
        // window.Echo.private('chat.'+Laravel.chatId).notification((notification) => {
        //     if (notification.type === 'App\\Notifications\\MessageSentNotification') {
        //         console.log(notification);
        //         this.notificationsMessage.push({
        //             ...notification
        //         });
        //     }
        // });
    },
});