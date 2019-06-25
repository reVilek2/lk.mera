<template>
    <div class="row">
        <div class="col-lg-12">
            <div class="payment-wrapper">
                <div class="total-payable-header">
                    <div class="total-payable-header__text">Итого к оплате:</div> <input type="text" class="form-control total-payable-header__input" v-model.lazy="totalPayable" v-money="money">
                </div>
                <div v-if="cards.length" class="payment-card-wrapper">
                    <h2 class="page-header">
                        Привязанные карты:
                    </h2>
                    <div class="payment-cards">
                        <payment-card v-for="card in cards" :key="card.id" :card="card" :total-payable="totalPayable"></payment-card>
                    </div>
                </div>
                <div class="payment-boxes-wrapper">
                    <h2 class="page-header">
                        Оплатить онлайн:
                    </h2>
                    <div class="payment-boxes">
                        <payment-box-card :active="itemCardActive" :total-payable="totalPayable" @itemCardChangeActive="itemCardChangeActive"></payment-box-card>
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
                totalPayable: 0,
                money: {
                    decimal: '',
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
                this.totalPayable = this.isEmptyObject(user) ? 0 : user.total_payable;
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
            }
        },
        directives: {money: VMoney}
    }
</script>