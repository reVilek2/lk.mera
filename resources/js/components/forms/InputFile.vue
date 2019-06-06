<template>
    <div :class="{'has-success': validate.show && validate.valid, 'has-error': validate.show && !validate.valid}">
        <input type="file"
               class="form-control"
               :id="id"
               :name="name"
               :disabled="disabled"
               :required="required"
               :placeholder="placeholder"
               v-on:change="changeEvent($event)">
        <div class="help-block" :class="{'with-success': validate.valid, 'with-errors': !validate.valid}">
            <span v-if="validate.show">{{validate.message}}</span>
        </div>
    </div>
</template>
<script>
    export default {
        model: {
            event: 'newSelectedFile',
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
            selectedFile(val) {
                this.$emit('newSelectedFile', val);
            },
            value(val) {
                // ?
            }
        },
        data() {
            return {
                selectedFile: this.value
            }
        },
        methods: {
            focusEvent(event){
                this.$emit('focus', event);
            },
            changeEvent(event){
                this.selectedFile = event.target.files[0];
                this.$emit('change', event);
            },
        }
    };
</script>