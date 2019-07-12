<template>
    <div class="box-body">
        <div class="files-table-wrapper dataTables_wrapper dt-bootstrap" :class="currUser.is_admin || currUser.is_manager ? 'admin': ''">
            <div class="row">
                <div class="col-xs-12">
                    <div v-if="!currUser.is_manager && !currUser.is_admin" class="dataTables_action dataTables_action__mobile">
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
                    <div v-if="!currUser.is_manager && !currUser.is_admin" class="dataTables_action dataTables_action__desktop">
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
                            <td class="owner"><span v-show="item.model">{{item.model.name}}</span></td>
                            <td class="origin_name">
                                <div class="btn-group" >
                                    <a class="document-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-file-pdf-o" v-show="item.type==='application/pdf'"></i>
                                        <i class="fa fa-file-image-o" v-show="item.type==='image/png' || item.type==='image/jpeg'"></i>
                                        {{item.origin_name}}
                                    </a>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a :href="'/document/'+item.id" target="_blank">Посмотреть</a></li>
                                        <li><a :href="'/document/'+item.id+'?download=1'">Скачать</a></li>
                                    </ul>
                                </div>
                            </td>
                            <td class="created_at">{{item.created_at}}</td>
                        </tr>
                        <tr v-if="item_count === 0" class="odd empty-row">
                            <td colspan="3" align="center">
                                <span v-if="filter">Не найдено ни одного документа</span>
                                <span v-if="!filter">
                                    <span>Нет ни одного документа</span>
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
            <form-user-file-create :ref="'formDocumentCreate'"
                                   :clear-form="clearForm"
                                   @createdFile="createdFileEvent"
                                   @formCleared="clearFormDone"></form-user-file-create>

        </div>
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    import FormUserFileCreate from '../forms/FormUserFileCreate';
    import Datatable from '../datatables/Datatable.vue';
    import FilterInfo from '../datatables/FilterInfo.vue';
    import Paginate from 'vuejs-paginate';

    export default {
        props: {
            files: {
                type: Object,
                default: () => {}
            },
            files_count: {
                type: Number,
                default: () => 0
            },
        },
        components: { datatable: Datatable, filterInfo: FilterInfo, paginate: Paginate, FormUserFileCreate: FormUserFileCreate},
        data() {
            let sortOrders = {};
            let excludeSortOrders = {
            };
            let columns = [
                {width: 'auto', label: 'Владелец', name: 'owner' },
                {width: 'auto', label: 'Название', name: 'origin_name' },
                {width: 'auto', label: 'Дата', name: 'created_at' },
            ];

            columns.forEach((column) => {
                sortOrders[column.name] = -1;
            });
            return {
                init_done: false,
                clearForm: false,
                excludeSortOrders: excludeSortOrders,
                items: [],
                item_count: this.files_count,
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
                collection_url: '/documents',
            }
        },
        computed: {
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
        methods: {
            paginateCallback(pageNum){
                this.getItems(this.collection_url+'?page='+pageNum)
            },
            showModal () {
                if (!this.currUser.is_manager && !this.currUser.is_admin) {
                    this.$modal.show('file-create');
                }
            },
            hideModal () {
                if (!this.currUser.is_manager && !this.currUser.is_admin) {
                    this.$modal.hide('file-create');
                }
            },
            createdSuccess () {
                this.$modal.hide('file-create');
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
                    this.items = this.files.data;
                    this.configPagination(this.files);
                } else {
                    this.tableData.draw++;
                    axios.get(url, {params: this.tableData})
                        .then(response => {
                            let data = response.data;
                            if (this.tableData.draw === parseInt(data.draw)) {
                                this.items = data.files.data;
                                this.item_count = this.items.length;
                                this.filter = true;
                                this.configPagination(data.files);
                            }
                        })
                        .catch(errors => {
                            //console.log(errors);
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
            createdFileEvent(file) {
                let newItems = [];
                newItems.push(file);
                this.items.forEach(el => {
                    newItems.push(el);
                });
                this.items = newItems;
                this.createdSuccess();
            },
        },
        created() {
            this.getItems();
        },
    };
</script>
