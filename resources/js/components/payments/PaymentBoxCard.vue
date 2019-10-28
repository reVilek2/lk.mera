<template>
    <div class="payment-boxes__item" :class="{ 'active': active }">
        <div class="payment-boxes__header">
            <div class="payment-boxes__header-icon">
                <span class="icon-cards">&nbsp;</span>
                <div class="payment-boxes__header-action-mobile">
                    <div class="payment-boxes__header-action-close">
                        <button type="button" class="btn btn-box-tool" @click="closed"><i class="fa fa-times"></i></button>
                    </div>
                    <button type="button" class="btn btn-success btn-sm payment-boxes__header-action-open" @click="opened"><i class="fa fa-arrow-right"></i></button>
                </div>
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
    import {VMoney} from 'v-money';
    import {amountToRaw} from "../../libs/utils";
    export default {
        props: {
            moneyParam: {
                type: Object,
                default: () => {}
            },
            active: {
                type: Boolean,
                default: () => false
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
                modalAlert: 'modal-alert-pay-card',
                modalAlertText: 'Технические неполадки.',
                modalAlertClass: 'alert-danger',
                payment_url: '/finances/payment',
                payment_type: 'card',
                save_card: false,
                amount: this.totalPayable,
                money: this.moneyParam,
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
            closed() {
                this.$emit('itemCardChangeActive', false);
            },
            opened() {
                this.$emit('itemCardChangeActive', true);
            },
            submit() {
                if (this.raw_amount > 0 && !this.isUploadingForm) {
                    this.isUploadingForm = true;
                    const formData = {};
                    formData.payment_type = this.payment_type;
                    formData.save_card = this.save_card;
                    formData.amount = this.raw_amount;
                    if (this.document) {
                        formData.document = this.document.id;
                    }
                    axios.post(this.payment_url, formData).then(response => {
                        this.isUploadingForm = false;
                        if (response.data.status === 'success') {
                            this.redirectToPaid(response.data.pay_link);
                        }
                        if (response.data.status === 'error') {
                            if (response.data.errors.hasOwnProperty('amount')) {
                                this.showModalAlertWarning(response.data.errors.amount[0]);
                            }
                            if (response.data.errors.hasOwnProperty('save_card')) {
                                this.showModalAlertWarning(response.data.errors.save_card[0]);
                            }
                            if (response.data.errors.hasOwnProperty('payment_type')) {
                                this.showModalAlertWarning(response.data.errors.payment_type[0]);
                            }
                        }
                        if (response.data.status === 'exception') {
                            this.showModalAlertError(response.data.message);
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
            redirectToPaid(pay_link) {
                if (pay_link) {
                    window.location.href = pay_link;
                    //window.open(pay_link);
                }
            }
        },
        mounted() {
        },
        directives: {money: VMoney}
    }
</script>
