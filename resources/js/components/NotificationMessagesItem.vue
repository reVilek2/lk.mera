<template>
    <li>
        <a v-bind:href="notificationMessage.data.chat.url">
            <div class="pull-left">
                <!-- User Image -->
                <img v-bind:src="notificationMessage.sender.avatar" class="img-circle" alt="User Image">
            </div>
            <!-- Message title and timestamp -->
            <h4>
                {{notificationMessage.sender.name}}
                <small><i class="fa fa-clock-o"></i> {{notificationMessage.data.message.created_at_humanize}}</small>
            </h4>
            <!-- The message -->
            <p>{{notificationMessage.data.message.message}}</p>
        </a>
    </li>
</template>

<script>
    export default {
        props:['notificationMessage'],
        data(){return {}},
        mounted(){}
    }
</script>
