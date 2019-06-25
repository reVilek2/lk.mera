<template>
    <div>
        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header">
                    Остаток средств:
                </h2>
                <div class="row">
                    <div class="col-lg-3">
                        <div class="service-balance">
                            <div class="service-balance__amount">
                                <div class="service-balance__amount-item">
                                    <span>{{balance}}</span>
                                </div>
                                <div class="service-balance__amount-btn">
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
</template>
<script>
    import { mapGetters } from 'vuex';
    export default {
        data: function() {
            return {
                balance: 0,
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
                this.balance = this.isEmptyObject(user) || user.balance === 0 ? '0 руб.' : user.balance_humanize;
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