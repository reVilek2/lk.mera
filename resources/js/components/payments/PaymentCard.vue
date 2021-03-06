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
            <div class="payment-cards__item-text">
                {{totalPayable}}
                <div class="radio">
                    <label>
                        <input type="radio" name="card_default" :value="card.id" v-model="card_selected" @change="changeDefaultCard($event)" > Использовать по умолчанию
                    </label>
                </div>
            </div>
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
    import {amountToRaw, isEmptyObject} from "../../libs/utils";
    export default {
        props: {
            card: {
                type: Object,
                default: () => {}
            },
            cardDefault: {
                type: Object,
                default: () => {}
            },
            totalPayable: {
                type: String|Number,
            },
            document: {
                type: Object,
                default: () => null
            },
        },
        data() {
            return {
                modalAlert: 'modal-alert-card'+this.card.id,
                modalAlertText: 'Технические неполадки.',
                modalAlertClass: 'alert-danger',
                payment_url: '/finances/payment/pay-fast',
                payment_check_url: '/finances/check-payment',
                remove_payment_pending_url: '/finances/payment/remove-payment-pending',
                amount: this.totalPayable,
                isUploadingForm:false,
                card_selected: this.checkCardSelected(this.cardDefault),
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
            },
            cardDefault(cardDefault) {
                this.card_selected = this.checkCardSelected(cardDefault);
            },
        },
        methods: {
            checkCardSelected(cardDefault){
                return !isEmptyObject(cardDefault) && cardDefault.id === this.card.id ? cardDefault.id : null;
            },
            changeDefaultCard(event) {
                event.preventDefault();
                this.$emit('newCardDefaultSelected', event.target.value);
            },
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
                    const formData = {};
                    formData.card_id = this.card.id;
                    formData.amount = this.raw_amount;
                    if (this.document) {
                        formData.document = this.document.id;
                    }
                    axios.post(this.payment_url, formData).then(response => {
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
                            if (response.data.hasOwnProperty('pay_key')) {
                                // удаляем не нужную платежку
                                this.removePaymentPending(response.data.pay_key);
                            }
                            this.showModalAlertError('Платёж не прошёл возможно недостаточно средств на счету или превышен суточный лимит карты. Попробуйте повторить попытку позже или воспользуйтесь формой "Оплатить онлайн".');
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
                        this.isUploadingForm = false;
                    });
                }
            },
            removePaymentPending(pay_key) {
                axios.post(this.remove_payment_pending_url, {pay_key: pay_key}).then(response => {
                    //console.log(response.data);
                }).catch(errors => {
                    //console.log(errors);
                });
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
