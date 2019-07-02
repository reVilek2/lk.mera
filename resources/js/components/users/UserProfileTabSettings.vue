<template>
    <div>
        <form-user-settings :user="user" @updatedUserSettings="updatedUserSettings"></form-user-settings>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import FormUserSettings from '../forms/FormUserSettings';
    export default {
        props: {
            user: {
                type: Object,
                default: () => {}
            },
        },
        components: { formUserSettings: FormUserSettings},
        computed: {
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },

        methods: {
            updatedUserSettings(user) {
                this.$emit('updatedUserSettings', user);
            }
        },
    }
</script>
