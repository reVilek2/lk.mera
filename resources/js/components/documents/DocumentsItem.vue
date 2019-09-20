<template>
   <div class="box document-box" :class="[classPaid]">
        <div class="box-header with-border">
            <h3 class="box-title">{{item.name}}</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="container-fluid">
                <div v-if="isColumnExist('created_at')" class="row created_at">
                    <div class="col-xs-4 column-title">Дата</div>
                    <div class="col-xs-8 column-content">{{item.created_at_humanize}}</div>
                </div>
                <div v-if="isColumnExist('client_full_name')" class="row client_full_name">
                    <div class="col-xs-4 column-title">ФИО</div>
                    <div class="col-xs-8 column-content"><span v-if="item.client.last_name">{{item.client.last_name}} </span><span v-if="item.client.first_name">{{item.client.first_name}} </span><span v-if="item.client.second_name">{{item.client.second_name}}</span></div>
                </div>
                <div v-if="isColumnExist('file')" class="row file">
                    <div class="col-xs-4 column-title">Файл</div>
                    <div class="col-xs-8 column-content">
                        <div v-if="item.files.length > 0" v-for="file in item.files" :key="file.id" class="btn-group" >
                            <a class="document-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-file-pdf-o" v-show="file.type==='application/pdf'"></i>
                                {{file.origin_name}}
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a :href="'/reports/'+item.id+'/files/'+file.id" target="_blank">Посмотреть</a></li>
                                <li><a :href="'/reports/'+item.id+'/files/'+file.id+'?download=1'">Скачать</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div v-if="isColumnExist('status')" class="row status">
                    <div class="col-xs-4 column-title">Статус</div>
                    <div class="col-xs-8 column-content">
                        <document-status :signed="item.signed"
                                :paid="item.paid"
                                :item="item"
                                :key="item.id"
                                @updateDocument="updateDocument">
                        </document-status>
                    </div>
                </div>
                <div v-if="isColumnExist('amount')" class="row amount">
                    <div class="col-xs-4 column-title">Сумма к оплате</div>
                    <div class="col-xs-8 column-content">{{item.amount_humanize}}</div>
                </div>
                <div v-if="isColumnExist('manager_full_name')" class="row manager_full_name">
                    <div class="col-xs-4 column-title">От кого</div>
                    <div class="col-xs-8 column-content"><span v-if="item.manager.last_name">{{item.manager.last_name}} </span><span v-if="item.manager.first_name">{{item.manager.first_name}} </span><span v-if="item.manager.second_name">{{item.manager.second_name}}</span></div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->

        <div v-if="isColumnExist('action')" class="box-footer">
            <document-action :signed="item.signed"
                                :paid="item.paid"
                                :item="item"
                                :key="'0'+item.id"
                                :key-id="'0'+item.id"
                                @updateDocument="updateDocument"
                                @deleteDocument="deleteDocument"
            >
            </document-action>
        </div>
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    import DocumentStatus from './DocumentStatus';
    import DocumentAction from './DocumentAction';
    import DocumentItem from '../../mixins/DocumentItem';

    export default {
        mixins: [DocumentItem],
        components: { documentStatus: DocumentStatus, documentAction: DocumentAction},
        computed: {
            classPaid: function () {
                const item = this.item;

                let isPaid = false;
                if(this.currUser.is_client){
                    isPaid = !!item.paid && !!item.signed
                }

                return {
                    'document-box-paid': isPaid
                };
            },

            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
    };
</script>
