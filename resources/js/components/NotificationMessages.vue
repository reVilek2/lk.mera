<template>
    <li class="dropdown messages-menu">
        <!-- Menu toggle button -->
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-envelope-o"></i>
            <span class="label label-success">{{notifyMessages.length}}</span>
        </a>
        <ul class="dropdown-menu">
            <li class="header">У вас {{notifyMessages.length}} {{ $sklonyator(notifyMessages.length, ['сообщение', 'сообщения', 'сообщений']) }}</li>
            <li>
                <!-- inner menu: contains the messages -->
                <ul class="menu">
                    <notification-messages-item v-for="notifyMessage in notifyMessages" :notification-message="notifyMessage" :key="notifyMessage.id"></notification-messages-item>
                </ul>
                <!-- /.menu -->
            </li>
            <li class="footer"><a href="#">Все Сообщения</a></li>
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
            }
        },
        data: function() {
            return {
                notifyMessages: this.notificationMessages
            }
        },
        components: {NotificationMessagesItem},
        methods: {

        },
        mounted() {
            window.Echo.private('notification.user.'+this.userid).notification((notification) => {
                if (notification.type === 'App\\Notifications\\MessageSentNotification') {
                    console.log(notification);
                    let newNotification = {
                        data: {message: notification.message, sender: notification.sender, chat: notification.chat},
                        sender: notification.sender

                    };
                    this.notificationMessages.push(newNotification);
                }
            });
        }
    }
</script>
