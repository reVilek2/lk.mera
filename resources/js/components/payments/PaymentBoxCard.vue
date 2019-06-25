<template>
    <div class="payment-boxes__item" :class="{ 'active': active }">
        <div class="payment-boxes__header">
            <div class="payment-boxes__header-icon">
                <span class="icon-cards">&nbsp;</span>
            </div>
            <div class="payment-boxes__header-item">
                <div class="payment-boxes__header-item-title">
                    Оплатить с помощью банковской карты
                </div>
                <div class="payment-boxes__header-item-text">{{totalPayable}}</div>
            </div>
            <div class="payment-boxes__header-action">
                <div class="payment-boxes__header-action-close">
                    <button type="button" class="btn btn-box-tool" @click="closed"><i class="fa fa-times"></i></button>
                </div>
                <button type="button" class="btn btn-success btn-sm payment-boxes__header-action-open" @click="opened"><i class="fa fa-arrow-right"></i></button>
            </div>
        </div>
        <div class="payment-boxes__content">
            <div class="payment-boxes__content-details">
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" checked="checked" v-model="save_card">
                            Привязать карту для дальнейших покупок
                        </label>
                    </div>
                </div>
                <p>Привязка карты ускоряет и облегчает работу. Карта будет использоваться для быстрой оплаты в один клик.</p>
            </div>
            <div class="payment-boxes__content-action">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="input-group payment-boxes__content-box_amount">
                            <input type="text" class="form-control" v-model.lazy="amount" v-money="money">
                            <span class="input-group-btn">
                                <button v-if="!disable_btn" type="button" class="btn btn-success btn-flat" @click="submit">Оплатить</button>
                                <button v-else type="button" class="btn btn-success btn-flat" disabled>Оплатить</button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import {VMoney} from 'v-money';
    export default {
        props: {
            active: {
                type: Boolean,
                default: () => false
            },
            totalPayable: {
                type: String|Number,
            },
        },
        data() {
            return {
                payment_url: '/finances/payment',
                payment_type: 'card',
                save_card: true,
                amount: this.totalPayable,
                money: {
                    decimal: '',
                    thousands: ' ',
                    prefix: '',
                    suffix: ' руб.',
                    precision: 0,
                    masked: false
                },
                isUploadingForm:false,
            }
        },
        computed: {
            raw_amount: function () {
                return this.getRawAmount(this.amount ? this.amount : 0)
            },
            disable_btn: function () {
                return parseInt(this.getRawAmount(this.amount ? this.amount :0)) === 0;
            },
        },
        watch: {
            totalPayable(val) {
                this.amount = val;
            }
        },
        methods: {
            getRawAmount(val) {
                if (val) {
                    return val.replace(/\D/g, '');
                }
                return 0;
            },
            closed() {
                this.$emit('itemCardChangeActive', false);
            },
            opened() {
                this.$emit('itemCardChangeActive', true);
            },
            submit() {
                if (parseInt(this.raw_amount) > 0 && !this.isUploadingForm) {
                    this.isUploadingForm = true;
                    axios.post(this.payment_url, {payment_type:this.payment_type, save_card:this.save_card, amount: this.raw_amount}).then(response => {
                        this.isUploadingForm = false;
                        console.log(response);
                        if (response.data.status === 'success') {
                            this.redirectToPaid(response.data.pay_link);
                        }
                    }).catch(errors => {
                        console.log(errors);
                        new Noty({
                            type: 'error',
                            text: 'Произошла ошибка.',
                            layout: 'topRight',
                            timeout: 5000,
                            progressBar: true,
                            theme: 'metroui',
                        }).show();
                        this.isUploadingForm = false;
                    });
                }
            },
            redirectToPaid(pay_link) {
                if (pay_link) {
                    // window.location.href = pay_link;
                    window.open(pay_link);
                }
            }
        },
        mounted() {
            console.log('payment service mounted');
        },
        directives: {money: VMoney}
    }
</script>