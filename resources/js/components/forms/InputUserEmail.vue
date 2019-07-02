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
    </div>
</template>
<script>
    export default {
        model: {
            event: 'newInputValue',
            prop: 'value'
        },
        props: {
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
        },
        data() {
            return {
                inputValue: this.value,
                inputBtnTitle: this.is_email_verified ? 'Email подтвержден':'Email не подтвержден',
                inputBtnClass: this.is_email_verified ? 'btn-success':'btn-danger',
                showConfirmForm: false,
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
                this.showConfirmForm = true;
            }
        }
    };
</script>