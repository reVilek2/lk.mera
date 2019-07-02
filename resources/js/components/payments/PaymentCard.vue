<template>
    <div class="payment-cards__box">
        <div class="payment-cards__icon">
            <span class="icon-card">&nbsp;</span>
            <div class="payment-cards__action-mobile">
                <button v-if="!disable_btn" type="button" class="btn btn-success" @click="submit">Оплатить в один клик</button>
                <button v-else type="button" class="btn btn-success" disabled>Оплатить в один клик</button>
            </div>
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

        <modal :name="modalAlert"
               classes="v-modal v-modal-alert"
               :min-width="200"
               :min-height="200"
               :width="'90%'"
               :height="'auto'"
               :max-width="400"
               :adaptive="true"
               :scrollable="true">
            <div class="alert alert-dismissible" :class="modalAlertClass">
                <button type="button" class="close" @click="hideModalAlert">×</button>
                <h4><i class="icon fa fa-ban"></i> Ошибка!</h4>
                <p>
                    {{modalAlertText}}
                </p>
            </div>
        </modal>
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
                modalAlert: 'modal-alert-card'+this.card.id,
                modalAlertText: 'Технические неполадки.',
                modalAlertClass: 'alert-danger',
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
            showModalAlertError (message) {
                if (message) {
                    this.modalAlertText = message;
                }
                this.modalAlertClass = 'alert-danger';
                this.showModalAlert();
            },
            showModalAlertWarning (message) {
                if (message) {
                    this.modalAlertText = message;
                }
                this.modalAlertClass = 'alert-warning';
                this.showModalAlert();
            },
            showModalAlert () {
                this.$modal.show(this.modalAlert);
            },
            hideModalAlert () {
                this.$modal.hide(this.modalAlert);
            },
            submit () {
                if (this.raw_amount > 0 && !this.isUploadingForm) {
                    this.isUploadingForm = true;
                    axios.post(this.payment_url, { card_id: this.card.card_id, amount: this.raw_amount }).then(response => {
                        this.isUploadingForm = false;
                        if (response.data.status === 'success') {
                            this.redirectToCheckPaid(response.data.pay_key)
                        }
                        if (response.data.status === 'error') {
                            if (response.data.errors.hasOwnProperty('amount')) {
                                this.showModalAlertWarning(response.data.errors.amount[0]);
                            }
                        }
                        if (response.data.status === 'exception') {
                            this.showModalAlertError(response.data.message);
                        }
                    }).catch(errors => {
                        console.log('tyt');
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
        },
        mounted() {
        }
    }
</script>