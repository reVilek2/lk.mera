<template>
    <div :id="container" class="document_status">
        <div v-if="!currUser.is_admin">
            <span class="label" :class="label">{{text}}</span>
        </div>
        <div v-else class="btn-group" :id="box">
            <span class="label dropdown-toggle" data-toggle="dropdown" :class="label">
                {{text}}
            </span>
            <div class="dropdown-staus dropdown-menu" @click="dropdownMenuEvent($event)">
                <div class="form-group">
                    <div class="radio">
                        <label>
                            <input type="radio" :name="'status'+item.id" :value="'0:0'" v-model="selected">
                            Не подписан и не оплачен
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" :name="'status'+item.id" :value="'1:0'" v-model="selected">
                            Подписан и не оплачен
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" :name="'status'+item.id" :value="'0:1'" v-model="selected">
                            Не подписан и оплачен
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" :name="'status'+item.id" :value="'1:1'" v-model="selected">
                            Подписан и оплачен
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-default pull-right" @click="resetChanges">Отмена</button>
                    <button type="button" class="btn btn-success" style="margin-right: 10px" @click="beforeOnSubmit">Сохранить</button>
                </div>
            </div>
        </div>
        <modal :name="modalConfirm"
               classes="v-modal"
               :min-width="200"
               :min-height="200"
               :width="'90%'"
               :height="'auto'"
               :max-width="500"
               :adaptive="true"
               :scrollable="true">
            <div class="v-modal-header">
                <button type="button" class="close" @click="hideModalConfirmAndReset">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="v-modal-title">Предупреждение</h4>
            </div>
            <div class="v-modal-body">
                Вы уверены, что хотите изменить статус документа?
            </div>
            <div class="v-modal-footer">
                <button type="button" class="btn btn-default pull-right" @click="hideModalConfirmAndReset">Нет</button>
                <button type="button" class="btn btn-success" @click="onSubmit()" style="margin-right: 10px">Да</button>
            </div>
        </modal>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    export default {
        props: {
            item: {
                type: Object,
                default: () => {}
            },
            signed: {
                type: Number,
                default: () => 0
            },
            paid: {
                type: Number,
                default: () => 0
            },
        },
        data() {
            return {
                container: 'document-status'+this.item.id,
                box: 'document-status-box'+this.item.id,
                modalConfirm: 'document-status-confirm'+this.item.id,
                statusSigned: this.signed,
                statusPaid: this.paid,
                selected: this.signed+':'+this.paid,
                default: {
                    statusSigned: this.signed,
                    statusPaid: this.paid,
                    selected: this.signed+':'+this.paid,
                },
                isUploadingForm:false,
            }
        },
        watch: {
            selected(val) {
                this.setSignedAndPaid(val);
            },
            signed(val) {
                this.statusSigned = val;
                this.selected = this.signed+':'+this.paid;
                this.default.statusSigned = this.signed;
                this.default.statusPaid = this.paid;
                this.default.selected = this.signed+':'+this.paid;
            },
            paid(val) {
                this.statusPaid = val;
                this.selected = this.signed+':'+this.paid;
                this.default.statusSigned = this.signed;
                this.default.statusPaid = this.paid;
                this.default.selected = this.signed+':'+this.paid;
            }
        },
        computed: {
            text: function () {
                let text = 'Не подписан и не оплачен';
                if (this.statusSigned && !this.statusPaid) {
                    text = 'Подписан и не оплачен';
                }
                if (!this.statusSigned && this.statusPaid) {
                    text = 'Не подписан и оплачен';
                }
                if (this.statusSigned && this.statusPaid) {
                    text = 'Подписан и оплачен';
                }
                return text;
            },
            label: function () {
                let label = 'label-danger';
                if (this.statusSigned && !this.statusPaid) {
                    label = 'label-primary';
                }
                if (!this.statusSigned && this.statusPaid) {
                    label = 'label-warning';
                }
                if (this.statusSigned && this.statusPaid) {
                    label = 'label-success';
                }
                return label;
            },
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
        methods: {
            dropdownMenuEvent(e){
                console.log(e);
                e.stopPropagation();
            },
            setSignedAndPaid(val) {
                let arrStatus = val.split(':');
                this.statusSigned = parseInt(arrStatus[0]);
                this.statusPaid = parseInt(arrStatus[1]);
            },
            resetChanges() {
                this.statusSigned= this.default.statusSigned;
                this.statusPaid= this.default.statusPaid;
                this.selected= this.default.selected;
                this.hideDropdownMenu();
            },
            hideDropdownMenu () {
                let box = $('#'+this.box, '#'+this.container);
                box.removeClass('open');
            },
            showModalConfirm () {
                this.$modal.show(this.modalConfirm);
            },
            hideModalConfirm () {
                this.$modal.hide(this.modalConfirm);
            },
            hideModalConfirmAndReset () {
                this.hideModalConfirm();
                this.resetChanges();
            },
            beforeOnSubmit() {
                this.showModalConfirm();
            },
            setNewChange (signed, paid) {
                let newItem = this.item;
                newItem.signed = signed;
                newItem.paid = paid;
                this.$emit('updateDocument', newItem);
            },
            onSubmit() {
                if (!this.isUploadingForm) {
                    this.isUploadingForm = true;
                    this.hideModalConfirm();
                    this.hideDropdownMenu();
                    axios.post('/documents/'+this.item.id+'/change-status',{signed:this.statusSigned, paid:this.statusPaid}).then(response => {
                        this.isUploadingForm = false;
                        if (response.data.status === 'success') {
                            this.setNewChange(response.data.document.signed, response.data.document.paid);
                        }
                    }).catch(errors => {
                        console.log(errors);
                        this.isUploadingForm = false;
                    });

                }
            },
        },
    }
</script>