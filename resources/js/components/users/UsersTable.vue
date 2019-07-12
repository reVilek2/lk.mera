<template>
    <div class="box-body">
        <div class="users-table-wrapper dataTables_wrapper form-inline dt-bootstrap">
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
                    <div class="table-responsive">
                        <datatable :columns="columns" :sortKey="sortKey" :sortOrders="sortOrders" :excludeSortOrders="excludeSortOrders" @sort="sortBy">
                            <tbody>
                                <tr v-for="(item, index) in items" :key="item.id" :class="{'odd': index % 2 === 1, 'even': index % 2 === 0}">
                                    <td>{{item.id}}</td>
                                    <td><span v-if="item.last_name">{{item.last_name}} </span><span v-if="item.first_name">{{item.first_name}} </span><span v-if="item.second_name">{{item.second_name}}</span></td>
                                    <td>{{item.role}}</td>
                                    <td>{{item.email}}</td>
                                    <td>{{item.phone}}</td>
                                    <td>{{item.created_at_short}}</td>
                                    <td>
                                        <a :href="'/users/'+item.id" class="btn btn-info"><i class="fa fa-eye"></i></a>
                                    </td>
                                </tr>
                                <tr v-if="item_count === 0" class="odd empty-row">
                                    <td colspan="9" align="center">
                                        <span v-if="filter">Не найдено ни одного пользователя</span>
                                        <span v-if="!filter">
                                            <span>Нет ни одного пользователя</span>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </datatable>
                    </div>
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
    </div>
</template>

<script>
import Datatable from '../datatables/Datatable.vue';
import FilterInfo from '../datatables/FilterInfo.vue';
import Paginate from 'vuejs-paginate';

export default {
    props: {
        users: {
            type: Object,
            default: () => {}
        },
        users_count: {
            type: Number,
            default: () => 0
        },
    },
    components: { datatable: Datatable, filterInfo: FilterInfo, paginate: Paginate},
    created() {
        this.getItems();
    },
    data() {
        let sortOrders = {};
        let excludeSortOrders = {
            action: true,
        };
        let columns = [
            {width: '25px', label: 'ID', name: 'id' },
            {width: 'auto', label: 'ФИО', name: 'full_name' },
            {width: 'auto', label: 'Роль', name: 'role' },
            {width: 'auto', label: 'Email', name: 'email' },
            {width: 'auto', label: 'Телефон', name: 'phone' },
            {width: 'auto', label: 'Регистрация', name: 'created_at' },
            {width: '50px', label: 'Действия', name: 'action' },

        ];
        columns.forEach((column) => {
           sortOrders[column.name] = -1;
        });

        return {
            init_data: true,
            excludeSortOrders: excludeSortOrders,
            items: [],
            item_count: this.users_count,
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
            collection_url: '/users',
        }
    },
    methods: {
        paginateCallback(pageNum){
            this.getItems(this.collection_url+'?page='+pageNum)
        },
        getItems(url = this.collection_url) {
            if (this.init_data) {
                this.init_data = false;
                this.items = this.users.data;
                this.configPagination(this.users);
            } else {
                this.tableData.draw++;
                axios.get(url, {params: this.tableData})
                    .then(response => {
                        let data = response.data;
                        if (this.tableData.draw === parseInt(data.draw)) {
                            this.items = data.users.data;
                            this.item_count = this.items.length;
                            this.filter = true;
                            this.configPagination(data.users);
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
    }
};
</script>
