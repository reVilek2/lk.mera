<template>
    <div class="history-pay-table-wrapper dataTables_wrapper dt-bootstrap">
        <div class="row">
            <div class="col-sm-6">
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
                <div class="dataTables_filter">
                    <label>
                        Поиск:
                        <input class="form-control input-sm" type="text" v-model="tableData.search" placeholder="Введите фразу"
                               @input="getItems()">
                    </label>
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
                        <td class="created_at">{{item.created_at}}</td>
                        <td class="operation"><span :class="getOperationNameClass(item.operation)">{{item.operation_name}}</span></td>
                        <td class="amount"><span class="history-pay-amount">{{amountHumanize(item.operation, item.amount)}}</span></td>
                        <td class="comment">{{item.comment}}</td>
                        <td class="balance">{{balanceHumanize(item.balance)}}</td>
                    </tr>
                    <tr v-if="item_count === 0" class="odd empty-row">
                        <td colspan="5" align="center">
                            <span v-if="filter">Не найдено ни одной оплаты</span>
                            <span v-if="!filter">
                                <span>Нет ни одной оплаты</span>
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
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    import Datatable from '../datatables/Datatable.vue';
    import FilterInfo from '../datatables/FilterInfo.vue';
    import Paginate from 'vuejs-paginate';
    import {amountToHumanize} from "../../libs/utils";

    export default {
        props: {
            transactions: {
                type: Object,
                default: () => {}
            },
            transactions_count: {
                type: Number,
                default: () => 0
            },
        },
        components: { datatable: Datatable, filterInfo: FilterInfo, paginate: Paginate },
        data() {
            let sortOrders = {};
            let excludeSortOrders = {
                balance: true
            };
            let columns = [
                {width: 'auto', label: 'Дата', name: 'created_at' },
                {width: 'auto', label: 'Операция', name: 'operation' },
                {width: 'auto', label: 'Сумма', name: 'amount' },
                {width: 'auto', label: 'Комментарий', name: 'comment' },
                {width: 'auto', label: 'Баланс', name: 'balance' },
            ];

            columns.forEach((column) => {
                sortOrders[column.name] = -1;
            });
            return {
                init_done: false,
                excludeSortOrders: excludeSortOrders,
                items: [],
                item_count: this.transactions_count,
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
                collection_url: '/finances',
            }
        },
        computed: {
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
        methods: {
            amountHumanize(item, amount){
                return item === 'outgoing' ? '-' + amountToHumanize(amount) : '+' + amountToHumanize(amount);
            },
            balanceHumanize(balance){
                return amountToHumanize(balance);
            },
            paginateCallback(pageNum){
                this.getItems(this.collection_url+'?page='+pageNum)
            },
            getItems(url = this.collection_url) {
                if (!this.init_done) {
                    this.init_done = true;
                    this.items = this.transactions.data;
                    this.configPagination(this.transactions);
                } else {
                    this.tableData.draw++;
                    axios.get(url, {params: this.tableData})
                        .then(response => {
                            let data = response.data;
                            if (this.tableData.draw === parseInt(data.draw)) {
                                this.items = data.transactions.data;
                                this.item_count = this.items.length;
                                this.filter = true;
                                this.configPagination(data.transactions);
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
            getOperationName(value) {
                return value === 'outgoing' ? 'расход' : 'доход';
            },
            getOperationNameClass(value) {
                return value === 'outgoing' ? 'text-red' : 'text-green';
            },
        },
        created() {
            this.getItems();
        },
    };
</script>
