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
                       <transaction-table :transactions="transactions"
                                          :transactions_count="transactions_count"></transaction-table>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    import TransactionTable from './TransactionTable';
    import {isEmptyObject} from "../../libs/utils";
    export default {
        props: {
            transactions: {
                type: Object,
                default: () => {}
            },
            transactions_count: {
                type: Number,
                default: () => 0
            },
        },
        components: { transactionTable: TransactionTable },
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
                if(!isEmptyObject(user)) {
                    this.user_balance = user.balance_humanize;
                }
            }
        },
    }
</script>