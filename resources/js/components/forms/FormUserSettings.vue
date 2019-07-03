<template>
    <div>
        <div v-if="email_changed && !(!!user.email_verified_at)" class="row">
            <div class="col-sm-offset-2 col-sm-10">
                <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-info"></i> Внимание вы сменили email!</h4>
                    Требуется подтверждение что вы являетесь владельцем почтового ящика.
                </div>
            </div>
        </div>
        <div v-if="phone_changed && !(!!user.phone_verified_at)" class="row">
            <div class="col-sm-offset-2 col-sm-10">
                <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-info"></i> Внимание вы сменили телефон!</h4>
                    Требуется подтверждение что вы являетесь владельцем телефонного номера.
                </div>
            </div>
        </div>
        <div class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-2 control-label">Имя</label>

                <div class="col-sm-10">
                    <vue-input-text :name="'first_name'"
                                    :placeholder="'Введите ваше имя'"
                                    v-model="firstNameValue"
                                    :validate="firstNameValidate"
                                    @focus="setDefaultValidateFirstName($event)"></vue-input-text>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Отчество</label>

                <div class="col-sm-10">
                    <vue-input-text :name="'second_name'"
                                    :placeholder="'Введите ваше отчество'"
                                    v-model="secondNameValue"
                                    :validate="secondNameValidate"
                                    @focus="setDefaultValidateSecondName($event)"></vue-input-text>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Фамилия</label>

                <div class="col-sm-10">
                    <vue-input-text :name="'last_name'"
                                    :placeholder="'Введите вашу фамилию'"
                                    v-model="lastNameValue"
                                    :validate="lastNameValidate"
                                    @focus="setDefaultValidateLastName($event)"></vue-input-text>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Телефон</label>

                <div class="col-sm-10">
                    <vue-input-user-phone :curr_phone="user.phone"
                                          :is_phone="!!user.phone"
                                          :is_phone_verified="!!user.phone_verified_at"
                                          :is_allowed_fast_confirm="isAllowedFastConfirmPhone"
                                          :name="'phone'"
                                          :placeholder="'+7 (___) ___ __ __'"
                                          v-model="phoneValue"
                                          :validate="phoneValidate"
                                          @focus="setDefaultValidatePhone($event)"
                                          @fastConfirmPhone="fastConfirmPhone"
                                          @confirmedPhone="confirmedPhone"></vue-input-user-phone>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Email</label>

                <div class="col-sm-10">
                    <vue-input-user-email :curr_email="user.email"
                                          :is_email="!!user.email"
                                          :is_email_verified="!!user.email_verified_at"
                                          :is_allowed_fast_confirm="isAllowedFastConfirmEmail"
                                          :name="'email'"
                                          :placeholder="'Введите ваш email'"
                                          v-model="emailValue"
                                          :validate="emailValidate"
                                          @focus="setDefaultValidateEmail($event)"
                                          @fastConfirmEmail="fastConfirmEmail"></vue-input-user-email>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-primary" @click="beforeOnSubmit">Сохранить</button>
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
    import { mapGetters } from 'vuex';
    import InputText from '../forms/InputText';
    import InputUserPhone from '../forms/InputUserPhone';
    import InputUserEmail from '../forms/InputUserEmail';
    import {isEmptyObject, emailTest} from "../../libs/utils";

    export default {
        components: {
            vueInputText: InputText,
            vueInputUserPhone: InputUserPhone,
            vueInputUserEmail: InputUserEmail,
        },
        props: {
            user: {
                type: Object,
                default: () => {}
            },
        },
        data() {
            return {
                formFastConfirmPhoneUrl: '/profile/fast-confirm-phone/'+this.user.id,
                formFastConfirmEmailUrl: '/profile/fast-confirm-email/'+this.user.id,
                formUrl: '/profile/'+this.user.id,
                firstNameValue: this.user.first_name,
                firstNameValidate: {
                    valid: true,
                    message: '',
                    show: false
                },
                secondNameValue: this.user.second_name,
                secondNameValidate: {
                    valid: true,
                    message: '',
                    show: false
                },
                lastNameValue: this.user.last_name,
                lastNameValidate: {
                    valid: true,
                    message: '',
                    show: false
                },
                phoneValue: this.user.phone,
                phoneValidate: {
                    valid: true,
                    message: '',
                    show: false
                },
                emailValue: this.user.email,
                emailValidate: {
                    valid: true,
                    message: '',
                    show: false
                },
                formValid: false,
                isUploadingForm: false,

                isAllowedFastConfirmPhone: this.checkAllowedFastConfirmPhone(),
                isAllowedFastConfirmEmail: this.checkAllowedFastConfirmEmail(),
                email_changed: false,
                phone_changed: false,

                modalAlert: 'modal-alert-user-setting'+this.user.id,
                modalAlertText: 'Технические неполадки.',
                modalAlertClass: 'alert-danger',
            }
        },
        watch: {
            currUser(user) {
                this.isAllowedFastConfirmPhone = this.checkAllowedFastConfirmPhone();
                this.isAllowedFastConfirmEmail = this.checkAllowedFastConfirmEmail();
            },
            user(user) {
                this.isAllowedFastConfirmPhone = this.checkAllowedFastConfirmPhone();
                this.isAllowedFastConfirmEmail = this.checkAllowedFastConfirmEmail();
            },
        },
        computed: {
            removed_all_credentials: function () {
                return !this.emailValue && !this.phoneValue
            },
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
        methods: {
            showModalAlertError (message) {
                if (message) {
                    this.modalAlertText = message;
                }
                this.modalAlertClass = 'alert-danger';
                this.showModalAlert();
            },
            showModalAlert () {
                this.$modal.show(this.modalAlert);
            },
            hideModalAlert () {
                this.$modal.hide(this.modalAlert);
            },

            beforeOnSubmit() {
                if (this.removed_all_credentials) {
                    this.showModalAlertError('В системе обязательно должен быть email или телефон, иначе аккаунтом нельзя будет пользоваться.');
                } else {
                    this.formValid = this.validate();
                    if (this.formValid) {
                        this.onSubmit();
                    }
                }
            },

            onSubmit() {
                if (this.formValid && !this.isUploadingForm && !this.removed_all_credentials) {

                    this.isUploadingForm = true;
                    const formData = new FormData();
                    if (this.firstNameValue) {
                        formData.append('first_name', this.firstNameValue);
                    }
                    if (this.secondNameValue) {
                        formData.append('second_name', this.secondNameValue);
                    }
                    if (this.lastNameValue) {
                        formData.append('last_name', this.lastNameValue);
                    }
                    if (this.phoneValue) {
                        formData.append('phone', this.phoneValue);
                    }
                    if (this.emailValue) {
                        formData.append('email', this.emailValue);
                    }

                    axios.post(this.formUrl, formData).then(response => {
                        if (response.data.status === 'success') {
                            if (response.data.hasOwnProperty('user')) {
                                this.$emit('updatedUserSettings', response.data.user);
                                if (parseInt(response.data.user.id) === parseInt(this.currUser.id)) {
                                    this.$store.dispatch('setCurrentUser', response.data.user);
                                }
                            }
                            if (response.data.hasOwnProperty('phone_changed')) {
                               this.phone_changed = response.data.phone_changed;
                            }
                            if (response.data.hasOwnProperty('email_changed')) {
                                this.email_changed = response.data.email_changed;
                            }
                            new Noty({
                                type: 'success',
                                text: 'Пользователь успешно изменен.',
                                layout: 'topRight',
                                timeout: 5000,
                                progressBar: true,
                                theme: 'metroui',
                            }).show();
                        }
                        if (response.data.status === 'error') {
                            if (response.data.errors.hasOwnProperty('first_name')) {
                                this.firstNameValidate.valid = false;
                                this.firstNameValidate.message = response.data.errors.first_name[0];
                                this.firstNameValidate.show = true;
                            }
                            if (response.data.errors.hasOwnProperty('second_name')) {
                                this.secondNameValidate.valid = false;
                                this.secondNameValidate.message = response.data.errors.second_name[0];
                                this.secondNameValidate.show = true;
                            }
                            if (response.data.errors.hasOwnProperty('last_name')) {
                                this.lastNameValidate.valid = false;
                                this.lastNameValidate.message = response.data.errors.last_name[0];
                                this.lastNameValidate.show = true;
                            }
                            if (response.data.errors.hasOwnProperty('phone')) {
                                this.phoneValidate.valid = false;
                                this.phoneValidate.message = response.data.errors.phone[0];
                                this.phoneValidate.show = true;
                            }
                            if (response.data.errors.hasOwnProperty('email')) {
                                this.emailValidate.valid = false;
                                this.emailValidate.message = response.data.errors.email[0];
                                this.emailValidate.show = true;
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
                        this.resetForm();
                        this.isUploadingForm = false;
                    }).catch(errors => {
                        console.log(errors);
                        this.isUploadingForm = false;
                        new Noty({
                            type: 'error',
                            text: 'Произошла ошибка.',
                            layout: 'topRight',
                            timeout: 5000,
                            progressBar: true,
                            theme: 'metroui',
                        }).show();
                        this.resetForm();
                    });
                }
            },

            resetForm() {
                this.formValid = false;
            },

            setDefaultValidateFirstName() {
                this.firstNameValidate.valid = true;
                this.firstNameValidate.message = '';
                this.firstNameValidate.show = false;
            },
            setDefaultValidateSecondName() {
                this.secondNameValidate.valid = true;
                this.secondNameValidate.message = '';
                this.secondNameValidate.show = false;
            },
            setDefaultValidateLastName() {
                this.lastNameValidate.valid = true;
                this.lastNameValidate.message = '';
                this.lastNameValidate.show = false;
            },
            setDefaultValidatePhone() {
                this.phoneValidate.valid = true;
                this.phoneValidate.message = '';
                this.phoneValidate.show = false;
            },
            setDefaultValidateEmail() {
                this.emailValidate.valid = true;
                this.emailValidate.message = '';
                this.emailValidate.show = false;
            },

            validate () {
                let valid = true;
                if (!this.validateFirstName()) {
                    valid = false;
                }
                if (!this.validateSecondName()) {
                    valid = false;
                }
                if (!this.validateLastName()) {
                    valid = false;
                }
                if (!this.validatePhone()) {
                    valid = false;
                }
                if (!this.validateEmail()) {
                    valid = false;
                }

                return valid;
            },

            validateFirstName() {
                let valid = true;
                this.setDefaultValidateFirstName();
                return valid;
            },

            validateSecondName() {
                let valid = true;
                this.setDefaultValidateSecondName();
                return valid;
            },

            validateLastName() {
                let valid = true;
                this.setDefaultValidateLastName();
                return valid;
            },

            validatePhone() {
                let valid = true;
                this.setDefaultValidatePhone();

                if (this.phoneValue &&
                    this.phoneValue.length !== 12) {
                    valid = false;
                    this.phoneValidate.valid = false;
                    this.phoneValidate.message = 'Неправильный формат телефона';
                    this.phoneValidate.show = true;
                }

                return valid;
            },
            validateEmail() {
                let valid = true;
                this.setDefaultValidateEmail();
                if (this.emailValue &&
                    !emailTest(this.emailValue)) {
                    valid = false;
                    this.emailValidate.valid = false;
                    this.emailValidate.message = 'Неправильный формат email';
                    this.emailValidate.show = true;
                }

                return valid;
            },
            checkAllowedFastConfirmPhone() {
                if (!isEmptyObject(this.currUser) && !isEmptyObject(this.user)) {
                      return this.currUser.is_admin && !(!!this.user.phone_verified_at) && !!this.user.phone && (parseInt(this.currUser.id) !== parseInt(this.user.id));
                }
                return false;
            },
            checkAllowedFastConfirmEmail() {
                if (!isEmptyObject(this.currUser) && !isEmptyObject(this.user)) {
                      return this.currUser.is_admin && !(!!this.user.email_verified_at) && !!this.user.email && (parseInt(this.currUser.id) !== parseInt(this.user.id));
                }
                return false;
            },
            fastConfirmPhone (user) {
                axios.post(this.formFastConfirmPhoneUrl).then(response => {
                    if (response.data.status === 'success') {
                        if (response.data.hasOwnProperty('user')) {
                            this.$emit('updatedUserSettings', response.data.user);
                            if (parseInt(response.data.user.id) === parseInt(this.currUser.id)) {
                                this.$store.dispatch('setCurrentUser', response.data.user);
                            }
                        }
                        new Noty({
                            type: 'success',
                            text: 'Телефон успешно подтвержден.',
                            layout: 'topRight',
                            timeout: 5000,
                            progressBar: true,
                            theme: 'metroui',
                        }).show();
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
                });
            },
            confirmedPhone(user) {
                new Noty({
                    type: 'success',
                    text: 'Телефон успешно подтвержден.',
                    layout: 'topRight',
                    timeout: 5000,
                    progressBar: true,
                    theme: 'metroui',
                }).show();
                this.$emit('updatedUserSettings', user);
                if (parseInt(user.id) === parseInt(this.currUser.id)) {
                    this.$store.dispatch('setCurrentUser', response.data.user);
                }
            },
            fastConfirmEmail (user) {
                axios.post(this.formFastConfirmEmailUrl).then(response => {
                    if (response.data.status === 'success') {
                        if (response.data.hasOwnProperty('user')) {
                            this.$emit('updatedUserSettings', response.data.user);
                            if (parseInt(response.data.user.id) === parseInt(this.currUser.id)) {
                                this.$store.dispatch('setCurrentUser', response.data.user);
                            }
                        }
                        new Noty({
                            type: 'success',
                            text: 'Email успешно подтвержден.',
                            layout: 'topRight',
                            timeout: 5000,
                            progressBar: true,
                            theme: 'metroui',
                        }).show();
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
                });
            },
        },
    };
</script>
