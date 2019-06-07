<template>
    <div class="documents-table-wrapper dataTables_wrapper dt-bootstrap">
        <div class="row">
            <div class="col-sm-6">
                <div class="dataTables_filter">
                    <label>
                        Поиск:
                        <input class="form-control input-sm" type="text" v-model="tableData.search" placeholder="Введите фразу"
                               @input="getItems()">
                    </label>
                </div>
                <div class="dataTables_length">
                    <label>
                        Показывать по:
                        <select v-model="tableData.length" @change="getItems()" class="form-control input-sm">
                            <option v-for="(records, index) in perPage" :key="index" :value="records">{{records}}</option>
                        </select>
                    </label>
                </div>
            </div>
            <div class="col-sm-6">
                <div v-if="is_manager || is_admin" class="dataTables_action">
                    <button class="btn btn-success" @click="showModal">Добавить</button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <datatable :columns="columns" :sortKey="sortKey" :sortOrders="sortOrders" :excludeSortOrders="excludeSortOrders" @sort="sortBy">
                    <tbody>
                    <tr v-for="(item, index) in items" :key="item.id" :class="{'odd': index % 2 === 1, 'even': index % 2 === 0}">
                        <td>{{item.id}}</td>
                        <td>{{item.created_at_humanize}}</td>
                        <td><span v-if="item.client.last_name">{{item.client.last_name}} </span><span v-if="item.client.first_name">{{item.client.first_name}} </span><span v-if="item.client.second_name">{{item.client.second_name}}</span></td>
                        <td>{{item.name}}</td>
                        <td>
                            <div v-if="item.files.length > 0" v-for="file in item.files" :key="file.id" class="btn-group" >
                                <a class="document-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-file-pdf-o" v-show="file.type==='application/pdf'"></i>
                                    {{file.origin_name}}
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a :href="'/documents/'+item.id+'/files/'+file.id" target="_blank">Посмотреть</a></li>
                                    <li><a :href="'/documents/'+item.id+'/files/'+file.id+'?download=1'">Скачать</a></li>
                                </ul>
                            </div>
                        </td>
                        <td>
                            <document-status :signed="item.signed"
                                             :paid="item.paid"
                                             :is_admin="is_admin"
                                             :item_id="item.id"
                                             :key="item.id"></document-status>
                        </td>
                        <td>{{item.humanize_amount}}</td>
                        <td><span v-if="item.manager.last_name">{{item.manager.last_name}} </span><span v-if="item.manager.first_name">{{item.manager.first_name}} </span><span v-if="item.manager.second_name">{{item.manager.second_name}}</span></td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-info">Подписать и оплатить</button>
                                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <span class="caret"></span>
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="#">Подписать и оплатить</a></li>
                                    <li><a href="#">Подписать</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </datatable>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-5">
                <filter-info :pagination="pagination"></filter-info>
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
                              :current-user="currentUser"
                              :ref="'formDocumentCreate'"
                              :clear-form="clearForm"
                              @createdDocument="createdDocumentEvent"
                              @formCleared="clearFormDone"></form-document-create>

    </div>
</template>
<script>
    import FormDocumentCreate from '../forms/FormDocumentCreate';
    import DocumentStatus from './DocumentStatus';
    import Datatable from '../datatables/Datatable.vue';
    import FilterInfo from '../datatables/FilterInfo.vue';
    import Paginate from 'vuejs-paginate';

    export default {
        props: {
            documents: {
                type: Object,
                default: () => {}
            },
            managers: {
                type: Array,
                default: () => []
            },
            currentUser: {
                type: Object,
                default: () => {}
            },
        },
        components: { datatable: Datatable, filterInfo: FilterInfo, paginate: Paginate, formDocumentCreate: FormDocumentCreate, documentStatus: DocumentStatus},
        created() {
            this.getItems();
            this.setUserRole();
        },
        data() {
            let sortOrders = {};
            let excludeSortOrders = {
                action: true,
                manager_full_name: true,
                status: true,
                client_full_name: true,
                file: true,
            };
            let columns = [
                {width: '25px', label: 'ID', name: 'id' },
                {width: 'auto', label: 'Дата', name: 'created_at' },
                {width: 'auto', label: 'ФИО', name: 'client_full_name' },
                {width: 'auto', label: 'Название', name: 'name' },
                {width: 'auto', label: 'Файл', name: 'file' },
                {width: 'auto', label: 'Статус', name: 'status' },
                {width: 'auto', label: 'Сумма', name: 'amount' },
                {width: 'auto', label: 'От кого', name: 'manager_full_name' },
                {width: '190px', label: 'Действия', name: 'action' },
            ];

            columns.forEach((column) => {
                sortOrders[column.name] = -1;
            });
            return {
                init_done: false,
                clearForm: false,
                excludeSortOrders: excludeSortOrders,
                items: [],
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
                user: this.currentUser,
                is_user:false,
                is_client:false,
                is_manager:false,
                is_admin:false,
                collection_url: '/documents',
                status: {
                    text: 'Не подписан и не оплачен',
                    label: 'label-danger'
                },
            }
        },
        methods: {
            paginateCallback(pageNum){
                this.getItems(this.collection_url+'?page='+pageNum)
            },
            showModal () {
                if (this.is_manager || this.is_admin) {
                    this.$modal.show('document-create');
                }
            },
            hideModal () {
                if (this.is_manager || this.is_admin) {
                    this.$modal.hide('document-create');
                }
            },
            createdSuccess () {
                this.$modal.hide('document-create');
                this.$modal.hide('document-create-confirm');
                //отчистить форму создания
                this.clearForm = true;
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
                                this.configPagination(data.documents);
                            }
                        })
                        .catch(errors => {
                            console.log(errors);
                        });
                }
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
                if (!this.excludeSortOrders.hasOwnProperty(key)) {
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
            setUserRole () {
                this.currentUser.role_names.forEach(role => {
                    if (role === 'admin') {
                        this.is_admin = true;
                    }
                    if (role === 'manager') {
                        this.is_manager = true;
                    }
                    if (role === 'client') {
                        this.is_client = true;
                    }
                    if (role === 'user') {
                        this.is_user = true;
                    }
                });
            },
        }
    };
</script>
