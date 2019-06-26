<template>
    <div>
        <div class="row">
            <div class="col-lg-12">
                <div class="finance-wrapper">
                    <h2 class="page-header">
                        Остаток средств:
                    </h2>
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="finance-balance">
                                <div class="finance-balance__amount">
                                    <div class="finance-balance__amount-item">
                                        <span>{{user_balance}}</span>
                                    </div>
                                    <div class="finance-balance__amount-btn">
                                        <a href="/finances/payment" class="btn btn-success">
                                            <i class="fa fa-money"></i>
                                            Пополнить
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h2 class="page-header">
                        <i class="fa fa-clock-o"></i> История операций:
                    </h2>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    export default {
        data: function() {
            return {
                user_balance: 0,
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
                if(!this.isEmptyObject(user)) {
                    this.user_balance = user.balance_humanize;
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