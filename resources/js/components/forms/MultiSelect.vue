<template>
    <div :class="{'has-success': validate.show && validate.valid, 'has-error': validate.show && !validate.valid}">
        <multiselect
            v-model="selected"
            label="name"
            track-by="code"
            :selectLabel="'Выбирете, чтобы добавить'"
            :selectedLabel="'Выбрано'"
            :deselectLabel="'Выбирете, чтобы убрать'"
            :placeholder="placeholder"
            :tagPlaceholder="tagPlaceholder"
            :options="options"
            :required="required"
            :multiple="true"
            :taggable="true"
            @input="input">
        </multiselect>

        <div class="help-block" :class="{'with-success': validate.valid, 'with-errors': !validate.valid}">
            <span v-if="validate.show">{{validate.message}}</span>
        </div>
    </div>
</template>
<script>
    import Multiselect from 'vue-multiselect'

    export default {
        model: {
            event: 'changeEvent',
            prop: 'value'
        },
        props: {
            placeholder: {
                type: String,
                default: ''
            },
            tagPlaceholder:{
                type: String,
                default: ''
            },
            value:{
                type: Array,
                default: () => []
            },
            options: {
                type: Array,
                default: () => []
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
        },
        data() {
            return {
                selected: this.value
            }
        },
        components: {
            multiselect: Multiselect,
        },
        methods: {
            input(options){
                this.$emit('input', this.selected);
            }
        }
    };
</script>
