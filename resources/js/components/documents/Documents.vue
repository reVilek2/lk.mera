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
                    <template v-if="!isMobile">
                    <datatable :columns="columns"
                            :sortKey="sortKey"
                            :sortOrders="sortOrders"
                            :excludeSortOrders="excludeSortOrders"
                            @sort="sortBy">
                        <tbody>
                            <documents-table-row
                                v-if="item_count > 0"
                                v-for="(item, index) in items"
                                :item="item"
                                :key="item.id"
                                :class="{'odd': index % 2 === 1, 'even': index % 2 === 0}"
                                :columns="columns"
                                @updateDocument="updateStatus"
                                @deleteDocument="deleteItem"
                            >
                            </documents-table-row>
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
                    </template>
                    <template v-else>
                        <documents-item
                            v-if="item_count > 0"
                            v-for="(item) in items"
                            :item="item"
                            :key="item.id"
                            :columns="columns"
                            @updateDocument="updateStatus"
                            @deleteDocument="deleteItem"
                        >
                        </documents-item>
                    </template>
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
    import DocumentsTableRow from './DocumentsTableRow';
    import DocumentsItem from './DocumentsItem';
    import Datatable from '../datatables/Datatable.vue';
    import FilterInfo from '../datatables/FilterInfo.vue';
    import Paginate from 'vuejs-paginate';
    import MobileCheck from "../../mixins/MobileCheck";

    export default {
        mixins: [MobileCheck],
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
        components: {
            datatable: Datatable,
            filterInfo: FilterInfo,
            paginate: Paginate,
            formDocumentCreate: FormDocumentCreate,
            documentsTableRow: DocumentsTableRow,
            documentsItem: DocumentsItem
        },
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
                this.paginateCallback(this.pagination.currentPage);
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
