<template></template>
<script>
    export default {
        props: {
            currentUser: {
                type: Object,
                default: () => {}
            },
            currentManager: {
                type: Object,
                default: () => {}
            },
            currentClients: {
                type: Array,
                default: () => {}
            },
            balanceMessage: {
                type: String,
                default: ''
            },
        },
        mounted() {
            this.$store.dispatch('setCurrentUser', this.currentUser);
            this.$store.dispatch('setCurrentManager', this.currentManager);
            this.$store.dispatch('setCurrentClients', this.currentClients);

            if (this.balanceMessage === 'replenished') {
                new Noty({
                    type: 'success',
                    text: 'Баланс пополнен.',
                    layout: 'topRight',
                    timeout: 5000,
                    progressBar: true,
                    theme: 'metroui',
                }).show();
            }
        },
    }
</script>