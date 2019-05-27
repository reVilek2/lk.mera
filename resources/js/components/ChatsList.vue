<template>
    <div class="row">
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-header with-border">All Users</div>

                <div class="box-body">
                    <ul class="chat-list">
                        <li class="chat-list__item" v-for="chat in chatsList" :class="{'active': chat.active}">
                            <a :href="chat.url" class="chat-list__link js-load-user-chat" :data-chat="chat.id" @click="loadChat(chat, $event)">
                                <span class="chat-list__user-icon">
                                    <img :src="chat.avatar" class="user-image" alt="User Image">
                                    <span v-if="chat.un_read_messages.length > 0" class="chat-list__unread">{{chat.un_read_messages.length}}</span>
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
            <chat v-for="chat in chatsList" :chat="chat" :userid="userid" :key="chat.id" v-show="chat.active"></chat>
        </div>
    </div>
</template>

<script>
    import Chat from './Chat';
    export default {
        props: [
            'chats',
            'userid'
        ],
        data: function() {
            return {
                chatsList: this.buildChats()
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
                    this.chatsList.forEach(el => {

                        el.active = el === chat;
                    });
                    // set active chat
                    window.location.hash = chat.id;

                    // load chat data
                    // if (!chat.onload) {
                    //     axios.get(chat.url).then(response => {
                    //         if (response.data.status === 'success') {
                    //             this.chats.forEach(el => {
                    //                 if (el === chat) {
                    //                     el.messages = [...el.messages, ...response.data.messages];
                    //                     el.onload = true;
                    //                 }
                    //             });
                    //         }
                    //     });
                    // }


                }
            },
            buildChats(){
                let chats = [];
                for(let key in this.chats) {
                    if (this.chats.hasOwnProperty(key)) {
                        chats.push({
                            id: this.chats[key].id,
                            url: '/chat/'+this.chats[key].id,
                            avatar: this.getAvatar(this.chats[key]),
                            name: this.getName(this.chats[key]),
                            messages: this.chats[key].messages,
                            un_read_messages: this.chats[key].un_read_messages,
                            active: false
                        });
                    }

                }
                return chats;
            },
            getAvatar(chat)
            {
                let avatar;
                if (!chat.private) {
                    // картинку для группы
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
            getName(chat)
            {
                let name;
                if (!chat.private) {
                    // картинку для группы
                    name = chat.group_name;
                } else {
                    for(let key in chat.users) {
                        if (chat.users.hasOwnProperty(key) && chat.users[key].id !== this.userid) {
                            name = chat.users[key].name;
                            return name;
                        }
                    }

                }
                return name;
            }
        },
        mounted() {
            this.$nextTick(function () {
                 let chat_id = location.hash.replace(/^#/, '');
                if (chat_id) {
                    this.chatsList.forEach(el => {
                        if (parseInt(el.id) === parseInt(chat_id)) {
                            this.loadChat(el);
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
