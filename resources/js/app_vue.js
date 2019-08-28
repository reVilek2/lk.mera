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
window.Noty = require('noty');

import store from './store';
import VueTextareaAutosize from 'vue-textarea-autosize';
import sklonyator from './plugins/sklonyator';
import VModal from 'vue-js-modal';

Vue.use(VModal, { dynamic: true });
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

import NotificationMessages from './components/notification/NotificationMessages';
import NotificationServiceMessages from './components/notification/NotificationServiceMessages';
import NotificationRecommendations from './components/notification/NotificationRecommendations'
import NotificationDocuments from './components/notification/NotificationDocuments'
import UsersTable from './components/users/UsersTable';
import UserFilesTable from './components/users/UserFilesTable';
import UserProfileBox from './components/users/UserProfileBox';
import UserProfileTabs from './components/users/UserProfileTabs';
import DocumentsTable from './components/documents/DocumentsTable';
import VueStoreData from './components/VueStoreData';
import UserBalanceMenu from './components/users/UserBalanceMenu';
import UserAccountMenu from './components/users/UserAccountMenu';
import UserSidebarPanel from './components/users/UserSidebarPanel';
import PaymentService from './components/payments/PaymentService';
import FinanceService from './components/finances/FinanceService';
import Chat from './components/chat/Chat';
import RecommendationsList from './components/recommendations/RecommendationsList';

Vue.config.devtools = process.env.NODE_ENV === 'development';

let app = new Vue({
    el: '#app',
    store,
    components: {
        VueStoreData,
        UserBalanceMenu,
        UserAccountMenu,
        UserSidebarPanel,
        Chat,
        NotificationMessages,
        NotificationServiceMessages,
        NotificationRecommendations,
        NotificationDocuments,
        UsersTable,
        UserFilesTable,
        UserProfileBox,
        UserProfileTabs,
        DocumentsTable,
        PaymentService,
        FinanceService,
        RecommendationsList,
    }
});
