<template>
    <div class="chat-input-group">
        <div class="chat-input-group__element-wrapper textarea-wrapper">
            <input type="hidden" name="_id" value="">
            <chat-textarea
                placeholder="сообщение ..."
                :ref="messageRef"
                class="form-control"
                v-model="newMessage"
                :min-height="34"
                :max-height="350"
                rows="1"
                @keydown.native="keydownHandle($event)"
                @focus.native="focusHandle($event)"
                @blur.native="blurHandle($event)"
            ></chat-textarea>
        </div>
        <div class="chat-input-group__element-wrapper button-wrapper">
        <template v-if="isMobile">
            <span v-if="isProcessSending" class="chat-btn">
                <button class="btn btn-danger btn-flat" disabled><span class="preloader preloader-sm"></span></button>
            </span>
            <span v-else class="chat-btn">
                <button v-if="chat.status === 1" class="btn btn-danger btn-flat" @click="sendMessage"><i class="fa fa-arrow-up" aria-hidden="true"></i></button>
                <button v-else class="btn btn-danger btn-flat" disabled><i class="fa fa-arrow-up" aria-hidden="true"></i></button>
            </span>
        </template>
        <template v-else>
            <span v-if="isProcessSending" class="preloader preloader-sm"></span>
            <button v-if="chat.status === 1" class="btn btn-danger btn-flat" @click="sendMessage">Отправить</button>
            <button v-else class="btn btn-danger btn-flat" disabled>Отправить</button>
        </template>
        </div>
    </div>
</template>

<script>
    import ChatTextarea from './ChatTextarea';
    import {isEmptyObject} from "../../libs/utils";
    export default {
        components: {ChatTextarea},
        props: {
            chat: {
                type: Object,
                default: () => {}
            },
            isMobile: {
                type: Boolean,
                default: () => false
            },
        },
        data: function() {
            return {
                messageRef: "messageInput",
                newMessage: '',
                sendUrl: null,
                isProcessSending: false,
            }
        },
        watch: {
            chat(chat) {
                if (!isEmptyObject(chat)) {
                    this.messageRef = "messageInput"+chat.id;
                    this.sendUrl = chat.url;
                }
            }
        },
        methods: {
            keydownHandle(e) {
                if (e.keyCode === 13 && !e.ctrlKey) {
                    e.preventDefault();
                    this.sendMessage()
                }
            },
            sendMessage() {
                if (this.newMessage !== '' && this.sendUrl && !this.isProcessSending) {
                    this.isProcessSending = true;
                    axios.post(this.sendUrl, {'message-data': this.newMessage}).then(response => {
                        if (response.data.status === 'success') {
                            this.$root.$emit('sendNewChatMessage', {chat:this.chat, message: response.data.message});
                        }
                        if (response.data.status === 'exception') {
                            new Noty({
                                type: 'error',
                                text: response.data.message,
                                layout: 'topRight',
                                timeout: 5000,
                                progressBar: true,
                                theme: 'metroui',
                            }).show();
                        }
                        this.isProcessSending = false;
                    }).catch(errors => {
                        new Noty({
                            type: 'error',
                            text: 'Произошла ошибка.',
                            layout: 'topRight',
                            timeout: 5000,
                            progressBar: true,
                            theme: 'metroui',
                        }).show();
                        this.isProcessSending = false;
                    });
                    this.newMessage = '';
                    this.$refs[this.messageRef].$el.focus();
                    this.$refs[this.messageRef].$el.blur();
                }
            },
            focusHandle(e) {
                document.querySelector('.main-footer-mobile').classList.add('hidden_menu');
                document.querySelector('.chat-wrapper').classList.add('set__full_height');
                this.$root.$emit('chatInputFocusHandle', e);
            },
            blurHandle(e) {
                setTimeout(function() {
                  document.querySelector('.main-footer-mobile').classList.remove('hidden_menu');
                  document.querySelector('.chat-wrapper').classList.remove('set__full_height');
                  const scrollingElement =  document.body;
                  window.scrollTo(0, scrollingElement.scrollHeight);
                }, 200);
                
            }
        }
    }
</script>
