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
                <div class="dataTables_action">
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
                            <a v-for="file in item.files" :key="file.id" :href="file.path+file.name+'.'+file.extension" target="_blank">
                                <i class="fa" v-show="file.extension==='pdf'||file.extension==='doc'" :class="{'fa-file-pdf-o': file.extension==='pdf', 'fa-file-word-o': file.extension==='doc'}"></i> {{file.name+'.'+file.extension}}
                            </a>
                        </td>
                        <td><span class="label label-success">Подписан и оплачен</span></td>
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
                <div class="dataTables_info">
                    Showing 1 to 10 of 57 entries
                </div>
            </div>
            <div class="col-sm-7">
                <div class="dataTables_paginate paging_simple_numbers">
                    <!--<pagination :pagination="pagination"-->
                    <!--@prev="getItems(pagination.prevPageUrl)"-->
                    <!--@next="getItems(pagination.nextPageUrl)">-->
                    <!--</pagination>-->
                </div>
            </div>
        </div>
        <modal name="document-create"
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
                <form-document-create :managers="managers" :current-user="currentUser" :ref="'formDocumentCreate'" @createdDocument="createdDocumentEvent"></form-document-create>
            </div>
            <div class="v-modal-footer">
                <button type="button" class="btn btn-default pull-left" @click="hideModal">Отмена</button>
                <button type="button" class="btn btn-primary" @click="$refs.formDocumentCreate.onSubmit()">Добавить</button>
            </div>
        </modal>
    </div>
</template>
<script>
    import FormDocumentCreate from '../forms/FormDocumentCreate';
    import Datatable from '../datatables/Datatable.vue';
    // import Pagination from '../datatables/Pagination.vue';
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
        components: {
            datatable: Datatable,
            formDocumentCreate: FormDocumentCreate,
        },
        created() {
            this.getItems();
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
                init_data: true,
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
            }
        },
        methods: {
            showModal () {
                this.$modal.show('document-create');
            },
            hideModal () {
                this.$modal.hide('document-create');
            },
            getItems(url = '/documents') {
                if (this.init_data) {
                    this.init_data = false;
                    this.items = this.documents.data;
                    this.configPagination(this.documents);
                } else {
                    this.tableData.draw++;
                    axios.get(url, {params: this.tableData})
                        .then(response => {
                            let data = response.data;
                            if (this.tableData.draw === parseInt(data.draw)) {
                                this.items = data.documents.data;
                                this.configPagination(data.documents.data);
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
                this.$modal.hide('document-create');
            }
        }
    };
</script>
