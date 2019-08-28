<template>
    <li>
        <a :href="'#'">
            <div class="notification-document">
                <div class="notification-document__sender">
                    {{document.sender.name}}
                </div>
                <div class="notification-document__title">
                    {{document.data.document.name}}
                </div>
            </div>
            <small><i class="fa fa-clock-o"></i> {{document.data.document.created_at_humanize}}</small>
        </a>
    </li>
</template>

<script>
    export default {
        props:['document'],
        data(){return {}},
        mounted(){
            console.log(document);
        }
    }
</script>
