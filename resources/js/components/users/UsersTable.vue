<template>
    <div class="users-table-wrapper dataTables_wrapper form-inline dt-bootstrap">
        <div class="row">
            <div class="col-sm-6">
                <div class="dataTables_length">
                    <label>
                        Показывать по:
                        <select v-model="tableData.length" @change="getUsers()" class="form-control input-sm">
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
                               @input="getUsers()">
                    </label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <datatable :columns="columns" :sortKey="sortKey" :sortOrders="sortOrders" :excludeSortOrders="excludeSortOrders" @sort="sortBy">
                    <tbody>
                        <tr v-for="(item, index) in projects" :key="item.id" :class="{'odd': index % 2 === 1, 'even': index % 2 === 0}">
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
                                <!--@prev="getUsers(pagination.prevPageUrl)"-->
                                <!--@next="getUsers(pagination.nextPageUrl)">-->
                    <!--</pagination>-->
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Datatable from '../datatables/Datatable.vue';
// import Pagination from '../datatables/Pagination.vue';
export default {
    props: ['users'],
    components: { datatable: Datatable},
    created() {
        this.getUsers();
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
            projects: [],
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
        getUsers(url = '/users') {
            if (this.init_data) {
                this.init_data = false;
                this.projects = this.users.data;
                this.configPagination(this.users);
            } else {
                this.tableData.draw++;
                axios.get(url, {params: this.tableData})
                    .then(response => {
                        let data = response.data;
                        if (this.tableData.draw === parseInt(data.draw)) {
                            this.projects = data.users.data;
                            this.configPagination(data.users.data);
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
                this.getUsers();
            }
        },
        getIndex(array, key, value) {
            return array.findIndex(i => i[key] === value)
        },
    }
};
</script>
