<template>
    <div class="chat-messages">
        <div id="" class="box box-primary direct-chat direct-chat-primary">
            <div class="box-header with-border">
                <h3 v-if="chat.status === 1" class="box-title">{{chat.name}}</h3>
                <h3 v-else class="box-title">Чат не доступен!</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
            </div>
            <div class="box-body">
                <div :id="id_chat_container" class="direct-chat-messages">
                    <div v-for="message in chat.messages">
                        <div v-if="message.sender.id === userid" class="direct-chat-msg right" :id="'message-'+message.id">
                            <div class="direct-chat-info clearfix">
                                <!--<span class="direct-chat-timestamp pull-right">{{message.created_at_humanize}}</span>-->
                            </div>
                            <img class="direct-chat-img" :src="message.sender.avatar" alt="Message User Image"><!-- /.direct-chat-img -->
                            <div class="direct-chat-text">
                                <span class="direct-chat-text__item">{{ message.message }}</span>
                                <span class="direct-chat-timestamp pull-right">{{message.created_at_humanize}}</span>
                            </div>
                        </div>
                        <div v-else class="direct-chat-msg" :id="'message-'+message.id">
                            <div class="direct-chat-info clearfix">
                                <span v-if="!chat.private" class="direct-chat-name pull-left">{{message.sender.name}}</span>
                                <!--<span class="direct-chat-timestamp pull-left">{{message.created_at_humanize}}</span>-->
                            </div>
                            <img class="direct-chat-img" :src="message.sender.avatar" alt="Message User Image">
                            <div class="direct-chat-text">
                                <span class="direct-chat-text__item">{{ message.message }}</span>
                                <span class="direct-chat-timestamp pull-left">{{message.created_at_humanize}}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="scroll-to-bottom" :class="{'active': btnToBottom}" @click="scrollToEnd(300)">
                    <span v-if="count_un_read > 0" class="scroll-to-bottom__unread unread">{{count_un_read}}</span>
                </div>
            </div>
            <div class="box-footer">
                <div class="chat-input-group">
                    <input type="hidden" name="_id" value="">
                    <chat-textarea
                            placeholder="Type Message ..."
                            :ref="messageRef"
                            class="form-control"
                            v-model="newMessage"
                            :min-height="34"
                            :max-height="350"
                            rows="1"
                            @keydown.native="sendMessageHandle"
                            @focus.native="setIsNeedMarkAllAsRead"
                    ></chat-textarea>
                    <span class="input-group-btn chat-bth">
                        <button v-if="chat.status === 1" class="btn btn-primary btn-flat" @click="sendMessage">Отправить</button>
                        <button v-else class="btn btn-primary btn-flat" disabled>Отправить</button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import ChatTextarea from './ChatTextarea';
    export default {
        props: {
            chat: {
                type: Object,
                default: () => {}
            },
            activeChat: {
                type: Boolean,
                default: () => false
            },
            userid: {
                type: Number,
                default: () => 0
            },
            count_un_read: {
                type: Number,
                default: () => 0,
            }
        },
        data() {
            return {
                newMessage: '',
                id_chat_container: "chat-container"+this.chat.id,
                scroll_bottom: true,
                bottom_done: false,
                active: this.activeChat,
                btnToBottom: false,
                scrolledToBottom: false,
                messageRef: "messageInput"+this.chat.id
            }
        },
        watch: {
            activeChat: function (isActive) {
                this.active = isActive;
                this.setIsNeedMarkAllAsRead();
                if (isActive && this.scroll_bottom) {
                    this.scroll_bottom = false;
                    this.scrollToEnd();
                }
            }
        },
        components: {ChatTextarea},
        methods: {
            sendMessageHandle(e) {
                if (e.keyCode === 13 && !e.ctrlKey) {
                    e.preventDefault();
                    this.sendMessage()
                }
            },
            sendMessage() {
                if (this.newMessage !== '') {
                    axios.post(this.chat.url, {'message-data': this.newMessage}).then(response => {
                        if (response.data.status === 'success') {
                            this.addMessage(response.data.message);
                        }
                    });
                    this.newMessage = '';
                    this.$refs[this.messageRef].$el.focus();
                }
            },

            addMessage(message) {
                this.chat.messages.push(message);
                this.chat.last_message = message;
                this.scrollToEnd(300);
            },

            scrollToEnd(speed = 0) {
                let $container = $('#'+this.id_chat_container);
                $container.stop().animate({
                    scrollTop: $container[0].scrollHeight
                }, speed);
            },

            resetBottomDone() {
                this.bottom_done = false;
            },

            markAllAsRead() {
                let _this = this;
                _this.bottom_done = true;
                _this.$emit('mark-all-as-read', _this.resetBottomDone, _this.chat);
            },

            handleFocus() {
                this.setIsNeedMarkAllAsRead();
            },

            listenScroll(){
                let _this = this;
                let scrollContainer = document.getElementById(_this.id_chat_container);
                scrollContainer.onscroll = function(){
                    if (_this.active) {
                        let containerScrollTop = scrollContainer.scrollTop + 5;
                        let containerScrollPosition = scrollContainer.offsetHeight + containerScrollTop;
                        // отслеживание когда элемент попадает в зону видимости
                        // _this.chat.messages.forEach(el => {
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

            showBtnToBottom() {
                this.btnToBottom = true;
            },

            hideBtnToBottom() {
                this.btnToBottom = false;
            },

            setIsNeedMarkAllAsRead() {
                let _this = this;

                if (!_this.bottom_done && _this.active) {
                    let scrollContainer = document.getElementById(_this.id_chat_container);
                    if (scrollContainer.offsetHeight >= scrollContainer.scrollHeight) {
                        _this.markAllAsRead();
                    }
                }
            }
        },
        mounted() {
            let _this = this;

            // прослушивание скролла
            setTimeout(function(){
                _this.listenScroll();
            }, 10);
        },
    }
</script>
