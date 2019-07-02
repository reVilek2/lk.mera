<template>
    <div :class="{'has-success': validate.show && validate.valid, 'has-error': validate.show && !validate.valid}">
        <div class="input-group">
            <input :type="show_pass ? 'text': 'password'"
                   class="form-control"
                   :id="id"
                   :name="name"
                   :disabled="disabled"
                   :required="required"
                   :placeholder="placeholder"
                   v-model="inputValue"
                   @focus="focusEvent($event)"
                   @keydown="keydownEvent($event)">
            <div class="input-group-btn">
                <button type="button" class="btn btn-default" @click="toggleShowPass"><i class="fa" :class="show_pass ? 'fa-eye': 'fa-eye-slash'"></i></button>
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
            }
        },
        data() {
            return {
                inputValue: this.value,
                show_pass: false,
            }
        },
        methods: {
            focusEvent(event){
                this.$emit('focus', event);
            },
            keydownEvent(event){
                this.$emit('keydown', event);
            },
            toggleShowPass() {
                this.show_pass = !this.show_pass
            }
        }
    };
</script>