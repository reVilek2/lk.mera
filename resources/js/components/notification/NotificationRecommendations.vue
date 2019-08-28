<template>
    <li class="dropdown notifications-menu" @click="markNotificationAsRead">
        <a v-if="isMobile" :href="categoryUrl">
            <span class="footer-item-icon">
                <i class="fa fa-tasks">
                    <span v-if="unreadItemsCount > 0" class="label label-warning">{{unreadItemsCount}}</span>
                </i>
            </span>
            <span class="footer-item-title">Рекомендации</span>
        </a>

        <!-- Menu toggle button -->
        <a v-if="!isMobile" href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-tasks"></i>
            <span v-if="unreadItemsCount > 0" class="label label-success">{{unreadItemsCount}}</span>
        </a>
        <ul v-if="!isMobile" class="dropdown-menu">
            <li v-if="items.length > 0" class="header">У вас {{items.length}} {{ $sklonyator(items.length, ['рекомендация', 'рекомендации', 'рекомендаций']) }}</li>
            <li v-else class="header">У вас нет новых рекомендациий</li>
            <li>
                <!-- inner menu: contains the messages -->
                <ul v-if="items.length > 0" class="menu">
                    <notification-recommendations-item v-for="recommendation in items" :recommendation="recommendation" :key="recommendation.id"></notification-recommendations-item>
                </ul>
                <!-- /.menu -->
            </li>
            <li class="footer"><a :href="categoryUrl">Все рекомендации</a></li>
        </ul>
    </li>
</template>

<script>
    import NotificationRecommendationsItem from './NotificationRecommendationsItem';
    export default {
        props: {
            recommendations: {
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
                items: this.recommendations,
                unreadItemsCount: this.recommendations.length,
            }
        },
        components: {NotificationRecommendationsItem},
        methods: {
            markNotificationAsRead() {
                if (this.items.length) {
                    axios.get('/recommendations/mark-as-read').then(response => {
                        if (response.data.status === 'success') {
                            this.unreadItemsCount = 0;
                        }
                    });
                }
            }
        },
        mounted() {
            window.Echo.private('recommendation.user.'+this.userid).notification((notification) => {
                if (notification.type === 'App\\Notifications\\RecommendationCreated') {
                    let newNotification = {
                        data: {recommendation: notification.recommendation, sender: notification.sender},
                        sender: notification.sender
                    };
                    this.items.push(newNotification);
                    this.unreadItemsCount += 1;
                }
            });
        }
    }
</script>
