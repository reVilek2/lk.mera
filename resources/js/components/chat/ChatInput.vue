<template>
    <div class="chat-input-group">
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
        ></chat-textarea>
        <span class="chat-bth">
            <span v-if="isProcessSending" class="preloader preloader-sm"></span>
            <button v-if="chat.status === 1" class="btn btn-primary btn-flat" @click="sendMessage">Отправить</button>
            <button v-else class="btn btn-primary btn-flat" disabled>Отправить</button>
        </span>
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
                }
            },
            focusHandle(e) {
                this.$root.$emit('chatInputFocusHandle', e);
            }
        }
    }
</script>
