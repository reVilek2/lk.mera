<template>
    <div class="chat-msg__wrapper">
        <div :id="chat_msg_id" class="chat-msg">
            <div class="chat-msg__item">
                <div v-for="message in messages" :key="message.id" :id="'message-'+message.id" class="chat-msg-item" :class="{'right':isSender(message)}">
                    <div v-if="!isPrivateChat && !isSender(message)" class="chat-msg-item__info">
                        <span class="chat-msg-item__info-name pull-left" :class="isSender(message) ? 'pull-right': 'pull-left'">
                            {{message.sender.name}}
                        </span>
                    </div>
                    <img v-if="message.sender.avatar" :src="message.sender.avatar" alt="Message User Image" class="chat-msg-item__img">
                    <div class="chat-msg-item__text">
                        <span class="chat-msg-item__text-item">{{ message.message }}</span>
                        <span class="chat-msg-item__timestamp" :class="isSender(message) ? 'pull-right': 'pull-left'">{{message.created_at_humanize}}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="scroll-to-bottom" :class="{'active': btnToBottom}" @click="scrollToEnd(300)">
            <span v-if="count_un_read > 0" class="scroll-to-bottom__unread unread">{{count_un_read}}</span>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            chat: {
                type: Object,
                default: () => {}
            },
            currentUser: {
                type: Object,
                default: () => {}
            },
        },
        data: function() {
            return {
                chat_msg_id: this.chat.id,
                bottom_done: false,
                btnToBottom: false,
                scroll_bottom: true,
            }
        },
        computed: {
            count_un_read: function () {
                return this.chat.un_read_messages.length
            },
            messages: function () {
                return this.chat.messages;
            },
            isPrivateChat: function () {
                return !!this.chat.private;
            },
            isActiveChat: function () {
                return this.chat.active;
            },
        },
        watch: {
            isActiveChat: function (is_active) {
                this.setIsNeedMarkAllAsRead();
                if (is_active && this.scroll_bottom) {
                    this.scroll_bottom = false;
                    this.scrollToEnd();
                }
            }
        },
        methods: {
            isSender(message) {
                return message.sender.id === this.currentUser.id
            },
            resetBottomDone() {
                this.bottom_done = false;
            },
            markAllAsRead() {
                this.bottom_done = true;
                this.$emit('markAllAsRead', this.resetBottomDone, this.chat);
            },
            showBtnToBottom() {
                this.btnToBottom = true;
            },
            hideBtnToBottom() {
                this.btnToBottom = false;
            },
            scrollToEnd(speed = 0) {
                let $container = $('#'+this.chat_msg_id);
                $container.stop().animate({
                    scrollTop: $container[0].scrollHeight
                }, speed);
            },
            listenScroll(){
                let _this = this;
                let scrollContainer = document.getElementById(_this.chat_msg_id);
                scrollContainer.onscroll = function(){
                    if (_this.isActiveChat) {
                        let containerScrollTop = scrollContainer.scrollTop + 5;
                        let containerScrollPosition = scrollContainer.offsetHeight + containerScrollTop;
                        // отслеживание когда элемент попадает в зону видимости
                        // _this.messages.forEach(el => {
                        //     let message = document.getElementById('message-'+el.id);
                        //     let messageTopPosition = message.offsetTop + message.offsetHeight;
                        //
                        //
                        //     if (containerScrollTop <= messageTopPosition && containerScrollPosition >= messageTopPosition) {
                        //         //элементы в зоне видимости
                        //         // console.log('элементы в зоне видимости');
                        //     }
                        // });
                        if (containerScrollPosition >= scrollContainer.scrollHeight) {

                            if (_this.bottom_done) return;
                            //дно контейнера
                            // console.log('дно контейнера');
                            _this.markAllAsRead();
                        } else {
                            if ((scrollContainer.scrollHeight - containerScrollPosition) >= 500) {
                                _this.showBtnToBottom();
                            } else {
                                _this.hideBtnToBottom();
                            }
                        }
                    }
                };
            },
            sendMessageHandler(payload){
                this.scrollToEnd(300);
            },
            setIsNeedMarkAllAsRead(e) {
                if (!this.bottom_done && this.isActiveChat) {
                    let scrollContainer = document.getElementById(this.chat_msg_id);
                    if (scrollContainer.offsetHeight >= scrollContainer.scrollHeight) {
                        this.markAllAsRead();
                    }
                }
            },
            chatInputFocusHandler(e) {
                this.setIsNeedMarkAllAsRead();
            },
        },
        mounted() {
            let _this = this;

            // прослушивание скролла
            setTimeout(function(){
                _this.listenScroll();
                _this.scrollToEnd();
            }, 10);

            this.$root.$on('sendNewChatMessage', this.sendMessageHandler);
            this.$root.$on('chatInputFocusHandle', this.chatInputFocusHandler);
        },
    }
</script>
