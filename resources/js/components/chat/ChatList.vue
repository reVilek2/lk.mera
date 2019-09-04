<template>
    <ul class="chat-list">
        <li class="chat-list__item" v-for="chat in chatsList" :key="chat.id" :class="{'active': chat.active}">
            <chat-list-item :chat="chat" :key="chat.id" @openChat="openChat"></chat-list-item>
        </li>
    </ul>
</template>

<script>
    import ChatListItem from './ChatListItem';
    export default {
        components: {ChatListItem},
        props: {
            chatsList: {
                type: Array,
                default: () => []
            },
        },
        methods: {
            openChat(event){
                this.$emit('openChat', event);
            }
        }
    }
</script>
