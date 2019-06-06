<template>
    <div>
        <modal name="document-create"
               classes="v-modal"
               :min-width="200"
               :min-height="200"
               :width="'90%'"
               :height="'auto'"
               :max-width="500"
               :adaptive="true"
               :scrollable="true">
            <div class="v-modal-header">
                <button type="button" class="close" @click="hideModal">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="v-modal-title">Добавление документа</h4>
            </div>
            <div class="v-modal-body">

                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">От кого</label>

                        <div class="col-sm-8">
                            <vue-select :options="managerOptions"
                                        :name="'manager'"
                                        :placeholder="'Выберите один из вариантов'"
                                        :search="true"
                                        :allowClear="true"
                                        :disabled="managerSelectDisabled"
                                        :validate="managerValidate"
                                        v-model="managerSelected"
                                        @click="setDefaultValidateManager($event)"></vue-select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">ФИО клиента:</label>

                        <div class="col-sm-8">
                            <vue-select :options="clientOptions"
                                        :name="'client'"
                                        :placeholder="'Выберите один из вариантов'"
                                        :search="true"
                                        :allowClear="true"
                                        :disabled="clientSelectDisabled"
                                        v-model="clientSelected"
                                        :validate="clientValidate"
                                        @click="setDefaultValidateClient($event)"></vue-select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Название документа</label>

                        <div class="col-sm-8">
                            <vue-input-text :name="'document'"
                                          :placeholder="'Введите название документа'"
                                          v-model="documentValue"
                                          :validate="documentValidate"
                                          @focus="setDefaultValidateDocument($event)"></vue-input-text>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Сумма для оплаты</label>

                        <div class="col-sm-8">
                            <vue-input-text :name="'amount'"
                                            :placeholder="'Укажите сумму'"
                                            v-model="amountValue"
                                            :validate="amountValidate"
                                            @focus="setDefaultValidateAmount($event)"></vue-input-text>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Документ</label>

                        <div class="col-sm-8">
                            <vue-input-file :name="'file'"
                                            :placeholder="'Выберите документ'"
                                            v-model="fileValue"
                                            :validate="fileValidate"
                                            @focus="setDefaultValidateFile($event)"></vue-input-file>
                        </div>
                    </div>
                </div>
            </div>
            <div class="v-modal-footer">
                <button type="button" class="btn btn-default pull-left" @click="hideModal">Отмена</button>
                <button type="button" class="btn btn-primary" @click="beforeOnSubmit">Добавить</button>
            </div>
        </modal>
        <modal name="document-create-confirm"
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
                После отправки удалить документ из личного кабинет клиента сможет только администратор. Отправляем?
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
    import InputFile from '../forms/InputFile';

    export default {
        components: {
            vueSelect: Select,
            vueInputText: InputText,
            vueInputFile: InputFile,
        },
        props: {
            managers: {
                type: Array,
                default: () => []
            },
            currentUser: {
                type: Object,
                default: () => {}
            },
            clearForm: {
                type: Boolean,
                default: () => false
            },
        },
        data() {
            return {
                clearData: this.clearForm,
                managerSelected: null,
                managerOptions: [],
                managerValidate: {
                    valid: true,
                    message: '',
                    show: false
                },
                managerSelectDisabled: true,
                clientSelected: null,
                clientOptions: [],
                clientValidate: {
                    valid: true,
                    message: '',
                    show: false
                },
                clientSelectDisabled: true,
                documentValue: null,
                documentValidate: {
                    valid: true,
                    message: '',
                    show: false
                },
                amountValue: null,
                amountValidate: {
                    valid: true,
                    message: '',
                    show: false
                },
                fileValue: null,
                fileValidate: {
                    valid: true,
                    message: '',
                    show: false
                },
                formValid: false,
                isUploadingForm: false,
                user: this.currentUser,
                is_user:false,
                is_client:false,
                is_manager:false,
                is_admin:false,
            }
        },
        watch: {
            managerSelected(val) {
                this.setClientOptions();
            },
            clearForm(val) {
                this.clearData = val;
                if (this.clearData === true) {
                    this.clearDocumentForm();
                }
            }
        },
        methods: {
            showModalConfirm () {
                this.hideModal();
                this.$modal.show('document-create-confirm');
            },
            hideModalConfirm () {
                this.$modal.hide('document-create-confirm');
                this.showModal();
            },
            showModal () {
                this.$modal.show('document-create');
            },
            hideModal () {
                this.$modal.hide('document-create');
            },
            setManagerOptions () {
                let managerOptions = this.managerOptions;
                let currentUserId = 0;
                if (this.user.hasOwnProperty('id')) {
                    currentUserId = this.user.id;
                }

                this.managers.forEach(el => {
                    if (parseInt(currentUserId) === parseInt(el.id)) {
                        this.managerSelected = el.id
                    }
                    managerOptions.push({ text:el.name, id:el.id });
                });

                this.managerOptions = managerOptions;
                this.managerSelectDisabled = !this.is_admin;
            },

            setClientOptions () {
                this.clientSelectDisabled = true;
                this.clientSelected = null;
                this.clientOptions = [];
                if (this.managerSelected) {
                    this.managers.forEach(el => {
                        if (parseInt(this.managerSelected) === parseInt(el.id)) {
                            el.clients.forEach(client => {
                                this.clientOptions.push({ text:client.name, id:client.id });
                            });
                            this.clientSelectDisabled = false;
                        }
                    });
                }
            },

            setDefaultValidateManager() {
                this.managerValidate.valid = true;
                this.managerValidate.message = '';
                this.managerValidate.show = false;
            },

            setDefaultValidateClient() {
                this.clientValidate.valid = true;
                this.clientValidate.message = '';
                this.clientValidate.show = false;
            },

            setDefaultValidateDocument() {
                this.documentValidate.valid = true;
                this.documentValidate.message = '';
                this.documentValidate.show = false;
            },

            setDefaultValidateAmount() {
                this.amountValidate.valid = true;
                this.amountValidate.message = '';
                this.amountValidate.show = false;
            },

            setDefaultValidateFile() {
                this.fileValidate.valid = true;
                this.fileValidate.message = '';
                this.fileValidate.show = false;
            },
            beforeOnSubmit() {
                this.formValid = this.validate();
                if (this.formValid) {
                    this.showModalConfirm();
                }
            },
            clearDocumentForm() {
                this.managerSelected = null;
                this.clientSelected = null;
                this.documentValue = null;
                this.amountValue = null;
                this.fileValue = null;
                this.formValid = false;

                // отчистка валидации
                this.managerValidate.valid = true;
                this.managerValidate.message = '';
                this.managerValidate.show = false;

                this.clientValidate.valid = true;
                this.clientValidate.message = '';
                this.clientValidate.show = false;

                this.documentValidate.valid = true;
                this.documentValidate.message = '';
                this.documentValidate.show = false;

                this.amountValidate.valid = true;
                this.amountValidate.message = '';
                this.amountValidate.show = false;

                this.fileValidate.valid = true;
                this.fileValidate.message = '';
                this.fileValidate.show = false;

                this.setManagerOptions();
                this.$emit('formCleared', 'cleared success');
            },
            onSubmit() {
                if (this.formValid && !this.isUploadingForm) {

                    this.isUploadingForm = true;
                    const formData = new FormData();
                    formData.append('manager', this.managerSelected);
                    formData.append('client', this.clientSelected);
                    formData.append('amount', this.amountValue);
                    formData.append('name', this.documentValue);
                    formData.append('file', this.fileValue, this.fileValue.name);

                    axios.post('/documents', formData).then(response => {
                        if (response.data.status === 'success') {
                            if (response.data.hasOwnProperty('document')) {
                                this.$emit('createdDocument', response.data.document);
                            }
                        }
                        if (response.data.status === 'error') {
                            if (response.data.errors.hasOwnProperty('file')) {
                                this.fileValidate.valid = false;
                                this.fileValidate.message = response.data.errors.file[0];
                                this.fileValidate.show = true;
                            }
                            if (response.data.errors.hasOwnProperty('name')) {
                                this.documentValidate.valid = false;
                                this.documentValidate.message = response.data.errors.name[0];
                                this.documentValidate.show = true;
                            }
                            if (response.data.errors.hasOwnProperty('client')) {
                                this.clientValidate.valid = false;
                                this.clientValidate.message = response.data.errors.client[0];
                                this.clientValidate.show = true;
                            }
                            if (response.data.errors.hasOwnProperty('manager')) {
                                this.managerValidate.valid = false;
                                this.managerValidate.message = response.data.errors.manager[0];
                                this.managerValidate.show = true;
                            }
                            if (response.data.errors.hasOwnProperty('amount')) {
                                this.amountValidate.valid = false;
                                this.amountValidate.message = response.data.errors.amount[0];
                                this.amountValidate.show = true;
                            }
                        }
                        this.isUploadingForm = false;
                    }).catch(errors => {
                        console.log(errors);
                        this.isUploadingForm = false;
                    });

                }
            },

            validate () {
                let valid = true;
                if (!this.validateManager()) {
                    valid = false;
                }

                if (!this.validateClient()) {
                    valid = false;
                }

                if (!this.validateDocument()) {
                    valid = false;
                }

                if (!this.validateAmount()) {
                    valid = false;
                }

                if (!this.validateFile()) {
                    valid = false;
                }

                return valid;
            },
            
            validateManager() {
                let valid = true;
                this.setDefaultValidateManager();

                if (!this.managerSelected ||
                    this.managerSelected.length === 0) {
                    valid = false;
                    this.managerValidate.valid = false;
                    this.managerValidate.message = 'Выберите один из вариантов';
                    this.managerValidate.show = true;
                }

                return valid;
            },

            validateClient() {
                let valid = true;
                this.setDefaultValidateClient();

                if (!this.clientSelected ||
                    this.clientSelected.length === 0) {
                    valid = false;
                    this.clientValidate.valid = false;
                    this.clientValidate.message = 'Выберите один из вариантов';
                    this.clientValidate.show = true;
                }

                return valid;
            },

            validateDocument() {
                let valid = true;
                this.setDefaultValidateDocument();

                if (!this.documentValue ||
                    this.documentValue.length === 0) {
                    valid = false;
                    this.documentValidate.valid = false;
                    this.documentValidate.message = 'Введите название документа';
                    this.documentValidate.show = true;
                }

                return valid;
            },

            validateAmount() {
                let amountRegExp = new RegExp('^([0-9]+([.][0-9]+)?)$','i');
                let valid = true;
                this.setDefaultValidateAmount();

                if (!this.amountValue ||
                    this.amountValue.length === 0) {
                    valid = false;
                    this.amountValidate.valid = false;
                    this.amountValidate.message = 'Укажите сумму';
                    this.amountValidate.show = true;
                } else if (!amountRegExp.test(this.amountValue)) {
                    valid = false;
                    this.amountValidate.valid = false;
                    this.amountValidate.message = 'Сумма должна быть числом или числом с плавающей точкой.';
                    this.amountValidate.show = true;
                }

                return valid;
            },

            validateFile() {
                let valid = true;
                this.setDefaultValidateFile();

                if (!this.fileValue ||
                    this.fileValue.length === 0) {
                    valid = false;
                    this.fileValidate.valid = false;
                    this.fileValidate.message = 'Выберите документ';
                    this.fileValidate.show = true;
                }

                return valid;
            },
            setUserRole () {
                this.currentUser.role_names.forEach(role => {
                    if (role === 'admin') {
                        this.is_admin = true;
                    }
                    if (role === 'manager') {
                        this.is_manager = true;
                    }
                    if (role === 'client') {
                        this.is_client = true;
                    }
                    if (role === 'user') {
                        this.is_user = true;
                    }
                });
            },
        },
        created(){
            this.setUserRole();
            this.setManagerOptions();
        }
    };
</script>
