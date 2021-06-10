<template>
    <div class="box-body">
        <div class="users-tableusers-table-wrapper dataTables_wrapper form-inline dt-bootstrap">
            <div class="row">
                <div class="col-sm-11">
                    <div class="dataTables_length">
                        <label>
                            Показывать по:
                            <select v-model="tableData.length" @change="getItems()" class="form-control input-sm">
                                <option v-for="(records, index) in perPage" :key="index" :value="records">{{records}}</option>
                            </select>
                        </label>
                    </div>
                    <div class="dataTables_filter">
                        <label>
                            Поиск:
                            <input class="form-control input-sm" type="text" v-model="tableData.search" placeholder="Введите фразу"
                                   @input="getItems()">
                        </label>
                    </div>
                </div>
                <div class="col-sm-1">
                    <div v-if="currUser.is_admin" class="dataTables_action dataTables_action__desktop">
                        <a href="/users/add" class="btn btn-success">Добавить</a>
                    </div>
                </div>
            </div>
            <div class="row" v-if="currUser.is_admin">
                <div class="col-sm-6"></div>
                <div class="col-sm-6">
                    <div class="dataTables_filter">
                        <label>
                            Поиск по интродьюсеру:
                            <select v-model="tableData.search_introducer" @change="getItems()" class="form-control input-sm">
                                <option :value="null">-</option>
                                <option v-for="(id, index) in Object.keys(introducers)" :key="index" :value="id">{{introducers[id]}}</option>
                            </select>
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
                                        <button v-on:click="removeUser(item.id)" class="btn btn-danger"><i class="fa fa-trash"></i></button>
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
import { mapGetters } from 'vuex';
import Datatable from '../datatables/Datatable.vue';
import FilterInfo from '../datatables/FilterInfo.vue';
import Paginate from 'vuejs-paginate';
import {mapGetters} from "vuex";

export default {
    props: {
        users: {
            type: Object,
            default: () => {}
        },
        introducers: {
            type: Object,
            default: () => {},
        },
        users_count: {
            type: Number,
            default: () => 0
        },
    },
    computed: {
        // смешиваем результат mapGetters с внешним объектом computed
        ...mapGetters({
            currUser: 'getCurrentUser'
        })
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
            {width: '80px', label: 'Действия', name: 'action' },

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
            perPage: ['30', '100', '300'],
            tableData: {
                draw: 0,
                length: 30,
                search: '',
                column: '',
                dir: 'desc',
                search_introducer: null,
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
    computed:{
        ...mapGetters({
            currUser: 'getCurrentUser'
        }),
    },
    methods: {
        removeUser(userId) {
            if (!confirm('Вы уверены что хотите удалить пользователя?')) {
                return;
            }

            axios.post(`/users/${userId}/remove`)
                .then(response => {
                    if (response.data.status !== 'success') {
                        return new Noty({
                            type: 'error',
                            text: response.data.errors.user,
                            layout: 'topRight',
                            timeout: 5000,
                            progressBar: true,
                            theme: 'metroui',
                        }).show();
                    }

                    this.getItems();
                })
                .catch(({response}) => {
                    if (response.status == 404) {
                        return new Noty({
                            type: 'error',
                            text: 'Пользователь не найден',
                            layout: 'topRight',
                            timeout: 5000,
                            progressBar: true,
                            theme: 'metroui',
                        }).show();
                    }
                    console.error(response)
                })
        },
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
    },
    mounted() {
        console.log(this.currUser);
    }
};
</script>
