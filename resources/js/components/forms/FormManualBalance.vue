<template>
    <div>
        <div class="box-profile-popup" :class="{'active': active}">
            <h4>Пополнение/списание баланса</h4>
            <div class="form-group">
                <label class="control-label">выберите тип операции:</label>
                <vue-select :options="transactionTypeOptions"
                            :name="'transaction_type'"
                            :placeholder="'выберите тип операции'"
                            :search="false"
                            :allowClear="false"
                            :validate="transactionTypeValidate"
                            v-model="transactionTypeSelected"
                            @click="setDefaultValidateTransactionType($event)"></vue-select>
            </div>
            <div class="form-group">
                <label class="control-label">введите сумму:</label>
                <vue-input-text :name="'transaction_amount'"
                                :placeholder="'введите сумму'"
                                v-model="transactionAmount"
                                :validate="transactionAmountValidate"
                                @focus="setDefaultValidateTransactionAmount($event)"></vue-input-text>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <button type="button" class="btn btn-danger pull-right box-profile-popup__cancel-btn" @click="closeBalanceBox()">отмена</button>
                    <button type="button" class="btn btn-success pull-right box-profile-popup__success-btn" @click="beforeOnSubmit()">сохранить</button>
                </div>
            </div>
        </div>
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
                <button type="button" class="close" @click="hideModalConfirm">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="v-modal-title">Предупреждение</h4>
            </div>
            <div class="v-modal-body">
                Вы уверенны, что хотите изменить баланс?
            </div>
            <div class="v-modal-footer">
                <button type="button" class="btn btn-default pull-right" @click="hideModalConfirm">Нет</button>
                <button type="button" class="btn btn-success" @click="onSubmit()" style="margin-right: 10px">Да</button>
            </div>
        </modal>
    </div>
</template>
<script>
    import Select from '../forms/Select';
    import InputText from '../forms/InputText';

    export default {
        components: {
            vueSelect: Select,
            vueInputText: InputText,
        },
        props: {
            active: {
                type: Boolean,
                default: () => false
            },
            profileUser: {
                type: Object,
                default: () => {}
            },
        },
        data() {
            return {
                modalConfirm: 'change-balance-confirm',
                clearData: false,
                transactionTypeSelected: null,
                transactionTypeOptions: [],
                transactionTypeValidate: {
                    valid: true,
                    message: '',
                    show: false
                },

                transactionAmount: null,
                transactionAmountValidate: {
                    valid: true,
                    message: '',
                    show: false
                },

                formValid: false,
                isUploadingForm: false,
            }
        },
        methods: {
            closeBalanceBox() {
                this.clearBalanceForm();
                this.$emit('closeBalanceBoxEvent', 'closed balance box');
            },

            showModalConfirm () {
                this.$modal.show(this.modalConfirm);
            },

            hideModalConfirm () {
                this.$modal.hide(this.modalConfirm);
            },

            setTransactionTypeOptions () {
                this.transactionTypeOptions = [{ text:'Ручное пополнение счета', id:'manual_in' }, { text:'Ручное списание со счета', id:'manual_out' }];
                this.transactionTypeSelected = 'manual_in';
            },

            setDefaultValidateTransactionType() {
                this.transactionTypeValidate.valid = true;
                this.transactionTypeValidate.message = '';
                this.transactionTypeValidate.show = false;
            },

            setDefaultValidateTransactionAmount() {
                this.transactionAmountValidate.valid = true;
                this.transactionAmountValidate.message = '';
                this.transactionAmountValidate.show = false;
            },

            beforeOnSubmit() {
                this.formValid = this.validate();
                if (this.formValid) {
                    this.showModalConfirm();
                }
            },

            clearBalanceForm() {
                this.transactionTypeSelected = null;
                this.transactionAmount = null;
                this.formValid = false;

                // отчистка валидации
                this.setDefaultValidateTransactionType();
                this.setDefaultValidateTransactionAmount();

                this.setTransactionTypeOptions();
            },

            onSubmit() {
                if (this.formValid && !this.isUploadingForm) {
                    this.isUploadingForm = true;
                    const formData = new FormData();
                    formData.append('transaction_type', this.transactionTypeSelected);
                    formData.append('amount', this.transactionAmount);

                    axios.post('/users/'+this.profileUser.id+'/change-balance', formData).then(response => {
                        if (response.data.status === 'success') {
                            if (response.data.hasOwnProperty('user') && response.data.hasOwnProperty('transaction')) {
                                     if (response.data.transaction.status.code === 'success') {
                                    new Noty({
                                        type: 'success',
                                        text: 'Баланс успешно изменен.',
                                        layout: 'topRight',
                                        timeout: 5000,
                                        progressBar: true,
                                        theme: 'metroui',
                                    }).show();
                                } else {
                                    new Noty({
                                        type: 'error',
                                        text: 'При изменении баланса произошла ошибка.',
                                        layout: 'topRight',
                                        timeout: 5000,
                                        progressBar: true,
                                        theme: 'metroui',
                                    }).show();
                                }
                                this.closeBalanceBox();
                                this.$emit('changedBalanceDone', response.data.user);
                            }
                        }
                        if (response.data.status === 'error') {
                            if (response.data.errors.hasOwnProperty('transaction_type')) {
                                this.transactionTypeValidate.valid = false;
                                this.transactionTypeValidate.message = response.data.errors.transaction_type[0];
                                this.transactionTypeValidate.show = true;
                            }
                            if (response.data.errors.hasOwnProperty('amount')) {
                                this.transactionAmountValidate.valid = false;
                                this.transactionAmountValidate.message = response.data.errors.amount[0];
                                this.transactionAmountValidate.show = true;
                            }
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
                        this.isUploadingForm = false;
                        this.hideModalConfirm();
                    }).catch(errors => {
                        console.log(errors);
                        this.isUploadingForm = false;
                        this.hideModalConfirm();
                    });
                }
            },

            validate () {
                let valid = true;
                if (!this.validateTransactionType()) {
                    valid = false;
                }

                if (!this.validateAmount()) {
                    valid = false;
                }

                return valid;
            },
            
            validateTransactionType() {
                let valid = true;
                this.setDefaultValidateTransactionType();

                if (!this.transactionTypeSelected ||
                    this.transactionTypeSelected.length === 0) {
                    valid = false;
                    this.transactionTypeValidate.valid = false;
                    this.transactionTypeValidate.message = 'выберите тип операции';
                    this.transactionTypeValidate.show = true;
                }

                return valid;
            },

            validateAmount() {
                let amountRegExp = new RegExp('^([0-9]+([.][0-9]+)?)$','i');
                let valid = true;
                this.setDefaultValidateTransactionAmount();

                if (!this.transactionAmount ||
                    this.transactionAmount.length === 0) {
                    valid = false;
                    this.transactionAmountValidate.valid = false;
                    this.transactionAmountValidate.message = 'Укажите сумму';
                    this.transactionAmountValidate.show = true;
                } else if (!amountRegExp.test(this.transactionAmount)) {
                    valid = false;
                    this.transactionAmountValidate.valid = false;
                    this.transactionAmountValidate.message = 'Сумма должна быть числом или числом с плавающей точкой.';
                    this.transactionAmountValidate.show = true;
                }

                return valid;
            },
        },
        created(){
            this.setTransactionTypeOptions();
        }
    };
</script>
