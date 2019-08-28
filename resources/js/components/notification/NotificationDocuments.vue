<template>
    <li class="dropdown notifications-menu" @click="markNotificationAsRead">
        <a v-if="isMobile" :href="categoryUrl">
            <span class="footer-item-icon">
                <i class="fa fa-file-pdf-o">
                    <span v-if="unreadItemsCount > 0" class="label label-warning">{{unreadItemsCount}}</span>
                </i>
            </span>
            <span class="footer-item-title">Отчеты</span>
        </a>
        <!-- Menu toggle button -->
        <a v-if="!isMobile" href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-file-pdf-o"></i>
            <span v-if="unreadItemsCount > 0" class="label label-success">{{unreadItemsCount}}</span>
        </a>
        <ul v-if="!isMobile" class="dropdown-menu">
            <li v-if="items.length > 0" class="header">У вас {{items.length}} {{ $sklonyator(items.length, ['отчет', 'отчета', 'отчетов']) }}</li>
            <li v-else class="header">У вас нет новых отчетов</li>
            <li>
                <!-- inner menu: contains the messages -->
                <ul v-if="items.length > 0" class="menu">
                    <notification-documents-item v-for="document in items" :document="document" :key="document.id"></notification-documents-item>
                </ul>
                <!-- /.menu -->
            </li>
            <li class="footer"><a :href="categoryUrl">Все отчеты</a></li>
        </ul>
    </li>
</template>

<script>
    import NotificationDocumentsItem from './NotificationDocumentsItem';
    export default {
        props: {
            documents: {
                type: Array,
                default: () => []
            },
            userid: {
                type: Number,
                default: () => 0
            },
            categoryUrl: {
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
                items: this.documents,
                unreadItemsCount: this.documents.length,
            }
        },
        components: {NotificationDocumentsItem},
        methods: {
            markNotificationAsRead() {
                if (this.items.length) {
                    axios.get('/documents/mark-as-read').then(response => {
                        if (response.data.status === 'success') {
                            this.unreadItemsCount = 0;
                        }
                    });
                }
            }
        },
        mounted() {
            window.Echo.private('document.user.'+this.userid).notification((notification) => {
                if (notification.type === 'App\\Notifications\\DocumentCreated') {
                    let newNotification = {
                        data: {document: notification.document, sender: notification.sender},
                        sender: notification.sender
                    };

                    this.items.push(newNotification);
                    this.unreadItemsCount += 1;
                }
            });
        }
    }
</script>
