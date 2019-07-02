<template>
    <div :class="{'has-success': validate.show && validate.valid, 'has-error': validate.show && !validate.valid}">
        <div :class="{'input-group':is_email}">
            <input type="text"
                   class="form-control"
                   :name="name"
                   :disabled="disabled"
                   :required="required"
                   :placeholder="placeholder"
                   v-model="inputValue"
                   @focus="focusEvent($event)"
                   @keydown="keydownEvent($event)">
            <div v-if="is_email" class="input-group-btn" :title="inputBtnTitle">
                <div v-if="is_allowed_fast_confirm" class="btn-group">
                    <button type="button" class="btn dropdown-toggle" :class="inputBtnClass" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-check text-white"></i></button>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                        <li><a href="#" @click="fastConfirmEmail($event)">Подтвердить</a></li>
                    </ul>
                </div>
                <button v-else type="button" class="btn" :class="inputBtnClass" @click="confirmEmail($event)"><i class="fa fa-check text-white"></i></button>
            </div>
        </div>
        <div class="help-block" :class="{'with-success': validate.valid, 'with-errors': !validate.valid}">
            <span v-if="validate.show">{{validate.message}}</span>
        </div>
        <div v-if="!is_allowed_fast_confirm && !is_email_verified" class="input-confirm-form-email" :ref="inputConfirmForm">
            <div class="input-confirm-form-email__item">
                <p class="text-green">На указанный вами email отправлено письмо с кодом авктивации. Перейдите по ссылке из email для активации.<br> Если этого письма нет во «Входящих», пожалуйста, проверьте «Спам».</p>
                <span v-if="preloader" class="preloader preloader-sm"></span>
                <span class="form-resend-link" :class="{'active': resend_code}" style="cursor: pointer" @click="resendCode">Отправить код повторно.</span>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        model: {
            event: 'newInputValue',
            prop: 'value'
        },
        props: {
            curr_email: {
                type: String,
                default: ''
            },
            is_email: {
                type: Boolean,
                default: false
            },
            is_email_verified: {
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
                        show: false
                    };
                }
            },
            value: null
        },
        watch: {
            inputValue(val) {
                this.$emit('newInputValue', val);
            },
            value(val) {
                this.inputValue = val;
            },
            is_email_verified(val) {
                this.inputBtnTitle = val ? 'Email подтвержден':'Email не подтвержден';
                this.inputBtnClass = val ? 'btn-success':'btn-danger';
            },
            curr_email(val) {
                this.emailCodeSending = false;
                this.closeFormConfirm();
                this.resend_code_url = '/email/code-resend/'+val;
            },
        },
        data() {
            return {
                inputValue: this.value,
                inputBtnTitle: this.is_email_verified ? 'Email подтвержден':'Email не подтвержден',
                inputBtnClass: this.is_email_verified ? 'btn-success':'btn-danger',
                inputConfirmForm: 'input_confirm_form_email',
                resend_code_url: '/email/code-resend/'+this.curr_email,
                showConfirmForm: false,
                emailCodeSending: false,
                resend_code: true,
                preloader: false,
            }
        },
        methods: {
            focusEvent(event){
                this.$emit('focus', event);
            },
            keydownEvent(event){
                this.$emit('keydown', event);
            },
            fastConfirmEmail(event) {
                event.preventDefault();
                this.$emit('fastConfirmEmail', event);
            },
            confirmEmail(event) {
                event.preventDefault();
                if (!this.is_allowed_fast_confirm && !this.is_email_verified) {
                    this.showConfirmForm = !this.showConfirmForm;
                    if (this.showConfirmForm) {
                        this.openFormConfirm(); // показываем форму
                        if (!this.emailCodeSending) { // если код не отсылали
                            this.emailCodeSending = true;
                            this.resendCode(); // посылаем новый код
                        }
                    } else { // если свернули форму
                        this.closeFormConfirm();
                    }
                }
            },
            openFormConfirm(){
                $(this.$refs[this.inputConfirmForm]).slideDown(300);
            },
            closeFormConfirm(){
                let _this = this;
                $(this.$refs[this.inputConfirmForm]).slideUp(300, function () {
                    _this.showConfirmForm = false;
                });
            },
            resendCode(){
                if (this.curr_email.length === 0) {
                    new Noty({
                        type: 'error',
                        text: 'Email не определен.',
                        layout: 'topRight',
                        timeout: 5000,
                        progressBar: true,
                        theme: 'metroui',
                    }).show();
                } else {
                    this.resend_code = false;
                    this.preloader = true;
                    axios.post(this.resend_code_url).then(response => {
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
                        this.resend_code = true;
                        this.preloader = false;
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
                        this.resend_code = true;
                        this.preloader = false;
                    });
                }
            },
        }
    };
</script>