<template>
    <div class="row">
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-header with-border">All Users</div>

                <div class="box-body">
                    <ul class="chat-list">
                        <li class="chat-list__item" v-for="chat in chats" :class="{'active': chat.active}">
                            <a :href="chat.url" class="chat-list__link js-load-user-chat" :data-chat="chat.id" @click="loadChat(chat, $event)">
                                <span class="chat-list__user-icon">
                                    <img :src="chat.avatar" class="user-image" alt="User Image">
                                    <span class="chat-list__unread">2</span>
                                </span>
                                <span>{{ chat.name }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <!--<div class="js-chat-messages chat-messages"></div>-->
            <chat v-for="chat in chats" :chat="chat" :userid="userid" :key="chat.id" v-show="chat.active"></chat>
        </div>
    </div>
</template>

<script>
    import Chat from './Chat';
    export default {
        props: [
            'users',
            'userid'
        ],
        data: function() {
            return {
                chats: this.buildChats()
            }
        },
        components: {Chat},
        methods: {
            loadChat(chat, event) {
                if (event) {
                    event.preventDefault();
                }
                if (chat) {
                    // set active chat
                    this.chats.forEach(el => {
                        el.active = el === chat;
                    });
                    // load chat data
                    if (!chat.onload) {
                        axios.get(chat.url).then(response => {
                            if (response.data.status === 'success') {
                                this.chats.forEach(el => {
                                    if (el === chat) {
                                        el.messages = [...el.messages, ...response.data.messages];
                                        el.onload = true;
                                    }
                                });
                            }
                        });
                    }
                    // set active chat
                    window.location.hash = chat.id;
                }
            },
            buildChats(){
                let chats = [];
                for(let key in this.users) {
                    if (this.users.hasOwnProperty(key)) {
                         chats.push({
                            id: 'chat-'+this.users[key].id,
                            url: '/chat/'+this.users[key].id,
                            avatar: this.users[key].avatar,
                            name: this.users[key].name,
                            messages: [],
                            active: false,
                            onload: false,
                        });
                    }

                }
                return chats;
            }
        },
        mounted() {
            this.$nextTick(function () {
                let chat_id = location.hash.replace(/^#/, '');
                if (chat_id) {
                    this.chats.forEach(el => {
                        if (el.id === chat_id) {
                            this.loadChat(el);
                        }
                    });
                }
            });
            window.Echo.private('chat.user.'+Laravel.userId).listen('MessageSent', (e) => {
                this.chats.forEach(el => {
                    if (el.id === e.chat_id) {
                        let newMessage = [e.message];
                        el.messages = [...el.messages, ...newMessage];
                        console.log(el);
                    }
                });
            });
        }
    }
</script>
