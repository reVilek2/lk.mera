<template>
    <div v-if="title" class="chat-content__header-title">{{title}}</div>
</template>

<script>
    import {isEmptyObject} from "../../libs/utils";
    export default {
        props: {
            chat: {
                type: Object,
                default: () => {}
            },
        },
        computed: {
            title: function () {
                let title = null;
                if (!isEmptyObject(this.chat)) {
                    title = this.chat.name;
                    if (this.chat.status !== 1) {
                        title += ' (чат недоступен)'
                    }
                }
                return title
            },
        },
    }
</script>
