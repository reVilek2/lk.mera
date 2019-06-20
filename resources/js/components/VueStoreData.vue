<template></template>
<script>
    export default {
        props: {
            currentUser: {
                type: Object,
                default: () => {}
            },
        },
        mounted() {
            this.$store.dispatch('setCurrentUser', this.currentUser);
        },
    }
</script>