<template>
    <li class="dropdown messages-menu" @click="markNotificationAsRead">
        <a v-if="isMobile" :href="allMessagesUrl">
            <span class="footer-item-icon">
                <i class="fa fa-comments">
                    <span v-if="un_read_count > 0" class="label label-warning">{{un_read_count}}</span>
                </i>
            </span>
            <span class="footer-item-title">Чат</span>
        </a>

        <!-- Menu toggle button -->
        <a v-if="!isMobile" href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-comments"></i>
            <span v-if="un_read_count > 0" class="label label-success">{{un_read_count}}</span>
        </a>
        <ul v-if="!isMobile" class="dropdown-menu">
            <li v-if="message_count > 0" class="header">У вас {{message_count}} {{ $sklonyator(message_count, ['сообщение', 'сообщения', 'сообщений']) }}</li>
            <li v-else class="header">У вас нет новых сообщений</li>
            <li>
                <!-- inner menu: contains the messages -->
                <ul v-if="notifyMessages.length > 0" class="menu">
                    <notification-messages-item v-for="notifyMessage in notifyMessages" :notification-message="notifyMessage" :key="notifyMessage.id"></notification-messages-item>
                </ul>
                <!-- /.menu -->
            </li>
            <li class="footer"><a :href="allMessagesUrl">Все Сообщения</a></li>
        </ul>
    </li>
</template>

<script>
    import NotificationMessagesItem from './NotificationMessagesItem';
    export default {
        props: {
            notificationMessages: {
                type: Array,
                default: () => []
            },
            userid: {
                type: Number,
                default: () => 0
            },
            allMessagesUrl: {
                type: String,
                default: () => '#'
            },
            isMobile: {
                type: Boolean,
                default: false
            }
        },
        data: function() {
            return {
                notifyMessages: this.notificationMessages,
                un_read_count: this.notificationMessages.length,
                message_count: this.notificationMessages.length,
            }
        },
        components: {NotificationMessagesItem},
        methods: {
            markNotificationAsRead() {
                if (this.notifyMessages.length) {
                    axios.get('/notification/mark-as-read').then(response => {
                        if (response.data.status === 'success') {
                            this.un_read_count = 0;
                        }
                    });
                }
            }
        },
        mounted() {
            window.Echo.private('notification.user.'+this.userid).notification((notification) => {
                if (notification.type === 'App\\Notifications\\MessageSentNotification') {
                    let newNotification = {
                        data: {message: notification.message, sender: notification.sender, chat: notification.chat},
                        sender: notification.sender
                    };
                    this.notifyMessages.push(newNotification);
                    this.un_read_count = this.un_read_count + 1;
                    this.message_count = this.notifyMessages.length;
                }
            });
        }
    }
</script>
