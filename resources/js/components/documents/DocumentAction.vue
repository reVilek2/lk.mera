<template>
    <div class="col-xs-12">
        <button v-if="btnText.value"
            type="button"
            class="btn"
            :class="btnText.class"
            :disabled="!btnText.action || isUploadingForm"
            @click="beforeOnSubmit(btnText.action, btnText.action_code)"
        >
            <span v-if="isUploadingForm" class="preloader preloader-sm"></span>
            {{btnText.value}}
        </button>
        <modal :name="modalConfirm"
            classes="v-modal"
            :min-width="200"
            :min-height="200"
            :width="'90%'"
            :height="'auto'"
            :max-width="500"
            :adaptive="true"
            :scrollable="true">
            <div class="v-modal-header">
                <button type="button" class="close" @click="hideModalConfirmAndReset">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="v-modal-title">Предупреждение</h4>
            </div>
            <div class="v-modal-body">
                <span>{{action_message}}</span>
            </div>
            <div class="v-modal-footer">
                <button type="button" class="btn btn-default pull-right" @click="hideModalConfirmAndReset">Нет</button>
                <button type="button" class="btn btn-success" @click="onSuccessConfirm" style="margin-right: 10px">Да</button>
            </div>
        </modal>
        <modal :name="modalCreditFail"
            classes="v-modal"
            :min-width="200"
            :min-height="200"
            :width="'90%'"
            :height="'auto'"
            :max-width="500"
            :adaptive="true"
            :scrollable="true">
            <div class="v-modal-header">
                <button type="button" class="close" @click="hideModalCreditFail">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="v-modal-title">Предупреждение</h4>
            </div>
            <div class="v-modal-body">
                {{credit_fail_message}}
            </div>
        </modal>
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
    import { mapGetters } from 'vuex';
    import {isEmptyObject} from '../../libs/utils';

    export default {
        props: {
            item: {
                type: Object,
                default: () => {}
            },
            signed: {
                type: Number,
                default: () => 0
            },
            paid: {
                type: Number,
                default: () => 0
            },
        },
        data() {
            return {
                container: 'document-action'+this.item.id,
                container_item: 'document-action-item'+this.item.id,
                box: 'document-action-box'+this.item.id,
                modalConfirm: 'document-action-confirm'+this.item.id,
                modalCreditFail: 'document-action-credit-fail'+this.item.id,
                credit_fail_message: '',
                paid_url: '/finances/payment',
                paid_fast_url: '/finances/payment/pay-fast',
                check_pay_fast_url: '/finances/check-payment',
                remove_payment_pending_url: '/finances/payment/remove-payment-pending',
                statusSigned: this.signed,
                statusPaid: this.paid,
                isUploadingForm:false,
                is_active_item:true,
                action: ()=> {},
                action_message: '',
                missingAmount: 0,
                paymentCardDefault: {},
                modalAlert: 'modal-alert-document'+this.item.id,
                modalAlertText: 'Технические неполадки.',
                modalAlertClass: 'alert-danger',
            }
        },
        watch: {
            signed(val) {
                this.statusSigned = val;
                this.checkActiveItem();
            },
            paid(val) {
                this.statusPaid = val;
                this.checkActiveItem();
            }
        },
        computed: {
            btnText: function () {
                if (this.currUser.is_admin) {
                    if (!this.statusSigned && !this.statusPaid) {
                        return {
                            action: this.actionDelete,
                            action_code: 'delete',
                            value: 'Удалить',
                            class: {'btn-danger': true}
                        };
                    }
                }

                if(this.currUser.is_client){
                    if (!this.statusSigned || !this.statusPaid) {
                        return {
                            action: this.actionSignedAndPaid,
                            action_code: 'signed_and_paid',
                            value: 'Подписать и оплатить',
                            class: {'btn-danger': true}
                        };
                    } else {
                        return {
                            action: false,
                            action_code: false,
                            value: 'Оплачено',
                            class: {'btn-default': true}
                        };
                    }
                }

                return {action: () => {}, action_code: '', value: ''};
            },
            btnList: function () {
                return [];
            },
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
        methods: {
            // экшены по нажатию на кнопку
            actionSignedAndPaid() {
                if (this.currUser.is_admin) {
                    let data = {signed:1, paid: 1};
                    let url = '/documents/'+this.item.id+'/set-paid';
                    this.submitForm(url, data);
                } else if (this.currUser.is_client) {
                    this.submitForm('/documents/'+this.item.id+'/set-signed', {signed:1}, this.actionPaid);
                }
            },
            actionPaid() {
                this.submitForm('/documents/'+this.item.id+'/set-paid', {paid:1});
            },
            actionSigned() {
                if (this.currUser.is_admin || this.currUser.is_client) {
                    this.submitForm('/documents/'+this.item.id+'/set-signed', {signed:1});
                }
            },
            actionDelete(){
                if (this.currUser.is_admin) {
                    this.submitForm('/documents/'+this.item.id+'/delete', {});
                }
            },
            // end

            submitForm(url, data, callback = () => {}) {
                this.resetChanges();
                if (!this.isUploadingForm) {
                    this.isUploadingForm = true;
                    axios.post(url, data).then(response => {
                        this.isUploadingForm = false;

                        if (response.data.status === 'success') {
                            if (response.data.hasOwnProperty('document')) {
                                this.setNewChange(response.data.document.signed ? 1 : 0, response.data.document.paid ? 1 : 0);
                            }

                            // обновляем currentUser в storage
                            if (response.data.hasOwnProperty('client')) {
                                if (parseInt(response.data.client.id) === parseInt(this.currUser.id)) {
                                    this.$store.dispatch('setCurrentUser', response.data.client);
                                }
                            }

                            if (response.data.hasOwnProperty('action')) {
                                if( response.data.action == 'delete') {
                                    this.$emit('deleteDocument');
                                }
                            }

                        }
                        if (response.data.status === 'missingAmount') {
                            this.showModalCreditFail('Недостаточно средств на балансе');
                        }
                        // выполнить функцию после ajax
                        callback();

                    }).catch(errors => {
                        //console.log(errors);
                        this.isUploadingForm = false;
                    });
                }
            },

            stepMissingAmount() {
                if (this.missingAmount > 0) {
                    if (!isEmptyObject(this.paymentCardDefault)) {
                        this.payFastDocument(this.paymentCardDefault.id);
                    } else {
                        this.showModalCreditFail('Недостаточно средств на балансе и не выбрана карта для быстрой оплаты. Перейти на страницу ручной оплаты?');
                    }
                }
            },

            payFastDocument(card_id) {
                if (!this.isUploadingForm) {
                    this.isUploadingForm = true;

                    axios.post(this.paid_fast_url, {card_id: card_id, amount: this.missingAmount, document: this.item.id }).then(response => {
                        if (response.data.status === 'success') {
                            this.checkPayFastDocument(response.data.pay_key);
                        }
                        if (response.data.status === 'error') {
                            if (response.data.errors.hasOwnProperty('amount')) {
                                this.showModalAlertWarning(response.data.errors.amount[0]);
                            }
                            this.isUploadingForm = false;
                        }
                        if (response.data.status === 'exception') {
                            if (response.data.hasOwnProperty('pay_key')) {
                                // удаляем не нужную платежку
                                this.removePaymentPending(response.data.pay_key);
                            }
                            this.showModalCreditFail('Платёж не прошёл возможно недостаточно средств на счету или превышен суточный лимит карты. Перейти на страницу ручной оплаты?');
                            this.isUploadingForm = false;
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

            checkPayFastDocument(pay_key) {
                axios.get(this.check_pay_fast_url+'?pay_key='+pay_key).then(response => {
                    this.isUploadingForm = false;
                    if (response.data.status === 'success') {
                        if (response.data.document && response.data.document_transaction_status === 'success') {
                            this.setNewChange(response.data.document.signed ? 1 : 0, response.data.document.paid ? 1 : 0);
                            // обновляем currentUser в storage
                            if (response.data.hasOwnProperty('client') && response.data.client) {
                                if (parseInt(response.data.client.id) === parseInt(this.currUser.id)) {
                                    this.$store.dispatch('setCurrentUser', response.data.client);
                                }
                            }

                            new Noty({
                                type: 'success',
                                text: 'Оплачено.',
                                layout: 'topRight',
                                timeout: 5000,
                                progressBar: true,
                                theme: 'metroui',
                            }).show();
                        }
                    }

                    if (response.data.status === 'exception') {
                        this.showModalAlertError(response.data.message);
                        this.isUploadingForm = false;
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
            },

            removePaymentPending(pay_key) {
                axios.post(this.remove_payment_pending_url, {pay_key: pay_key}).then(response => {
                    //console.log(response.data);
                }).catch(errors => {
                    //console.log(errors);
                });
            },

            redirectToPaid () {
                window.location.href = this.missingAmount > 0 ? this.paid_url+'?document='+this.item.id : this.paid_url;
            },

            setNewChange (signed, paid) {
                let newItem = this.item;
                newItem.signed = signed;
                newItem.paid = paid;
                this.$emit('updateDocument', newItem);
            },
            isNeedShow () {
                return this.currUser.is_admin || this.currUser.is_manager || this.currUser.is_client
            },
            checkActiveItem () {
                if (this.currUser.is_manager && !this.currUser.is_admin && !this.currUser.is_client) {
                    this.is_active_item = !this.statusPaid;
                } else {
                    this.is_active_item = !this.statusSigned || !this.statusPaid;
                }
            },
            setSignedAndPaid(val) {
                let arrStatus = val.split(':');
                this.statusSigned = parseInt(arrStatus[0]);
                this.statusPaid = parseInt(arrStatus[1]);
            },
            resetChanges() {
                this.action = () => {};
                this.action_message = '';
            },
            showModalConfirm () {
                this.$modal.show(this.modalConfirm);
            },
            hideModalConfirm () {
                this.$modal.hide(this.modalConfirm);
            },
            showModalCreditFail (message) {
                this.credit_fail_message = message;
                this.$modal.show(this.modalCreditFail);
            },
            hideModalCreditFail () {
                this.$modal.hide(this.modalCreditFail);
            },
            hideModalConfirmAndReset () {
                this.hideModalConfirm();
                this.resetChanges();
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
            beforeOnSubmit(action, action_code) {
                this.action = action;
                if (action_code === 'signed_and_paid') {
                    this.action_message = 'Вы уверены, что хотите подписать и оплатить отчет? Отменить подписание и оплату будет невозможно.';
                } else if (action_code === 'signed') {
                    this.action_message = 'Вы уверены, что хотите подписать отчет? Отменить подписание будет невозможно.';
                } else if (action_code === 'paid') {
                    this.action_message = 'Вы уверены, что хотите оплатить отчет? Отменить оплату будет невозможно.';
                } else if (action_code === 'delete') {
                    this.action_message = 'Вы уверены, что хотите удалить отчет? Отменить удаление будет невозможно.';
                }
                this.showModalConfirm();
            },
            onSuccessConfirm () {
                this.action();
                this.hideModalConfirm();
            },
        },
        created() {
            this.checkActiveItem();
        }
    }
</script>
