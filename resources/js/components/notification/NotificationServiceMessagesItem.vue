<template>
    <li>
        <a href="#" @click="linkClick($event)">
            <p><i class="fa fa-info-circle text-aqua"></i> {{message.data.message.text}}</p>
            <small><i class="fa fa-clock-o"></i> {{message.data.message.created_at}}</small>
        </a>
    </li>
</template>

<script>
    export default {
        props:['message'],
        methods: {
            linkClick(e){
                e.preventDefault();
            }
        }
    }
</script>
