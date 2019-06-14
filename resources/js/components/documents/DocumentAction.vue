<template>
    <div :id="container" class="document_action" v-show="isNeedShow">
        <div :id="container_item" class="btn-group" v-show="is_active_item">
            <button type="button" class="btn btn-info" @click="beforeOnSubmit(btnText.action, btnText.action_signed)">{{btnText.value}}</button>
            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul v-if="btnList.length > 0" :id="box" class="dropdown-menu" role="menu">
                <li v-for="(list, index) in btnList" :key="index"><a href="#" @click="beforeOnSubmit(list.action, btnText.action_signed)">{{list.value}}</a></li>
            </ul>
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
                Вы уверены, что хотите <span v-if="action_signed">подписать</span><span v-else>оплатить</span> документ? Отменить <span v-if="action_signed">подписание</span><span v-else>оплату</span> будет невозможно.
            </div>
            <div class="v-modal-footer">
                <button type="button" class="btn btn-default pull-right" @click="hideModalConfirmAndReset">Нет</button>
                <button type="button" class="btn btn-success" @click="onSuccessConfirm" style="margin-right: 10px">Да</button>
            </div>
        </modal>
    </div>
</template>

<script>
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
            currentUser: {
                type: Object,
                default: () => {}
            },
        },
        data() {
            return {
                container: 'document-action'+this.item.id,
                container_item: 'document-action-item'+this.item.id,
                box: 'document-action-box'+this.item.id,
                modalConfirm: 'document-action-confirm'+this.item.id,
                paid_url: '/documents/'+this.item.id+'/paid',
                statusSigned: this.signed,
                statusPaid: this.paid,
                isUploadingForm:false,
                is_active_item:true,
                action: ()=> {},
                action_signed: true,
                currUser: this.currentUser,
            }
        },
        watch: {
            signed(val) {
                this.statusSigned = val;
                this.checkActiveItem();
            },
            paid(val) {
                this.statusPaid = val;
                this.checkActiveItem();
            }
        },
        computed: {
            btnText: function () {
                if (this.currUser.is_admin || this.currUser.is_client) {
                    if (!this.statusSigned && !this.statusPaid) {
                        return {action: this.actionSignedAndPaid, action_signed: true ,value: 'Подписать и оплатить'};
                    }
                    if (!this.statusPaid) {
                        return {action: this.actionPaid, action_signed: false ,value: 'Оплатить'};
                    }
                    if (!this.statusSigned) {
                        return {action: this.actionSigned, action_signed: true ,value: 'Подписать'};
                    }
                }

                if (this.currUser.is_manager) {
                    return {action: this.actionPaid, action_signed: false, value: 'Оплатить'};
                }

                return {action: () => {}, action_signed: true, value: ''};
            },
            btnList: function () {
                if (this.currUser.is_admin) {
                    let btnList = [];
                    if (!this.statusSigned && !this.statusPaid) {
                        btnList.push({action: this.actionSignedAndPaid, action_signed: true ,value: 'Подписать и оплатить'});
                    }
                    if (!this.statusPaid) {
                        btnList.push({action: this.actionPaid, action_signed: false ,value: 'Оплатить'});
                    }
                    if (!this.statusSigned) {
                        btnList.push({action: this.actionSigned, action_signed: true ,value: 'Подписать'});
                    }
                    return btnList;
                }
                if (this.currUser.is_client) {
                    let btnList = [];
                    if (!this.statusSigned && !this.statusPaid) {
                        btnList.push({action: this.actionSignedAndPaid, action_signed: true ,value: 'Подписать и оплатить'});
                    }
                    if (this.statusSigned && !this.statusPaid) {
                        btnList.push({action: this.actionPaid, action_signed: false ,value: 'Оплатить'});
                    }
                    if (!this.statusSigned) {
                        btnList.push({action: this.actionSigned, action_signed: true ,value: 'Подписать'});
                    }
                    return btnList;
                }
                if (this.currUser.is_manager) {
                    return [{action: this.actionPaid, action_signed: false ,value: 'Оплатить'}];
                }
                return [];
            },
        },
        methods: {
            actionSignedAndPaid() {
                this.resetChanges();
                if (!this.isUploadingForm) {
                    this.isUploadingForm = true;
                    let data = {signed:1};
                    let url = '/documents/'+this.item.id+'/change-signed';
                    if (this.currUser.is_admin) {
                        data = {signed:1, paid: 1};
                        url = '/documents/'+this.item.id+'/change-status';
                    }
                    axios.post(url,data).then(response => {
                        if (this.currUser.is_client) {
                            window.location = this.paid_url;
                        } else {
                            this.isUploadingForm = false;
                            if (response.data.status === 'success') {
                                this.setNewChange(response.data.document.signed, response.data.document.paid);
                            }
                        }
                    }).catch(errors => {
                        console.log(errors);
                        this.isUploadingForm = false;
                    });
                }
            },
            actionPaid() {
                this.resetChanges();
                if (this.currUser.is_client) {
                    window.location = this.paid_url;
                } else {
                    if (!this.isUploadingForm) {
                        this.isUploadingForm = true;
                        axios.post('/documents/'+this.item.id+'/change-paid',{paid:1}).then(response => {
                            this.isUploadingForm = false;
                            if (response.data.status === 'success') {
                                this.setNewChange(response.data.document.signed, response.data.document.paid);
                            }
                        }).catch(errors => {
                            console.log(errors);
                            this.isUploadingForm = false;
                        });

                    }
                }
            },
            actionSigned() {
                this.resetChanges();
                if (!this.isUploadingForm) {
                    this.isUploadingForm = true;
                    axios.post('/documents/'+this.item.id+'/change-signed',{signed:1}).then(response => {
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
            setNewChange (signed, paid) {
                let newItem = this.item;
                newItem.signed = signed;
                newItem.paid = paid;
                this.$emit('updateDocument', newItem);
            },
            isNeedShow () {
                return this.currUser.is_admin || this.currUser.is_manager || this.currUser.is_client
            },
            checkActiveItem () {
                if (this.currUser.is_manager && !this.currUser.is_admin && !this.currUser.is_client) {
                    this.is_active_item = !this.statusPaid;
                } else {
                    this.is_active_item = !this.statusSigned || !this.statusPaid;
                }
            },
            setSignedAndPaid(val) {
                let arrStatus = val.split(':');
                this.statusSigned = parseInt(arrStatus[0]);
                this.statusPaid = parseInt(arrStatus[1]);
            },
            resetChanges() {
                this.action = () => {};
                this.action_signed = true;
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
            beforeOnSubmit(action, action_signed) {
                this.action = action;
                if (!action_signed && this.currUser.is_client) {
                    window.location = this.paid_url;
                } else {
                    this.action_signed = action_signed;
                    this.showModalConfirm();
                }
            },
            onSuccessConfirm () {
                this.action();
                this.hideModalConfirm();
            },
        },
        created() {
            this.checkActiveItem();
        }
    }
</script>