<template>
    <div class="box-body">
        <div class="documents-table-wrapper dataTables_wrapper dt-bootstrap">
            <div class="row">
                <div class="col-xs-12">
                    <div v-if="currUser.is_manager" class="dataTables_action dataTables_action__mobile">
                        <button class="btn btn-success" @click="showModal">Добавить</button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-10">
                    <div class="dataTables_filter">
                        <label>
                            Поиск:
                            <input class="form-control input-sm" type="text" v-model="tableData.search" placeholder="Введите фразу"
                                   @input="getItems()" :disabled="item_count === 0 && !filter">
                        </label>
                    </div>
                    <div class="dataTables_length">
                        <label>
                            Показывать по:
                            <select v-model="tableData.length" @change="getItems()" class="form-control input-sm" :disabled="item_count === 0 && !filter">
                                <option v-for="(records, index) in perPage" :key="index" :value="records">{{records}}</option>
                            </select>
                        </label>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div v-if="currUser.is_manager || currUser.is_admin" class="dataTables_action dataTables_action__desktop">
                        <button class="btn btn-success" @click="showModal">Добавить</button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <datatable :columns="columns"
                               :sortKey="sortKey"
                               :sortOrders="sortOrders"
                               :excludeSortOrders="excludeSortOrders"
                               @sort="sortBy">
                        <tbody>
                        <tr v-if="item_count > 0" v-for="(item, index) in items" :key="item.id" :class="{'odd': index % 2 === 1, 'even': index % 2 === 0}">
                            <td v-if="isColumnExist('created_at')" class="created_at">
                                {{item.created_at_humanize}}
                            </td>
                            <td v-if="isColumnExist('client_full_name')" class="client_full_name">
                                <span v-if="item.client.last_name">{{item.client.last_name}} </span><span v-if="item.client.first_name">{{item.client.first_name}} </span><span v-if="item.client.second_name">{{item.client.second_name}}</span>
                            </td>
                            <td v-if="isColumnExist('name')" class="name">
                                {{item.name}}
                            </td>
                            <td v-if="isColumnExist('file')" class="file">
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
                            </td>
                            <td v-if="isColumnExist('status')" class="status">
                                <document-status :signed="item.signed"
                                                 :paid="item.paid"
                                                 :item="item"
                                                 :key="item.id"
                                                 @updateDocument="updateStatus"></document-status>
                            </td>
                            <td v-if="isColumnExist('amount')" class="amount">
                                {{item.amount_humanize}}
                            </td>
                            <td v-if="isColumnExist('manager_full_name')" class="manager_full_name">
                                <span v-if="item.manager.last_name">{{item.manager.last_name}} </span><span v-if="item.manager.first_name">{{item.manager.first_name}} </span><span v-if="item.manager.second_name">{{item.manager.second_name}}</span>
                            </td>
                            <td v-if="isColumnExist('action')" class="action">
                                <document-action :signed="item.signed"
                                                 :paid="item.paid"
                                                 :item="item"
                                                 :key="'0'+item.id"
                                                 :key-id="'0'+item.id"
                                                 @updateDocument="updateStatus"
                                                 @deleteDocument="deleteItem"
                                >
                                </document-action>
                            </td>
                        </tr>
                        <tr v-if="item_count === 0" class="odd empty-row">
                            <td colspan="8" align="center">
                                <span v-if="filter">Не найдено ни одного отчета</span>
                                <span v-if="!filter">
                                    <span v-if="currUser.is_admin || currUser.is_manager">Вы не создали ни одного отчета</span>
                                    <span v-if="!currUser.is_admin && !currUser.is_manager">У вас нет ни одного отчета</span>
                                </span>
                            </td>
                        </tr>
                        </tbody>
                    </datatable>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-5">
                    <filter-info :pagination="pagination" :item_count="item_count"></filter-info>
                </div>
                <div class="col-sm-7">
                    <div class="dataTables_paginate paging_simple_numbers">
                        <paginate v-show="pagination.lastPage > 1"
                                  v-model="pagination.currentPage"
                                  :page-count="pagination.lastPage"
                                  :page-range="3"
                                  :margin-pages="1"
                                  :click-handler="paginateCallback"
                                  :prev-text="'Назад'"
                                  :next-text="'Вперед'"
                                  :container-class="'pagination'"
                                  :page-class="'page-item'">
                        </paginate>
                    </div>
                </div>
            </div>
            <form-document-create :managers="managers"
                                  :ref="'formDocumentCreate'"
                                  :clear-form="clearForm"
                                  @createdDocument="createdDocumentEvent"
                                  @formCleared="clearFormDone"></form-document-create>

        </div>
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    import FormDocumentCreate from '../forms/FormDocumentCreate';
    import DocumentStatus from './DocumentStatus';
    import DocumentAction from './DocumentAction';
    import Datatable from '../datatables/Datatable.vue';
    import FilterInfo from '../datatables/FilterInfo.vue';
    import Paginate from 'vuejs-paginate';

    export default {
        props: {
            documents: {
                type: Object,
                default: () => {}
            },
            documents_count: {
                type: Number,
                default: () => 0
            },
            managers: {
                type: Array,
                default: () => []
            },
        },
        components: { datatable: Datatable, filterInfo: FilterInfo, paginate: Paginate, formDocumentCreate: FormDocumentCreate, documentStatus: DocumentStatus, documentAction: DocumentAction},
        data() {
            let sortOrders = {};
            let excludeSortOrders = {
                action: true,
                status: true,
                file: true,
            };
            let columns = [
                {width: 'auto', label: 'Дата', name: 'created_at' },
                {width: 'auto', label: 'ФИО', name: 'client_full_name' },
                {width: 'auto', label: 'Название', name: 'name' },
                {width: 'auto', label: 'Файл', name: 'file' },
                {width: 'auto', label: 'Статус', name: 'status' },
                {width: 'auto', label: 'Сумма', name: 'amount' },
                {width: 'auto', label: 'От кого', name: 'manager_full_name' },
                {width: '50px', label: 'Действия', name: 'action' },
            ];

            columns.forEach((column) => {
                sortOrders[column.name] = -1;
            });
            return {
                init_done: false,
                clearForm: false,
                excludeSortOrders: excludeSortOrders,
                items: [],
                item_count: this.documents_count,
                filter: false,
                columns: columns,
                sortKey: 'deadline',
                sortOrders: sortOrders,
                perPage: ['10', '20', '30'],
                tableData: {
                    draw: 0,
                    length: 10,
                    search: '',
                    column: '',
                    dir: 'desc',
                },
                pagination: {
                    lastPage: '',
                    currentPage: '',
                    total: '',
                    lastPageUrl: '',
                    nextPageUrl: '',
                    prevPageUrl: '',
                    from: '',
                    to: ''
                },
                collection_url: '/reports',
            }
        },
        computed: {
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
        methods: {
            updateStatus(document) {
                this.items.forEach(el => {
                    if (parseInt(el.id) === parseInt(document.id)) {
                        el.signed = document.signed;
                        el.paid = document.paid;
                    }
                });
            },
            paginateCallback(pageNum){
                this.getItems(this.collection_url+'?page='+pageNum)
            },
            showModal () {
                if (this.currUser.is_manager) {
                    this.$modal.show('document-create');
                }
            },
            hideModal () {
                if (this.currUser.is_manager) {
                    this.$modal.hide('document-create');
                }
            },
            createdSuccess () {
                this.$modal.hide('document-create');
                this.$modal.hide('document-create-confirm');
                //отчистить форму создания
                this.clearForm = true;
                // сброс что бы отобразилась хотя бы одна строка
                this.item_count = 1;
            },
            clearFormDone (val) {
              this.clearForm = false;
            },
            getItems(url = this.collection_url) {
                if (!this.init_done) {
                    this.init_done = true;
                    this.items = this.documents.data;
                    this.configPagination(this.documents);
                } else {
                    this.tableData.draw++;
                    axios.get(url, {params: this.tableData})
                        .then(response => {
                            let data = response.data;
                            if (this.tableData.draw === parseInt(data.draw)) {
                                this.items = data.documents.data;
                                this.item_count = this.items.length;
                                this.filter = true;
                                this.configPagination(data.documents);
                            }
                        })
                        .catch(errors => {
                            //console.log(errors);
                        });
                }
            },
            deleteItem() {
                this.paginateCallback(this.pagination.currentPage);
            },
            configPagination(data) {
                this.pagination.lastPage = data.last_page;
                this.pagination.currentPage = data.current_page;
                this.pagination.total = data.total;
                this.pagination.lastPageUrl = data.last_page_url;
                this.pagination.nextPageUrl = data.next_page_url;
                this.pagination.prevPageUrl = data.prev_page_url;
                this.pagination.from = data.from;
                this.pagination.to = data.to;
            },
            sortBy(key) {
                if (!this.excludeSortOrders.hasOwnProperty(key) && this.item_count > 0) {
                    this.sortKey = key;
                    this.sortOrders[key] = this.sortOrders[key] * -1;
                    this.tableData.column = key;
                    this.tableData.dir = this.sortOrders[key] === 1 ? 'asc' : 'desc';
                    this.getItems();
                }
            },
            getIndex(array, key, value) {
                return array.findIndex(i => i[key] === value)
            },
            createdDocumentEvent(document) {
                let newItems = [];
                newItems.push(document);
                this.items.forEach(el => {
                    newItems.push(el);
                });
                this.items = newItems;
                this.createdSuccess();
            },
            isColumnExist(name) {
                let column = this.columns.find(column => {
                    return column.name == name
                })

                if(column){
                    return true;
                }

                return false;
            },
            filterColumns() {
                this.columns = this.columns.filter( column => {
                    if(this.currUser.is_client){
                        if(column.name == 'client_full_name'){
                            return false;
                        } else if(column.name == 'status'){
                            return false;
                        } else if(column.name == 'manager_full_name'){
                            return false;
                        } else if(column.name == 'created_at'){
                            return false;
                        }
                    }

                    return true;
                });
            }
        },
        watch:{
            currUser() {
                if(this.currUser && this.currUser.is_client){
                    this.columns = this.columns.filter( column => {
                        if(column.name == 'client_full_name'){
                            return false;
                        } else if(column.name == 'status'){
                            return false;
                        } else if(column.name == 'manager_full_name'){
                            return false;
                        } else if(column.name == 'created_at'){
                            return false;
                        }

                        return true;
                    });
                }
            }
        },
        created() {
            this.getItems();
        },
    };
</script>
