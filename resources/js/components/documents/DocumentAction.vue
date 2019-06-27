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
        <modal :name="modalCreditFail"
               classes="v-modal"
               :min-width="200"
               :min-height="200"
               :width="'90%'"
               :height="'auto'"
               :max-width="500"
               :adaptive="true"
               :scrollable="true">
            <div class="v-modal-header">
                <button type="button" class="close" @click="hideModalCreditFail">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="v-modal-title">Предупреждение</h4>
            </div>
            <div class="v-modal-body">
                Не достаточно средств для оплаты документа. Перейти на страницу оплаты?
            </div>
            <div class="v-modal-footer">
                <button type="button" class="btn btn-default pull-right" @click="hideModalCreditFail">Нет</button>
                <button type="button" class="btn btn-success" @click="redirectToPaid" style="margin-right: 10px">Да</button>
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
                container: 'document-action'+this.item.id,
                container_item: 'document-action-item'+this.item.id,
                box: 'document-action-box'+this.item.id,
                modalConfirm: 'document-action-confirm'+this.item.id,
                modalCreditFail: 'document-action-credit-fail'+this.item.id,
                paid_url: '/finances/payment',
                statusSigned: this.signed,
                statusPaid: this.paid,
                isUploadingForm:false,
                is_active_item:true,
                action: ()=> {},
                action_signed: true,
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
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
        methods: {
            actionSignedAndPaid() {
                if (this.currUser.is_admin || this.currUser.is_client) {
                    let data = {signed:1, paid: 1};
                    let url = '/documents/'+this.item.id+'/set-paid';
                    this.submitForm(url, data);
                }
            },
            actionPaid() {
                this.resetChanges();
                this.submitForm('/documents/'+this.item.id+'/set-paid', {paid:1});
            },
            actionSigned() {
                this.submitForm('/documents/'+this.item.id+'/set-signed', {signed:1});
            },

            submitForm(url, data, callback = () => {}) {
                this.resetChanges();
                if (!this.isUploadingForm) {
                    this.isUploadingForm = true;
                    axios.post(url, data).then(response => {
                        this.isUploadingForm = false;
                        if (response.data.status === 'success') {
                            this.setNewChange(response.data.document.signed, response.data.document.paid);
                            // обновляем currentUser в storage
                            if (response.data.hasOwnProperty('client')) {
                                if (parseInt(response.data.client.id) === parseInt(this.currUser.id)) {
                                    this.$store.dispatch('setCurrentUser', response.data.client);
                                }
                            }
                        }
                        if (response.data.status === 'error' && response.data.errors[0] === 'credit-fail') {
                            this.showModalCreditFail();
                        }
                        // выполнить функцию после ajax
                        callback();
                    }).catch(errors => {
                        console.log(errors);
                        this.isUploadingForm = false;
                    });
                }
            },

            redirectToPaid () {
                window.location.href = this.paid_url;
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
            showModalCreditFail () {
                this.$modal.show(this.modalCreditFail);
            },
            hideModalCreditFail () {
                this.$modal.hide(this.modalCreditFail);
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