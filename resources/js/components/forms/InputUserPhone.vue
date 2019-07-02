<template>
    <div :class="{'has-success': validate.show && validate.valid, 'has-error': validate.show && !validate.valid}">
        <div :class="{'input-group':is_phone}">
            <masked-input
                    type="text"
                    class="form-control"
                    :name="name"
                    :disabled="disabled"
                    :required="required"
                    :placeholder="placeholder"
                    v-model="inputValue"
                    :mask="['+', '7', ' ', '(', /\d/, /\d/, /\d/, ')', ' ', /\d/, /\d/, /\d/, ' ', /\d/, /\d/,' ', /\d/, /\d/]"
                    :guide="true"
                    placeholderChar="_"
                    @focus="focusEvent($event)"
                    @keydown="keydownEvent($event)">
            </masked-input>
            <div v-if="is_phone" class="input-group-btn" :title="inputBtnTitle">
                <div v-if="is_allowed_fast_confirm" class="btn-group">
                    <button type="button" class="btn dropdown-toggle" :class="inputBtnClass" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-check text-white"></i></button>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                        <li><a href="#" @click="fastConfirmPhone($event)">Подтвердить</a></li>
                    </ul>
                </div>
                <button v-else type="button" class="btn" :class="inputBtnClass" @click="confirmForm($event)"><i class="fa fa-check text-white"></i></button>
            </div>
        </div>

        <div class="help-block" :class="{'with-success': validate.valid, 'with-errors': !validate.valid}">
            <span v-if="validate.show">{{validate.message}}</span>
        </div>
        <div v-if="!is_allowed_fast_confirm && !is_phone_verified" class="input-confirm-form" :ref="inputConfirmForm">
            <div class="input-confirm-form__item">
                <div class="form-group">
                    <div class="col-sm-12">
                        <label class="control-label">Код активации</label>
                        <div :class="{'has-success': phoneCodeValidate.show && phoneCodeValidate.valid, 'has-error': phoneCodeValidate.show && !phoneCodeValidate.valid}">
                            <input v-model="phoneCode"
                                   type="text"
                                   name="code"
                                   placeholder="Введите код активации"
                                   class="form-control"
                                   autocomplete="off"
                                   @focus="focusCodeEvent($event)">

                            <div class="help-block" :class="{'with-success': phoneCodeValidate.valid, 'with-errors': !phoneCodeValidate.valid}">
                                <span v-if="phoneCodeValidate.show">{{phoneCodeValidate.message}}</span>
                            </div>

                            <span v-if="preloader" class="preloader preloader-sm"></span>
                            <span class="form-resend-link" :class="{'active': resend_code}" style="cursor: pointer" @click="resendCode">Отправить код повторно.</span>
                            <span :ref="resendTimer" class="form-resend-link" :class="{'active': resend_timer}">Время действия кода:  --- сек.</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-primary" @click="beforeConfirmPhone">Подтвердить</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import MaskedInput from 'vue-text-mask';

    export default {
        model: {
            event: 'newInputValue',
            prop: 'value'
        },
        components: {
            MaskedInput
        },
        props: {
            curr_phone: {
                type: String,
                default: ''
            },
            is_phone: {
                type: Boolean,
                default: false
            },
            is_phone_verified: {
                type: Boolean,
                default: false
            },
            is_allowed_fast_confirm: {
                type: Boolean,
                default: false
            },
            id: {
                type: String,
                default: ''
            },
            name: {
                type: String,
                default: ''
            },
            placeholder: {
                type: String,
                default: ''
            },
            disabled: {
                type: Boolean,
                default: false
            },
            required: {
                type: Boolean,
                default: false
            },
            validate : {
                type: Object,
                default() {
                    return {
                        valid: true,
                        message: '',
                        show: false,
                    };
                }
            },
            value: null,
        },
        watch: {
            inputValue(val) {
                let newVal = val.replace(/\D+/g, '');
                if (newVal) {
                    newVal = '+'+newVal;
                }
                this.$emit('newInputValue', newVal);
            },
            is_phone_verified(val) {
                this.inputBtnTitle = val ? 'Телефон подтвержден':'Телефон не подтвержден';
                this.inputBtnClass = val ? 'btn-success':'btn-danger';
            },
            curr_phone(val) {
                this.phoneCodeSending = false;
                this.closeFormConfirm();
                this.resetTimer();

                this.resend_code_url = '/phone/code-resend/'+val;
                this.confirm_code_url = '/phone/confirm/'+val;
            },
        },
        data() {
            return {
                inputValue: this.value,
                inputBtnTitle: this.is_phone_verified ? 'Телефон подтвержден':'Телефон не подтвержден',
                inputBtnClass: this.is_phone_verified ? 'btn-success':'btn-danger',

                resend_code_url: '/phone/code-resend/'+this.curr_phone,
                confirm_code_url: '/phone/confirm/'+this.curr_phone,
                resendTimer: 'resend_timer',
                inputConfirmForm: 'input_confirm_form',

                preloader: true,
                resend_code: false,
                resend_timer: false,
                resend_phone_code_time: 0,
                phoneCode: null,
                phoneCodeValidate: {
                    valid: true,
                    message: '',
                    show: false
                },
                time: null,
                showConfirmForm: false,
                phoneCodeSendProcess: false,
                phoneCodeSending: false,
            }
        },
        methods: {
            focusEvent(event){
                this.$emit('focus', event);
            },
            keydownEvent(event){
                this.$emit('keydown', event);
                this.closeFormConfirm();
            },
            focusCodeEvent(event){
                this.setDefaultValidatePhoneCode();
            },
            fastConfirmPhone(event) {
                event.preventDefault();
                this.$emit('fastConfirmPhone', event);
            },
            openFormConfirm(){
                this.resetFormConfirm();
                $(this.$refs[this.inputConfirmForm]).slideDown(300);
            },
            closeFormConfirm(){
                let _this = this;
                $(this.$refs[this.inputConfirmForm]).slideUp(300, function () {
                    _this.showConfirmForm = false;
                    _this.resetFormConfirm()
                });
            },
            resetFormConfirm() {
                this.phoneCodeSendProcess = false;
                this.phoneCode = null;
                this.preloader = true;
                this.resend_code = false;
                this.resend_timer = false;

                this.setDefaultValidatePhoneCode();
            },
            confirmForm(event) {
                event.preventDefault();
                if (!this.is_allowed_fast_confirm && !this.is_phone_verified) {
                    this.showConfirmForm = !this.showConfirmForm;
                    if (this.showConfirmForm) {
                        this.openFormConfirm(); // показываем форму
                        if (!this.phoneCodeSending) { // если код не отсылали
                            this.phoneCodeSending = true;
                            this.resendCode(); // посылаем новый код
                        } else {
                            this.timer(); // иначе просто таймер
                        }
                    } else { // если свернули форму
                        this.closeFormConfirm();
                    }
                }
            },
            timer(){
                let _this = this;
                _this.clearTimer();
                if (_this.resend_phone_code_time > 0) {
                    _this.preloader = true;
                    _this.time = setInterval(function () {
                        _this.preloader = false;
                        _this.resend_phone_code_time--;
                        if(_this.$refs[_this.resendTimer] !== undefined) {
                            _this.$refs[_this.resendTimer].innerHTML = 'Время действия кода: ' + _this.resend_phone_code_time + ' сек.';
                        }
                        if (_this.resend_phone_code_time === 0) {
                            _this.resend_code = true;
                            _this.resend_timer = false;
                            _this.clearTimer();
                        } else {
                            _this.resend_timer = true;
                            _this.resend_code = false;
                        }
                    }, 1000);
                } else {
                    _this.preloader = false;
                    _this.resend_code = true;
                    _this.resend_timer = false;
                }
            },
            resetTimer() {
                this.resend_phone_code_time = 0;
                this.clearTimer();
            },
            clearTimer() {
                if (this.time) {
                    clearInterval(this.time);
                }
            },
            resendCode(){
                if (this.curr_phone.length === 0) {
                    new Noty({
                        type: 'error',
                        text: 'Телефон не определен.',
                        layout: 'topRight',
                        timeout: 5000,
                        progressBar: true,
                        theme: 'metroui',
                    }).show();
                } else {
                    this.resetFormConfirm();
                    axios.post(this.resend_code_url).then(response => {
                        if (response.data.status === 'success') {
                            if (response.data.hasOwnProperty('resend_phone_code_time')) {
                                this.resend_phone_code_time = response.data.resend_phone_code_time > 0 ? response.data.resend_phone_code_time : 0;
                                this.timer();
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
                        this.preloader = false;
                    });
                }
            },
            setDefaultValidatePhoneCode() {
                this.phoneCodeValidate.valid = true;
                this.phoneCodeValidate.message = '';
                this.phoneCodeValidate.show = false;
            },
            codeValidate() {
                let valid = true;
                this.setDefaultValidatePhoneCode();

                if (!this.phoneCode ||
                    this.phoneCode.length === 0) {
                    valid = false;
                    this.phoneCodeValidate.valid = false;
                    this.phoneCodeValidate.message = 'Введите код активации';
                    this.phoneCodeValidate.show = true;
                }

                return valid;
            },
            beforeConfirmPhone() {
                if (this.codeValidate()) {
                    this.confirmPhone();
                }
            },
            confirmPhone () {
                if (!this.phoneCodeSendProcess && this.phoneCode) {
                    this.phoneCodeSendProcess = true;
                    const formData = new FormData();
                    formData.append('code', this.phoneCode);
                    axios.post(this.confirm_code_url, formData).then(response => {
                        if (response.data.status === 'success') {
                            if (response.data.hasOwnProperty('user')) {
                                this.$emit('confirmedPhone', response.data.user);
                                this.closeFormConfirm();
                            }
                        }
                        if (response.data.status === 'error') {
                            if (response.data.errors.hasOwnProperty('code')) {
                                this.phoneCodeValidate.valid = false;
                                this.phoneCodeValidate.message = response.data.errors.code[0];
                                this.phoneCodeValidate.show = true;
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
                        this.phoneCodeSendProcess = false; // разрешаем отправлять форму
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
                        this.phoneCodeSendProcess = false; // разрешаем отправлять форму
                    });
                }
            }
        },
    };
</script>