<template>
    <div class="row">
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-header with-border">All Users</div>

                <div class="box-body">
                    <ul class="chat-list">
                        <li class="chat-list__item" v-for="chat in chatsList" :class="{'active': chat.active}">
                            <chat-list-name :chat_url="chat.url"
                                            :chat_id="chat.id"
                                            :chat_img="chat.avatar"
                                            :chat_name="chat.name"
                                            :count_un_read="chat.un_read_messages.length"
                                            :key="chat.id"></chat-list-name>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <!--<div class="js-chat-messages chat-messages"></div>-->
            <chat-container v-for="chat in chatsList" :chat="chat" :userid="userid" :key="chat.id" v-show="chat.active"></chat-container>
        </div>
    </div>
</template>

<script>
    import ChatContainer from './ChatContainer';
    import ChatListName from './ChatListName';
    export default {
        props: {
            chats: {
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
                chatsList: this.buildChatList()
            }
        },
        components: {ChatContainer, ChatListName},
        methods: {
            buildChatList(){
                let chats = [];
                for(let key in this.chats) {
                    if (this.chats.hasOwnProperty(key)) {
                        chats.push({
                            id: this.chats[key].id,
                            url: '/chat/'+this.chats[key].id,
                            avatar: this.getChatAvatar(this.chats[key]),
                            name: this.getChatName(this.chats[key]),
                            messages: this.chats[key].messages,
                            un_read_messages: this.chats[key].un_read_messages,
                            private: this.chats[key].private,
                            active: false
                        });
                    }

                }
                return chats;
            },

            getChatAvatar(chat)
            {
                let avatar;
                if (!chat.private) {
                    avatar = '/images/group.jpg';
                } else {
                    for(let key in chat.users) {
                        if (chat.users.hasOwnProperty(key) && chat.users[key].id !== this.userid) {
                            avatar = chat.users[key].avatar;
                            return avatar;
                        }
                    }

                }
                return avatar;
            },

            getChatName(chat)
            {
                let name;
                if (!chat.private) {
                    // картинку для группы
                    name = chat.name;
                } else {
                    for(let key in chat.users) {
                        if (chat.users.hasOwnProperty(key) && chat.users[key].id !== this.userid) {
                            name = chat.users[key].name;
                            return name;
                        }
                    }

                }
                return name;
            },

            openChat(chat_id) {
                // set active chat
                this.chatsList.forEach(el => {
                    el.active = parseInt(el.id) === parseInt(chat_id);
                });
                // set active chat
                window.location.hash = chat_id;
            }
        },
        mounted() {
            this.$nextTick(function () {
                 let chat_id = location.hash.replace(/^#/, '');
                if (chat_id) {
                    this.chatsList.forEach(el => {
                        if (parseInt(el.id) === parseInt(chat_id)) {
                            this.openChat(el.id);
                        }
                    });
                }
            });
            window.Echo.private('chat.user.'+this.userid).listen('MessageSent', (e) => {
                this.chatsList.forEach(el => {
                    if (el.id === e.chat_id) {
                        let newMessage = [e.message];
                        el.messages = [...el.messages, ...newMessage];
                        el.un_read_messages = [...el.un_read_messages, ...newMessage];
                    }
                });
            });
        }
    }
</script>
