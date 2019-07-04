<template>
    <a :href="url" class="chat-list__link" :data-chat="id" @click="openChat($event)">
        <span class="chat-list__user-icon">
            <img :src="avatar" class="user-image" alt="Chat logo">
            <span v-if="count_un_read > 0" class="chat-list__unread unread">{{count_un_read}}</span>
        </span>
        <span class="chat-list__user-box">
            <span class="chat-list__user-name">{{ name }}</span>
            <span v-if="is_last_message" class="chat-list__last-message">
                <span v-if="last_message.sender.id === currUser.id">Вы: </span>
                {{last_message.message}}
            </span>
        </span>
    </a>
</template>

<script>
    import { mapGetters } from 'vuex';
    import {isEmptyObject} from "../../libs/utils";
    export default {
        props: {
            chat: {
                type: Object,
                default: () => {}
            },
        },
        data() {
            return {
                id: this.chat.id,
                url: this.chat.url,
                avatar: this.chat.avatar,
                name: this.chat.name,
            }
        },
        computed: {
            last_message: function () {
                return this.chat.last_message
            },
            is_last_message: function () {
                return !isEmptyObject(this.chat.last_message)
            },
            count_un_read: function () {
                return this.chat.un_read_messages.length
            },
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
        methods: {
            openChat(event){
                if (event) {
                    event.preventDefault();
                }
                this.$emit('openChat', this.id);
            }
        }
    }
</script>
