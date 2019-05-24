<template>
    <div class="chat-messages">
        <div id="" class="box box-primary direct-chat direct-chat-primary">
            <div class="box-header with-border">
                <h3 class="box-title">{{chat.name}}</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
            </div>
            <div class="box-body">
                <div :id="chat.id" class="direct-chat-messages">
                    <div v-for="message in chat.messages">
                        <div v-if="message.sender.id === userid" class="direct-chat-msg right" :id="'message-'+message.id">
                            <div class="direct-chat-info clearfix">
                                <span class="direct-chat-timestamp pull-right">{{message.created_at}}</span>
                            </div>
                            <img class="direct-chat-img" :src="message.sender.avatar" alt="Message User Image"><!-- /.direct-chat-img -->
                            <div class="direct-chat-text">
                                {{ message.message }}
                            </div>
                        </div>
                        <div v-else class="direct-chat-msg" :id="'message-'+message.id">
                            <div class="direct-chat-info clearfix">
                                <span class="direct-chat-timestamp pull-left">{{message.created_at}}</span>
                            </div>
                            <img class="direct-chat-img" :src="message.sender.avatar" alt="Message User Image">
                            <div class="direct-chat-text">
                                {{ message.message }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <div class="input-group">
                    <input type="hidden" name="_id" value="">
                    <input type="text" name="message-data" placeholder="Type Message ..." class="form-control" v-model="newMessage" @keyup.enter="sendMessage">
                    <span class="input-group-btn">
                        <button class="btn btn-primary btn-flat" @click="sendMessage">Отправить</button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: [
            'chat',
            'userid',
        ],
        data() {
            return {
                newMessage: '',
                scroll_content: true,
            }
        },
        methods: {
            sendMessage() {
                if (this.newMessage !== '') {
                    axios.post(this.chat.url, {'message-data': this.newMessage}).then(response => {
                        if (response.data.status === 'success') {
                            this.addMessage(response.data.message);
                        }
                    });
                    this.newMessage = ''
                }
            },
            addMessage(message) {
                this.chat.messages.push(message);
                this.scrollToEnd(800);
            },
            scrollToEnd(speed = 0) {
                let $container = $("#"+this.chat.id);
                $container.stop().animate({
                    scrollTop: $container[0].scrollHeight
                }, speed);
            },
            listenScroll(){
                let me = this;
                setTimeout(function(){
                    let scrollContainer = document.getElementById(me.chat.id);
                    scrollContainer.onscroll = function(){
                        me.chat.messages.forEach(el => {
                            let message = document.getElementById('message-'+el.id);
                            let messageTopPosition = message.offsetTop + message.offsetHeight;
                            let containerScrollTop = scrollContainer.scrollTop+1;
                            let containerScrollPosition = scrollContainer.offsetHeight + containerScrollTop;

                            if (containerScrollTop <= messageTopPosition && containerScrollPosition >= messageTopPosition) {
                                //элементы в зоне видимости
                            }
                        });
                        if(scrollContainer.offsetHeight + scrollContainer.scrollTop+1 >= scrollContainer.scrollHeight){
                            //дно контейнера
                        }
                    }
                }, 1000)
            }
        },
        updated() {
            this.$nextTick(function () {
                if(this.scroll_content && this.chat.onload){
                    this.scrollToEnd(0);
                    this.scroll_content = false;
                }
            });
        },
        mounted() {
            this.listenScroll();
        },
    }
</script>
