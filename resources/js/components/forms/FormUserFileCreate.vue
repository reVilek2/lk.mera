<template>
    <div>
        <modal name="file-create"
               classes="v-modal"
               :min-width="200"
               :min-height="200"
               :width="'90%'"
               :height="'auto'"
               :max-width="500"
               :adaptive="true"
               :scrollable="true">
            <div class="v-modal-header">
                <button type="button" class="close" @click="hideModal">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="v-modal-title">Добавление документа</h4>
            </div>
            <div class="v-modal-body">

                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Название файла</label>

                        <div class="col-sm-8">
                            <vue-input-text :name="'name'"
                                          :placeholder="'Введите название файла'"
                                          v-model="nameValue"
                                          :validate="nameValidate"
                                          @focus="setDefaultValidateName($event)"></vue-input-text>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Файл</label>

                        <div class="col-sm-8">
                            <vue-input-file :name="'file'"
                                            :placeholder="'Выберите файл'"
                                            v-model="fileValue"
                                            :validate="fileValidate"
                                            @focus="setDefaultValidateFile($event)"></vue-input-file>
                        </div>
                    </div>
                </div>
            </div>
            <div class="v-modal-footer">
                <button type="button" class="btn btn-default pull-left" @click="hideModal">Отмена</button>
                <button type="button" class="btn btn-primary" @click="beforeOnSubmit">Добавить</button>
            </div>
        </modal>
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    import Select from '../forms/Select';
    import InputText from '../forms/InputText';
    import InputFile from '../forms/InputFile';

    export default {
        components: {
            vueSelect: Select,
            vueInputText: InputText,
            vueInputFile: InputFile,
        },
        props: {
            clearForm: {
                type: Boolean,
                default: () => false
            },
        },
        data() {
            return {
                clearData: this.clearForm,
                nameValue: null,
                nameValidate: {
                    valid: true,
                    message: '',
                    show: false
                },
                fileValue: null,
                fileValidate: {
                    valid: true,
                    message: '',
                    show: false
                },
                formValid: false,
                isUploadingForm: false,
            }
        },
        watch: {
            clearForm(val) {
                this.clearData = val;
                if (this.clearData === true) {
                    this.clearFileForm();
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
            showModal () {
                this.$modal.show('file-create');
            },
            hideModal () {
                this.$modal.hide('file-create');
            },

            setDefaultValidateName() {
                this.nameValidate.valid = true;
                this.nameValidate.message = '';
                this.nameValidate.show = false;
            },

            setDefaultValidateFile() {
                this.fileValidate.valid = true;
                this.fileValidate.message = '';
                this.fileValidate.show = false;
            },
            beforeOnSubmit() {
                this.formValid = this.validate();
                if (this.formValid) {
                    this.onSubmit();
                }
            },
            clearFileForm() {
                this.nameValue = null;
                this.fileValue = null;
                this.formValid = false;

                // отчистка валидации
                this.nameValidate.valid = true;
                this.nameValidate.message = '';
                this.nameValidate.show = false;

                this.fileValidate.valid = true;
                this.fileValidate.message = '';
                this.fileValidate.show = false;

                this.$emit('formCleared', 'cleared success');
            },
            onSubmit() {
                if (this.formValid && !this.isUploadingForm) {

                    this.isUploadingForm = true;
                    const formData = new FormData();

                    formData.append('name', this.nameValue);
                    formData.append('file', this.fileValue, this.fileValue.name);

                    axios.post('/documents', formData).then(response => {
                        if (response.data.status === 'success') {
                            if (response.data.hasOwnProperty('file')) {
                                this.$emit('createdFile', response.data.file);
                            }
                        }
                        if (response.data.status === 'error') {
                            if (response.data.errors.hasOwnProperty('file')) {
                                this.fileValidate.valid = false;
                                this.fileValidate.message = response.data.errors.file[0];
                                this.fileValidate.show = true;
                            }
                            if (response.data.errors.hasOwnProperty('name')) {
                                this.nameValidate.valid = false;
                                this.nameValidate.message = response.data.errors.name[0];
                                this.nameValidate.show = true;
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

                if (!this.validateName()) {
                    valid = false;
                }

                if (!this.validateFile()) {
                    valid = false;
                }

                return valid;
            },

            validateName() {
                let valid = true;
                this.setDefaultValidateName();

                if (!this.nameValue ||
                    this.nameValue.length === 0) {
                    valid = false;
                    this.nameValidate.valid = false;
                    this.nameValidate.message = 'Введите название файла';
                    this.nameValidate.show = true;
                }

                return valid;
            },

            validateFile() {
                let valid = true;
                this.setDefaultValidateFile();

                if (!this.fileValue ||
                    this.fileValue.length === 0) {
                    valid = false;
                    this.fileValidate.valid = false;
                    this.fileValidate.message = 'Выберите файл';
                    this.fileValidate.show = true;
                }

                return valid;
            },
        }
    };
</script>
