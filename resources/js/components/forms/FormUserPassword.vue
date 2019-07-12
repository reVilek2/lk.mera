<template>
    <div class="form-horizontal">
        <div v-if="is_need_old_password" class="form-group">
            <label class="col-sm-2 control-label">Текуший пароль</label>

            <div class="col-sm-10">
                <vue-input-password :name="'old_password'"
                                    :placeholder="'Введите текуший пароль'"
                                    v-model="oldPasswordValue"
                                    :validate="oldPasswordValidate"
                                    @focus="setDefaultValidateOldPassword($event)"></vue-input-password>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Новый пароль</label>

            <div class="col-sm-10">
                <vue-input-password :name="'password'"
                                    :placeholder="'Введите новый пароль'"
                                    v-model="passwordValue"
                                    :validate="passwordValidate"
                                    @focus="setDefaultValidatePassword($event)"></vue-input-password>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Подтвердите пароль</label>

            <div class="col-sm-10">
                <vue-input-password :name="'password_confirmation'"
                                    :placeholder="'Введите подтверждение пароля'"
                                    v-model="passwordConfirmationValue"
                                    :validate="passwordConfirmationValidate"
                                    @focus="setDefaultValidatePasswordConfirmation($event)"></vue-input-password>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-primary" @click="beforeOnSubmit">Сменить пароль</button>
            </div>
        </div>
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    import InputPassword from '../forms/InputPassword';

    export default {
        components: {
            vueInputPassword: InputPassword,
        },
        props: {
            user: {
                type: Object,
                default: () => {}
            },
        },
        data() {
            return {
                formUrl: '/profile/password/'+this.user.id,
                is_need_old_password: true,
                oldPasswordValue: null,
                oldPasswordValidate: {
                    valid: true,
                    message: '',
                    show: false
                },
                passwordValue: null,
                passwordValidate: {
                    valid: true,
                    message: '',
                    show: false
                },
                passwordConfirmationValue: null,
                passwordConfirmationValidate: {
                    valid: true,
                    message: '',
                    show: false
                },
                formValid: false,
                isUploadingForm: false,
            }
        },
        watch: {
            currUser(user) {
                this.is_need_old_password = !user.is_admin || (parseInt(user.id) === parseInt(this.user.id));
            },
        },
        computed: {
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
        methods: {
            beforeOnSubmit() {
                this.formValid = this.validate();
                if (this.formValid) {
                    this.onSubmit();
                }
            },

            onSubmit() {
                if (this.formValid && !this.isUploadingForm) {

                    this.isUploadingForm = true;
                    const formData = new FormData();
                    if (this.oldPasswordValue) {
                        formData.append('old_password', this.oldPasswordValue);
                    }
                    if (this.passwordValue) {
                        formData.append('password', this.passwordValue);
                    }
                    if (this.passwordConfirmationValue) {
                        formData.append('password_confirmation', this.passwordConfirmationValue);
                    }
                    axios.post(this.formUrl, formData).then(response => {
                        if (response.data.status === 'success') {
                            if (response.data.hasOwnProperty('user')) {
                                this.$emit('updatedUserPassword', response.data.user);
                                if (parseInt(response.data.user.id) === parseInt(this.currUser.id)) {
                                    this.$store.dispatch('setCurrentUser', response.data.user);
                                }
                            }
                            new Noty({
                                type: 'success',
                                text: 'Пароль успешно изменен.',
                                layout: 'topRight',
                                timeout: 5000,
                                progressBar: true,
                                theme: 'metroui',
                            }).show();
                        }
                        if (response.data.status === 'error') {
                            if (response.data.errors.hasOwnProperty('old_password')) {
                                this.oldPasswordValidate.valid = false;
                                this.oldPasswordValidate.message = response.data.errors.old_password[0];
                                this.oldPasswordValidate.show = true;
                            }
                            if (response.data.errors.hasOwnProperty('password')) {
                                this.passwordValidate.valid = false;
                                this.passwordValidate.message = response.data.errors.password[0];
                                this.passwordValidate.show = true;
                            }
                        }
                        this.isUploadingForm = false;
                        this.resetForm();
                    }).catch(errors => {
                        //console.log(errors);
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
                this.oldPasswordValue = null;
                this.passwordValue = null;
                this.passwordConfirmationValue = null;
                this.formValid = false;
            },

            setDefaultValidateOldPassword() {
                this.oldPasswordValidate.valid = true;
                this.oldPasswordValidate.message = '';
                this.oldPasswordValidate.show = false;
            },
            setDefaultValidatePassword() {
                this.passwordValidate.valid = true;
                this.passwordValidate.message = '';
                this.passwordValidate.show = false;
            },
            setDefaultValidatePasswordConfirmation() {
                this.passwordConfirmationValidate.valid = true;
                this.passwordConfirmationValidate.message = '';
                this.passwordConfirmationValidate.show = false;
            },

            validate () {
                let valid = true;
                if (!this.validateOldPassword() && this.is_need_old_password) {
                    valid = false;
                }
                if (!this.validatePassword()) {
                    valid = false;
                }
                if (!this.validatePasswordConfirmation()) {
                    valid = false;
                }

                return valid;
            },

            validateOldPassword() {
                let valid = true;
                this.setDefaultValidateOldPassword();
                if (!this.oldPasswordValue ||
                    this.oldPasswordValue.length === 0) {
                    valid = false;
                    this.oldPasswordValidate.valid = false;
                    this.oldPasswordValidate.message = 'Введите текуший пароль';
                    this.oldPasswordValidate.show = true;
                }
                return valid;
            },

            validatePassword() {
                let valid = true;
                this.setDefaultValidatePassword();
                if (!this.passwordValue ||
                    this.passwordValue.length === 0) {
                    valid = false;
                    this.passwordValidate.valid = false;
                    this.passwordValidate.message = 'Введите новый пароль';
                    this.passwordValidate.show = true;
                }
                return valid;
            },

            validatePasswordConfirmation() {
                let valid = true;
                this.setDefaultValidatePasswordConfirmation();
                if (!this.passwordConfirmationValue ||
                    this.passwordConfirmationValue.length === 0) {
                    valid = false;
                    this.passwordConfirmationValidate.valid = false;
                    this.passwordConfirmationValidate.message = 'Введите подтверждение пароля';
                    this.passwordConfirmationValidate.show = true;
                }
                return valid;
            },
        },
        mounted(){

        }
    };
</script>
