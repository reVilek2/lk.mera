<template>
    <div :class="{'has-success': validate.show && validate.valid, 'has-error': validate.show && !validate.valid}">
        <v-select2 :id="id"
                   :name="name"
                   :disabled="disabled"
                   :required="required"
                   :settings="settings"
                   :options="options"
                   v-model="selected"
                   @click="clickEvent($event)"
                   @change="changeEvent($event)"
                   @select="selectEvent($event)"></v-select2>
        <div class="help-block" :class="{'with-success': validate.valid, 'with-errors': !validate.valid}">
            <span v-if="validate.show">{{validate.message}}</span>
        </div>
    </div>
</template>
<script>
    import VSelect2 from '../VSelect2';

    export default {
        model: {
            event: 'changeEvent',
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
            search: {
                type: Boolean,
                default: false
            },
            allowClear: {
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
            selected(val) {
                this.$emit('changeEvent', val);
            }
        },
        data() {
            let customSetting = {allowClear: this.allowClear};
            if (!this.search) {
                customSetting = {...customSetting, minimumResultsForSearch: 'Infinity'};
            }
            if (this.placeholder.length > 0) {
                customSetting = {...customSetting, placeholder: this.placeholder};
            }
            return {
                settings: customSetting,
                selected: this.value
            }
        },
        components: {
            VSelect2: VSelect2,
        },
        methods: {
            clickEvent(val){
                this.$emit('click', val);
            },
            changeEvent(val){
                //this.$emit('changeEvent', val);
            },
            selectEvent({id, text}){
                // console.log({id, text})
            }
        }
    };
</script>