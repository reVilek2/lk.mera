<template>
    <a :href="chat_url" class="chat-list__link js-load-user-chat" :data-chat="chat_id" @click="openChat($event)">
        <span class="chat-list__user-icon">
            <img :src="chat_img" class="user-image" alt="Chat logo">
            <span v-if="count_un_read > 0" class="chat-list__unread unread">{{count_un_read}}</span>
        </span>
        <span class="chat-list__user-box">
            <span class="chat-list__user-name">{{ chat_name }}</span>
            <span v-if="is_last_message" class="chat-list__last-message">
                <span v-if="chat_last_message.sender.id === userid">Вы: </span>
                {{chat_last_message.message}}
            </span>
        </span>
    </a>
</template>

<script>
    export default {
        props: {
            chat_url: {
                type: String,
                default: () => '/chat',
            },
            chat_id: {
                type: Number,
                default: () => 0,
            },
            chat_img: {
                type: String,
                default: () => '',
            },
            chat_name: {
                type: String,
                default: () => '',
            },
            count_un_read: {
                type: Number,
                default: () => 0,
            },
            chat_last_message: {
                type: Object,
                default: () => {},
            },
            is_last_message: {
                type: Boolean,
                default: () => false,
            },
            userid: {
                type: Number,
                default: () => 0
            },
        },
        data() {
            return {

            }
        },
        methods: {
            openChat(event){
                if (event) {
                    event.preventDefault();
                }
                this.$parent.openChat(this.chat_id);
            }
        }
    }
</script>
