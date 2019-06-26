<template>
    <div class="row">
        <div class="col-lg-12">
            <div class="payment-wrapper">
                <div class="payment-balance-info">
                    <div class="payment-balance-info__text">Баланс: {{user_balance}}</div>
                </div>
                <div class="payment-balance-info">
                    <div class="payment-balance-info__text">На оплату: {{user_total_payable}}</div>
                </div>
                <div class="payment-amount-total">
                    <div class="payment-amount-total__text">Итого к оплате:</div> <input type="text" class="form-control payment-amount-total__input" v-model.lazy="user_total_for_pay" v-money="money">
                </div>
                <div v-if="cards.length" class="payment-card-wrapper">
                    <h2 class="page-header">
                        Привязанные карты:
                    </h2>
                    <div class="payment-cards">
                        <payment-card v-for="card in cards"
                                      :key="card.id"
                                      :card="card"
                                      :total-payable="user_total_for_pay"></payment-card>
                    </div>
                </div>
                <div class="payment-boxes-wrapper">
                    <h2 class="page-header">
                        Оплатить онлайн:
                    </h2>
                    <div class="payment-boxes">
                        <payment-box-card :active="itemCardActive"
                                          :total-payable="user_total_for_pay"
                                          :money-param="money"
                                          @itemCardChangeActive="itemCardChangeActive"></payment-box-card>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    import {VMoney} from 'v-money';
    import PaymentCard from './PaymentCard';
    import PaymentBoxCard from './PaymentBoxCard';
    import {amountToExternal, amountToReadable, amountToHumanize} from '../../libs/utils';

    export default {
        props: {
            paymentCards: {
                type: Array,
                default: () => []
            },
        },
        components: { paymentCard: PaymentCard, paymentBoxCard: PaymentBoxCard },
        data() {
            return {
                itemCardActive: false,
                cards: this.paymentCards,
                user_total_payable: '0 руб.',
                user_balance: '0 руб.',
                user_total_for_pay: '0 руб.',
                user_total_payable2: 0,
                money: {
                    decimal: '.',
                    thousands: ' ',
                    prefix: '',
                    suffix: ' руб.',
                    precision: 0,
                    masked: false
                },
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
                if (!this.isEmptyObject(user)) {
                    this.user_balance = user.balance_humanize;
                    this.user_total_payable = user.total_payable_humanize;
                    this.user_total_for_pay = this.countTotalForPay(user.balance, user.total_payable);
                }
            }
        },
        methods: {
            itemCardChangeActive(active) {
                if (active) {
                    this.closedAllItem();
                }

                this.itemCardActive = active;
            },
            closedAllItem() {
                this.itemCardActive = false;
            },
            isEmptyObject(obj) {
                for (let i in obj) {
                    if (obj.hasOwnProperty(i)) {
                        return false;
                    }
                }
                return true;
            },
            countTotalForPay(balance, total_payable) {
                let external_total_payable = amountToExternal(total_payable);
                let external_balance = amountToExternal(balance);
                if (external_total_payable > external_balance) {
                    let amount_external = amountToReadable(external_total_payable - external_balance);
                    this.moneyParams(amount_external);
                    return amountToHumanize(amount_external);
                }
                this.moneyParams(0);
                return 0
            },
            moneyParams(val) {
                if (typeof val === 'number') {
                    val = val.toString();
                }
                let tmp = val.split('.');

                this.money.decimal = '';
                this.money.precision = 0;
                if (tmp[1]) {
                    this.money.decimal = '.';
                    this.money.precision = tmp[1].length;
                }
            },
        },
        directives: {money: VMoney}
    }
</script>