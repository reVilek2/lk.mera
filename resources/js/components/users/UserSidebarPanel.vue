<template>
    <div class="user-panel">
        <div class="pull-left image">
            <img v-if="user_avatar_small" :src="user_avatar_small" class="img-circle js-user-avatar-thumb" alt="User Image">
        </div>
        <div class="pull-left info">
            <p>{{user_name}}</p>
            <!-- Status -->
            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    import {isEmptyObject} from "../../libs/utils";

    export default {
        data: function() {
            return {
                user_avatar_small: '',
                user_name: '',
            }
        },
        computed: {
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
        watch: {
            currUser(user) {
                if(!isEmptyObject(user)) {
                    this.user_avatar_small = user.avatar_small;
                    this.user_name = user.name;
                }
            },
        }
    }
</script>