<template>
    <div>
        <form-user-password :user="user" @updatedUserPassword="updatedUserPassword"></form-user-password>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import FormUserPassword from '../forms/FormUserPassword';
    export default {
        props: {
            user: {
                type: Object,
                default: () => {}
            },
        },
        components: { formUserPassword: FormUserPassword},
        computed: {
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
        methods: {
            updatedUserPassword(user) {
                this.$emit('updatedUserPassword', user);
            }
        },
    }
</script>
