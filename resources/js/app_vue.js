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

import store from './store';
import VueTextareaAutosize from 'vue-textarea-autosize';
import sklonyator from './plugins/sklonyator';
import VModal from 'vue-js-modal';

Vue.use(VModal);
Vue.use(sklonyator);
Vue.use(VueTextareaAutosize);
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

import ChatsList from './components/chat/ChatsList.vue';
import NotificationMessages from './components/notification/NotificationMessages.vue';
import UsersTable from './components/users/UsersTable.vue';
import UserProfileBox from './components/users/UserProfileBox';
import DocumentsTable from './components/documents/DocumentsTable';


Vue.config.devtools = process.env.NODE_ENV === 'development';

let app = new Vue({
    el: '#app',
    store,
    components: {
        ChatsList,
        NotificationMessages,
        UsersTable,
        UserProfileBox,
        DocumentsTable
    }
});
