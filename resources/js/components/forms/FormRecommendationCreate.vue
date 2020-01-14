<template>
    <div>
        <modal name="recommendation-create"
               classes="v-modal"
               :min-width="200"
               :min-height="300"
               :width="'90%'"
               :height="'auto'"
               :max-width="500"
               :adaptive="true"
               :scrollable="true">
            <div class="v-modal-header">
                <button type="button" class="close" @click="hideModal">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="v-modal-title">Добавление рекомендации</h4>
            </div>
            <div class="v-modal-body">

                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">ФИО клиента:</label>

                        <div class="col-sm-8">
                            <vue-multiselect
                                tagPlaceholder="Имя клиента"
                                placeholder="Выберите клиентов"
                                :value="selectedClients"
                                :options="clientOptions"
                                :multiple="true"
                                :taggable="true"
                                :validate="clientValidate"
                                @input="multiSelectHandler">
                            </vue-multiselect>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Заголовок</label>

                        <div class="col-sm-8">
                            <vue-input-text :name="'title'"
                                            :placeholder="'Укажите заголовок'"
                                            v-model="titleValue"
                                            :validate="titleValidate"
                                            @focus="setValidateTitle()"></vue-input-text>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Текст</label>

                        <div class="col-sm-8">
                            <vue-textarea :name="'text'"
                                            :placeholder="'Текст рекомендации'"
                                            v-model="textValue"
                                            :validate="textValidate"
                                            @focus="setValidateText()"></vue-textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Email</label>

                        <div class="col-sm-8">
                            <vue-input-text :name="'text'"
                                            :placeholder="'Адрес электронной почты'"
                                            v-model="emailValue"
                                            :validate="emailValidate"
                                            @focus="setValidateEmail()"></vue-input-text>
                        </div>
                    </div>
                </div>
            </div>
            <div class="v-modal-footer">
                <button type="button" class="btn btn-default pull-left" @click="hideModal">Отмена</button>
                <button type="button" class="btn btn-primary" @click="beforeOnSubmit">Добавить</button>
            </div>
        </modal>
        <modal name="recommendation-create-confirm"
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
                После отправки удалить рекомендацию из личного кабинета клиента будет не возможно. Отправляем?
            </div>
            <div class="v-modal-footer">
                <button type="button" class="btn btn-default pull-right" @click="hideModalConfirm">Нет</button>
                <button type="button" class="btn btn-success" @click="onSubmit()" style="margin-right: 10px">Да</button>
            </div>
        </modal>
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    import InputText from './InputText';
    import Textarea from './Textarea';
    import MultiSelect from './MultiSelect'

    export default {
        components: {
            vueInputText: InputText,
            vueTextarea: Textarea,
            vueMultiselect: MultiSelect
        },
        props: {
            managers: {
                type: Array,
                default: () => []
            },
            clearForm: {
                type: Boolean,
                default: () => false
            },
        },
        data() {
            return {
                clearData: this.clearForm,

                selectedClients: [],
                clientOptions: [],
                clientValidate: {
                    valid: true,
                    message: '',
                    show: false
                },

                titleValue: null,
                titleValidate: {
                    valid: true,
                    message: '',
                    show: false
                },

                textValue: null,
                textValidate: {
                    valid: true,
                    message: '',
                    show: false
                },

                emailValue: null,
                emailValidate: {
                    valid: true,
                    message: '',
                    show: false
                },

                formValid: false,
                isUploadingForm: false,
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
        computed: {
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
        methods: {
            multiSelectHandler (selected) {
                this.selectedClients = selected;
            },
            removeTag (tag) {
                this.selectedClients = this.selectedClients.filter( client => {
                    return client.code != tag.code
                });
            },
            showModalConfirm () {
                this.hideModal();
                this.$modal.show('recommendation-create-confirm');
            },
            hideModalConfirm () {
                this.$modal.hide('recommendation-create-confirm');
                this.showModal();
            },
            showModal () {
                this.$modal.show('recommendation-create');
            },
            hideModal () {
                this.$modal.hide('recommendation-create');
            },

            setClientOptions () {
                if(!this.currUser.is_manager){
                    return;
                }
                let managerOptions = this.managerOptions;
                let currUserId = 0;
                if (this.currUser.hasOwnProperty('id')) {
                    currUserId = this.currUser.id;
                }
                console.log(this.managers)
                this.clientSelected = [];
                this.clientOptions = [];
                this.managers.forEach(el => {
                    if (parseInt(currUserId) === parseInt(el.id)) {
                        el.clients.forEach(client => {
                            this.clientOptions.push({ name:client.name, code:client.id });
                        });
                    }
                });
            },

            setValidateClient(valid = true, message = '', show = false) {
                this.clientValidate.valid = valid;
                this.clientValidate.message = message;
                this.clientValidate.show = show;
            },

            setValidateTitle(valid = true, message = '', show = false) {
                this.titleValidate.valid = valid;
                this.titleValidate.message = message;
                this.titleValidate.show = show;
            },

            setValidateText(valid = true, message = '', show = false) {
                this.textValidate.valid = valid;
                this.textValidate.message = message;
                this.textValidate.show = show;
            },

            setValidateEmail(valid = true, message = '', show = false) {
                this.emailValidate.valid = valid;
                this.emailValidate.message = message;
                this.emailValidate.show = show;
            },

            beforeOnSubmit() {
                this.formValid = this.validate();
                if (this.formValid) {
                    this.showModalConfirm();
                }
            },
            clearDocumentForm() {
                this.selectedClients = [];
                this.titleValue = null;
                this.textValue = null;
                this.emailValue = null;

                // отчистка валидации
                this.setValidateClient();
                this.setValidateTitle();
                this.setValidateText();
                this.setValidateEmail();

                this.setClientOptions();
                this.$emit('formCleared', 'cleared success');
            },
            onSubmit() {
                if (this.formValid && !this.isUploadingForm) {
                    this.isUploadingForm = true;

                    const data = {}
                    data.clients = [];
                    this.selectedClients.forEach((client, index) => {
                        data.clients.push(client.code);
                    });
                    data.title = this.titleValue;
                    data.text = this.textValue;
                    data.email = this.emailValue;

                    axios.post('/recommendations', data).then(response => {
                        if (response.data.status === 'success') {
                            if (response.data.hasOwnProperty('recommendation')) {
                                this.$emit('createdRecommendation', response.data.recommendation);
                            }
                        }
                        if (response.data.status === 'error') {
                            if (response.data.errors.hasOwnProperty('client')) {
                                this.setValidateClient(false, response.data.errors.client[0], true);
                            }
                            if (response.data.errors.hasOwnProperty('title')) {
                                this.setValidateTitle(false, response.data.errors.title[0], true);
                            }
                            if (response.data.errors.hasOwnProperty('text')) {
                                this.setValidateText(false, response.data.errors.text[0], true);
                            }
                            if (response.data.errors.hasOwnProperty('email')) {
                                this.setValidateEmail(false, response.data.errors.email[0], true);
                            }
                        }
                        this.isUploadingForm = false;
                    }).catch(errors => {
                        //console.log(errors);
                        this.isUploadingForm = false;
                    });

                }
            },

            validate () {
                let valid = true;

                if (!this.validateClient()) {
                    valid = false;
                }

                if (!this.validateTitle()) {
                    valid = false;
                }

                if (!this.validateText()) {
                    valid = false;
                }

                if (!this.validateEmail()) {
                    valid = false;
                }

                return valid;
            },

            validateClient() {
                let valid = true;
                this.setValidateClient();
                if (!this.selectedClients ||
                    this.selectedClients.length === 0) {
                    valid = false;
                    this.setValidateClient(false, 'Выберите один из вариантов', true);
                }

                return valid;
            },

            validateTitle() {
                let valid = true;
                this.setValidateTitle();

                if (!this.titleValue ||
                    this.titleValue.length === 0) {
                    valid = false;
                    this.setValidateTitle(false, 'Введите заголовок', true);
                }

                return valid;
            },

            validateText() {
                let valid = true;
                this.setValidateText();

                if (!this.textValue ||
                    this.textValue.length === 0) {
                    valid = false;
                    this.setValidateText(false, 'Введите текст', true);
                }

                return valid;
            },

            validateEmail() {
                let emailRegExp = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                let valid = true;
                this.setValidateEmail();

                if (!this.emailValue ||
                    this.emailValue.length === 0 ||
                    !emailRegExp.test(this.emailValue)) {
                    valid = false;
                    this.setValidateEmail(false, 'Введите адрес электронной почты', true);
                }

                return valid;
            },

        },
        mounted(){
            this.setClientOptions();
        }
    };
</script>
