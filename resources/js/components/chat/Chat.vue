<template>
    <div class="chat-wrapper" :class="[isMobile ? 'mobile' : '', isMobile ? sideBarOpened ? 'sidebar-open' : '' : '']">
        <div v-if="!currUser.is_client" class="chat-sidebar">
            <div class="chat-sidebar__header">
                <div class="chat-sidebar__header-title">Список чатов</div>
            </div>
            <div class="chat-sidebar__item">
                <chat-list :chats-list="chatsList" @openChat="openChat"></chat-list>
            </div>
        </div>
        <div class="chat-content" @click="sideBarClose($event)">
            <div class="chat-content__header">
                <div v-if="isMobile && !currUser.is_client" class="chat-content__header-btn" @click="toggleMenu" :ref="chatMenuBtn"></div>
                <chat-header :chat="activeChat"></chat-header>
            </div>
            <div class="chat-content__message">
                <chat-item v-for="chat in chatsList"
                           :key="chat.id"
                           :chat="chat"
                           :current-user="currUser"
                           v-show="chat.active"
                           @markAllAsRead="markAllAsRead"
                ></chat-item>
                <div v-if="!isActiveChat" class="chat-empty-message">
                    Выберите, кому хотели бы написать
                </div>
            </div>
            <div class="chat-content__message-input" :class="{'active': isActiveChat}">
                <chat-input :chat="activeChat"></chat-input>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import ChatHeader from './ChatHeader';
    import ChatItem from './ChatItem';
    import ChatInput from './ChatInput';
    import ChatList from './ChatList';
    import {isEmptyObject} from "../../libs/utils";

    export default {
        components: {ChatList, ChatItem, ChatInput, ChatHeader},
        props: {
            chats: {
                type: Array,
                default: () => []
            },
            agentType: {
                type: String,
                default: () => 'desktop'
            },
        },
        data: function() {
            return {
                chatMenuBtn: 'chatMenuBtn',
                chatsList: [],
                activeChat: {},
                mobileWindowWidth: 767,
                windowInnerWidth: 0,
                sideBarOpened: false
            }
        },
        computed: {
            isMobile: function () {
                return this.agentType === 'mobile' || (this.windowInnerWidth > 0 && this.mobileWindowWidth >= this.windowInnerWidth);
            },
            isActiveChat: function () {
                return !isEmptyObject(this.activeChat);
            },
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
        watch: {
            currUser(user) {
                if (!isEmptyObject(user)) {
                    this.chatsList = this.buildChatList();
                    if(user.is_client && this.chatsList.length){
                        this.openChat(this.chatsList[0].id);
                    }
                }
            }
        },
        methods: {
            toggleMenu() {
                this.sideBarOpened = !this.sideBarOpened;
            },
            sideBarClose(event) {
                if(!event || this.$refs[this.chatMenuBtn] !== event.target) {
                    this.sideBarOpened = false;
                }
            },
            buildChatList(){
                let chatList = [];
                for(let key in this.chats) {
                    if (this.chats.hasOwnProperty(key)) {
                        chatList.push({
                            id: this.chats[key].id,
                            url: '/chat/'+this.chats[key].id,
                            avatar: this.getChatAvatar(this.chats[key]),
                            name: this.getChatName(this.chats[key]),
                            messages: this.chats[key].messages,
                            un_read_messages: this.chats[key].un_read_messages,
                            last_message: this.chats[key].last_message,
                            private: this.chats[key].private,
                            status: this.getChatStatus(this.chats[key]),
                            active: false,
                            scroll_bottom: true
                        });
                    }

                }
                return chatList;
            },

            getChatAvatar(chat)
            {
                let avatar = '/images/chats/avatar.jpg';

                if (!chat.private) {
                    avatar = '/images/chats/group.jpg';
                } else {
                    for(let key in chat.users) {
                        if (chat.users.hasOwnProperty(key) && chat.users[key].id !== this.currUser.id) {
                            avatar = chat.users[key].avatar;
                            return avatar;
                        }
                    }
                }
                return avatar;
            },

            getChatName(chat)
            {
                let name = chat.name;
                if (!chat.private) {
                    // картинку для группы
                    name = chat.name;
                } else {
                    for(let key in chat.users) {
                        if (chat.users.hasOwnProperty(key) && chat.users[key].id !== this.currUser.id) {
                            name = chat.users[key].name;
                            return name;
                        }
                    }

                }
                return name;
            },

            getChatStatus(chat) {
                let status = 1;
                if (chat.users.length <= 1) { //если всего один участник
                    status = 0;
                }

                return status;
            },

            openChat(chat_id) {
                this.sideBarClose();

                // set active chat
                this.chatsList.forEach(el => {
                    if (parseInt(el.id) === parseInt(chat_id)) {
                        el.active = true;
                        this.activeChat = el;
                    } else {
                        el.active = false;
                    }
                });
                // set active chat
                window.location.hash = chat_id;
            },

            markAllAsRead(done, chat) {
                if (chat.un_read_messages.length) {
                    this.chatsList.forEach(el => {
                        if (el.id === chat.id) {
                            el.un_read_messages = [];
                        }
                    });
                    axios.get('/chat/'+chat.id+'/mark-as-read').then(response => {
                        if (response.data.status === 'success') {
                            done();
                        }
                    });
                } else {
                    done();
                }
            },
            sendMessageHandler(payload){
                this.chatsList.forEach(el => {
                    if (parseInt(el.id) === parseInt(payload.chat.id)) {
                        el.messages.push(payload.message);
                        el.last_message = payload.message;
                    }
                });
            },
            handleResize() {
                this.windowInnerWidth = window.innerWidth;
            }
        },
        created() {
            window.addEventListener('resize', this.handleResize);
            this.handleResize();
        },
        destroyed() {
            window.removeEventListener('resize', this.handleResize)
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
            window.Echo.private('chat.user.'+this.currUser.id).listen('MessageSent', (e) => {
                this.chatsList.forEach(el => {
                    if (el.id === e.chat_id) {
                        let newMessage = [e.message];
                        el.messages = [...el.messages, ...newMessage];
                        el.un_read_messages = [...el.un_read_messages, ...newMessage];
                        el.last_message = e.message;
                    }
                });
            });

            this.$root.$on('sendNewChatMessage', this.sendMessageHandler);
        },
    }
</script>
