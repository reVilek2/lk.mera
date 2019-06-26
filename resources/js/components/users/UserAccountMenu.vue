<template>
    <li class="dropdown user user-menu">
        <!-- Menu Toggle Button -->
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <!-- The user image in the navbar-->
            <img :src="user_avatar_small" class="user-image js-user-avatar-thumb" alt="User Image">
            <!-- hidden-xs hides the username on small devices so only the image appears. -->
            <span class="hidden-xs">{{user_name}}</span>
        </a>
        <ul class="dropdown-menu">
            <!-- The user image in the menu -->
            <li class="user-header">
                <img :src="user_avatar_medium" class="img-circle js-user-avatar-small" alt="User Image">

                <p>
                    {{user_name}} - {{user_role}}
                    <small>Зарегистрирован {{user_created}}</small>
                </p>
            </li>
            <!-- Menu Body -->
            <li v-if="!user_is_user || !user_is_client" class="user-body">
                <div class="row">
                    <div class="col-xs-4">
                        <a href="#">Менеджер:</a>
                    </div>
                    <div class="col-xs-8 text-left">
                        <span>{{manager_name}}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-4">
                        <a href="#">Баланс:</a>
                    </div>
                    <div class="col-xs-8 text-left">
                        <span>{{user_balance}}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-4">
                        <a href="#">На оплату:</a>
                    </div>
                    <div class="col-xs-8 text-left">
                        <span>{{user_total_payable}}</span>
                    </div>
                </div>
                <!-- /.row -->
            </li>
            <!-- Menu Footer-->
            <li class="user-footer">
                <div class="pull-left">
                    <a href="/profile" class="btn btn-default btn-flat">Редактировать</a>
                </div>
                <div class="pull-right">
                    <a href="/logout" class="btn btn-default btn-flat">Выйти</a>
                </div>
            </li>
        </ul>
    </li>
</template>
<script>
    import { mapGetters } from 'vuex';
    export default {
        data: function() {
            return {
                user_balance: 0,
                user_total_payable: 0,
                user_avatar_small: '',
                user_avatar_medium: '',
                user_name: '',
                user_role: '',
                user_created: '',
                user_is_user: false,
                user_is_client: false,

                manager_name: 'не закреплен',
            }
        },
        computed: {
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser',
                currManager: 'getCurrentManager',
            })
        },
        watch: {
            currUser(user) {
                if(!this.isEmptyObject(user)) {
                    this.user_balance = user.balance_humanize;
                    this.user_total_payable = user.total_payable_humanize;
                    this.user_avatar_small = user.avatar_small;
                    this.user_avatar_medium = user.avatar_medium;
                    this.user_name = user.name;
                    this.user_role = user.role;
                    this.user_created = user.created_at_diff;
                    this.user_is_user = user.is_user;
                    this.user_is_client = user.is_client;
                }
            },
            currManager(manager) {
                if(!this.isEmptyObject(manager)) {
                    this.manager_name = manager.name;
                }
            }
        },
        methods: {
            isEmptyObject(obj) {
                for (let i in obj) {
                    if (obj.hasOwnProperty(i)) {
                        return false;
                    }
                }
                return true;
            }
        }
    }
</script>