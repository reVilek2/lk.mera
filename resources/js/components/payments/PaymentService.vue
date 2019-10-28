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
                    <div v-if="!this.document" class="payment-amount-total__text">Итого к оплате:</div>
                    <div v-else class="payment-amount-total__text">К оплате по документу:</div>
                    <input type="text" class="form-control payment-amount-total__input" v-model.lazy="user_total_for_pay" v-money="money">
                </div>
                <div class="payment-boxes-wrapper">
                    <h2 class="page-header">
                        Оплатить онлайн:
                    </h2>
                    <div class="payment-boxes">
                        <payment-box-card :active="itemCardActive"
                                          :total-payable="user_total_for_pay"
                                          :money-param="money"
                                          :document="document"
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
    import {isEmptyObject, amountToExternal, amountToReadable, amountToHumanize} from '../../libs/utils';

    export default {
        props: {
            paymentCards: {
                type: Array,
                default: () => []
            },
            document: {
                type: Object,
                default: () => null
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
                cardDefault: this.getCardDefault(this.paymentCards),
                setCardDefaultUrl: '/finances/payment/set-card-default',
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
                if (!isEmptyObject(user)) {
                    this.user_balance = user.balance_humanize;
                    this.user_total_payable = user.total_payable_humanize;
                    this.user_total_for_pay = !this.document ? this.countTotalForPay(user.balance, user.total_payable) : this.countTotalForPay(user.balance, this.document.amount);
                }
            }
        },
        methods: {
            newCardDefaultSelected(id) {
                this.setNewCardDefault(id);
            },
            setNewCardDefault(id) {
                axios.post(this.setCardDefaultUrl, { card_id: id }).then(response => {
                    if (response.data.status === 'success') {
                        this.cards = response.data.paymentCards;
                        this.cardDefault = this.getCardDefault(this.cards)
                    }
                    if (response.data.status === 'exception') {
                        new Noty({
                            type: 'error',
                            text: response.data.message,
                            layout: 'topRight',
                            timeout: 5000,
                            progressBar: true,
                            theme: 'metroui',
                        }).show();
                    }
                }).catch(errors => {
                    //console.log(errors);
                    new Noty({
                        type: 'error',
                        text: 'Произошла ошибка.',
                        layout: 'topRight',
                        timeout: 5000,
                        progressBar: true,
                        theme: 'metroui',
                    }).show();
                });
            },
            getCardDefault(cards) {
                let cardDefault = null;
                cards.forEach(el => {
                    if (el.card_default === 1) {
                        cardDefault = el;
                    }
                });

                return cardDefault;
            },
            itemCardChangeActive(active) {
                if (active) {
                    this.closedAllItem();
                }

                this.itemCardActive = active;
            },
            closedAllItem() {
                this.itemCardActive = false;
            },
            countTotalForPay(balance, total_payable) {
                let external_total_payable = amountToExternal(total_payable);
                let external_balance = amountToExternal(balance);
                if (external_total_payable > external_balance) {
                    let amount_readable = amountToReadable(external_total_payable - external_balance);
                    this.moneyParams(amount_readable);
                    return amountToHumanize(amount_readable);
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
