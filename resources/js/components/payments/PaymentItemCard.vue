<template>
    <div class="payment-boxes__item" :class="{ 'active': active }">
        <div class="payment-boxes__header">
            <div class="payment-boxes__header-icon">
                <span class="icon-cards">&nbsp;</span>
            </div>
            <div class="payment-boxes__header-item">
                <span class="payment-boxes__header-item-title">
                    Оплатить с помощью банковской карты
                </span>
            </div>
            <div class="payment-boxes__header-action">
                <div class="payment-boxes__header-action-close">
                    <button type="button" class="btn btn-box-tool" @click="closed"><i class="fa fa-times"></i></button>
                </div>
                <button type="button" class="btn btn-success btn-sm payment-boxes__header-action-open" @click="opened"><i class="fa fa-arrow-right"></i></button>
            </div>
        </div>
        <div v-if="active" class="payment-boxes__content">
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
                            <!--<input type="text" class="form-control" v-model.lazy="amount" v-money="money">-->
                            <input type="text" class="form-control" v-model="amount">
                            <span class="input-group-btn"><button type="button" class="btn btn-success btn-flat" @click="submit">Оплатить</button></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import {VMoney, format} from 'v-money';
    export default {
        props: {
            active: {
                type: Boolean,
                default: () => false
            },
        },
        data() {
            return {
                payment_url: '/finances/payment',
                amount: 0,
                payment_type: 'card',
                save_card: true,
                // money: {
                //     decimal: '',
                //     thousands: ' ',
                //     prefix: '',
                //     suffix: ' руб.',
                //     precision: 0,
                //     masked: false
                // },
                isUploadingForm:false,
            }
        },
        methods: {
            closed() {
                this.$emit('itemCardChangeActive', false);
            },
            opened() {
                this.$emit('itemCardChangeActive', true);
            },
            submit() {
                if (parseInt(this.amount) > 0 && !this.isUploadingForm) {
                    this.isUploadingForm = true;
                    axios.post(this.payment_url, {payment_type:this.payment_type, save_card:this.save_card, amount: this.amount}).then(response => {
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
                    window.location.href = pay_link;
                    // window.open(pay_link)
                }
            }
        },
        mounted() {
            console.log('payment service mounted');
        },
        directives: {money: VMoney}
    }
</script>