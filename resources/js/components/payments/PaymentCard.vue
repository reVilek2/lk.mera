<template>
    <div class="payment-cards__box">
        <div class="payment-cards__icon">
            <span class="icon-card">&nbsp;</span>
        </div>
        <div class="payment-cards__item">
            <div class="payment-cards__item-title">Карта: {{card.pan}}</div>
            <div class="payment-cards__item-text">{{totalPayable}}</div>
        </div>
        <div class="payment-cards__action">
            <button v-if="!disable_btn" type="button" class="btn btn-success" @click="submit">Оплатить в один клик</button>
            <button v-else type="button" class="btn btn-success" disabled>Оплатить в один клик</button>
        </div>

        <div class="loader" :class="{'active': isUploadingForm}">
            <div class="loader__item">
                <div class="loader__dot"></div>
                <div class="loader__dot"></div>
                <div class="loader__dot"></div>
                <div class="loader__dot"></div>
                <div class="loader__dot"></div>
                <div class="loader__dot"></div>
                <div class="loader__dot"></div>
                <div class="loader__dot"></div>
                <div class="loader__dot"></div>
                <div class="loader__dot"></div>
            </div>
        </div>
    </div>
</template>
<script>
    import {amountToRaw} from "../../libs/utils";
    export default {
        props: {
            card: {
                type: Object,
                default: () => {}
            },
            totalPayable: {
                type: String|Number,
            },
        },
        data() {
            return {
                payment_url: '/finances/payment/pay-fast',
                payment_check_url: '/finances/check-payment',
                amount: this.totalPayable,
                isUploadingForm:false,
            }
        },
        computed: {
            raw_amount: function () {
                return amountToRaw(this.amount ? this.amount : 0)
            },
            disable_btn: function () {
                return amountToRaw(this.amount ? this.amount : 0) === 0;
            },
        },
        watch: {
            totalPayable(val) {
                this.amount = val;
            }
        },
        methods: {
            submit () {
                if (this.raw_amount > 0 && !this.isUploadingForm) {
                    this.isUploadingForm = true;
                    axios.post(this.payment_url, { card_id: this.card.card_id, amount: this.raw_amount }).then(response => {
                        this.isUploadingForm = false;
                        if (response.data.status === 'success') {
                            this.redirectToCheckPaid(response.data.pay_key)
                        }
                        if (response.data.status === 'error') {
                            console.log(response.data.errors);
                            new Noty({
                                type: 'error',
                                text: 'Произошла ошибка.',
                                layout: 'topRight',
                                timeout: 5000,
                                progressBar: true,
                                theme: 'metroui',
                            }).show();
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
            redirectToCheckPaid(pay_key) {
                if (this.payment_check_url+'?pay_key='+pay_key) {
                    window.location.href = this.payment_check_url+'?pay_key='+pay_key;
                }
            }
        }
    }
</script>