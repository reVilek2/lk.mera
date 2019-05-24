<template>
    <div class="input-group">
        <input type="hidden" name="_id" value="">
        <input type="text" name="message-data" placeholder="Type Message ..." class="form-control" v-model="newMessage" @keyup.enter="sendMessage">
        <span class="input-group-btn">
            <button class="btn btn-primary btn-flat" @click="sendMessage">Отправить</button>
        </span>
    </div>
</template>

<script>
    export default {
        props: ['userid'],

        data() {
            return {
                newMessage: ''
            }
        },

        methods: {
            sendMessage() {
                this.$emit('messagesent', {
                    message: this.newMessage
                });
                this.newMessage = ''
            }
        }
    }
</script>
