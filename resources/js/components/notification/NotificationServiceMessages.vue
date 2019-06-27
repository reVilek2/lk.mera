<template>
    <li class="dropdown notifications-menu" @click="markNotificationAsRead">
        <!-- Menu toggle button -->
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-bell-o"></i>
            <span v-if="un_read_count > 0" class="label label-warning">{{un_read_count}}</span>
        </a>
        <ul class="dropdown-menu">
            <li v-if="message_count > 0" class="header">У вас {{message_count}} {{ $sklonyator(message_count, ['уведомление', 'уведомления', 'уведомлений']) }}</li>
            <li v-else class="header">У вас нет новых уведомлений</li>
            <li>
                <!-- inner menu: contains the messages -->
                <ul v-if="notifyMessages.length > 0" class="menu">
                    <notification-service-messages-item v-for="notifyMessage in notifyMessages" :message="notifyMessage" :key="notifyMessage.id"></notification-service-messages-item>
                </ul>
                <!-- /.menu -->
            </li>
        </ul>
    </li>
</template>

<script>
    import { mapGetters } from 'vuex';
    import NotificationServiceMessagesItem from './NotificationServiceMessagesItem';
    export default {
        props: {
            notificationServiceTextMessages: {
                type: Array,
                default: () => []
            },
        },
        data: function() {
            return {
                notifyMessages: this.notificationServiceTextMessages,
                un_read_count: this.notificationServiceTextMessages.length,
                message_count: this.notificationServiceTextMessages.length,
            }
        },
        components: {NotificationServiceMessagesItem},
        computed: {
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
        methods: {
            markNotificationAsRead() {
                if (this.notifyMessages.length) {
                    axios.get('/service-text-notification/mark-as-read').then(response => {
                        if (response.data.status === 'success') {
                            this.un_read_count = 0;
                        }
                    });
                }
            }
        },
        mounted() {
            window.Echo.private('service.notification.user.'+this.currUser.id).notification((notification) => {
                if (notification.type === 'App\\Notifications\\ServiceTextNotification') {
                    let newNotification = {
                        data: {message: notification.message},
                    };
                    this.notifyMessages.push(newNotification);
                    this.un_read_count = this.un_read_count + 1;
                    this.message_count = this.notifyMessages.length;
                }
            });
        }
    }
</script>
