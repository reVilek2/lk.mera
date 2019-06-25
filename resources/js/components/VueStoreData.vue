<template></template>
<script>
    export default {
        props: {
            currentUser: {
                type: Object,
                default: () => {}
            },
            balanceMessage: {
                type: String,
                default: ''
            },
        },
        mounted() {
            this.$store.dispatch('setCurrentUser', this.currentUser);
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