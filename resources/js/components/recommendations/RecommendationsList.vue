<template>
    <div class="box-body">
        <div class="row">
            <div class="col-sm-10"></div>
            <div class="col-sm-2">
                <div v-if="currUser.is_manager" class="dataTables_action dataTables_action__desktop">
                    <button class="btn btn-success pull-right" @click="showModal">Добавить</button>
                </div>
            </div>
        </div>
        <form-recommendation-create
            :managers="managers"
            :ref="'formRecommendationCreate'"
            :clear-form="clearForm"
            @createdRecommendation="createdRecommendationHandler"
            @formCleared="clearFormDone"
        >
        </form-recommendation-create>
        <div class="row">
            <div class="recommendation-list-wrapper col-sm-12">
                <recommendation-item
                    v-for="recommendation in items"
                    :key="recommendation.id"
                    :recommendation="recommendation"
                    @showRecommendationStatusModal="showStatusModalHandler"
                >
                </recommendation-item>

                <span v-if="!items.length">У вас нет ни одной рекомендации</span>
            </div>
        </div>
        <div class="row">
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
        <modal name="recommendation-status-accepted"
                classes="v-modal"
                :min-width="200"
                :min-height="200"
                :width="'90%'"
                :height="'auto'"
                :max-width="500"
                :adaptive="true"
                :scrollable="true">
            <div class="v-modal-header">
                <button type="button" class="close" @click="$modal.hide('recommendation-status-accepted')">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="v-modal-title">Предупреждение</h4>
            </div>
            <div class="v-modal-body">
                Вы уверены, что хотите принять рекомендацию?
            </div>
            <div class="v-modal-footer">
                <button type="button" class="btn btn-default pull-right" @click="$modal.hide('recommendation-status-accepted')">Нет</button>
                <button type="button" class="btn btn-success" @click="clientResolve()" style="margin-right: 10px">Да</button>
            </div>
        </modal>
        <modal name="recommendation-status-declined"
                classes="v-modal"
                :min-width="200"
                :min-height="200"
                :width="'90%'"
                :height="'auto'"
                :max-width="500"
                :adaptive="true"
                :scrollable="true">
            <div class="v-modal-header">
                <button type="button" class="close" @click="$modal.hide('recommendation-status-declined')">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="v-modal-title">Предупреждение</h4>
            </div>
            <div class="v-modal-body">
                Вы уверены, что хотите отклонить рекомендацию?
            </div>
            <div class="v-modal-footer">
                <button type="button" class="btn btn-default pull-right" @click="$modal.hide('recommendation-status-declined')">Нет</button>
                <button type="button" class="btn btn-success" @click="clientResolve()" style="margin-right: 10px">Да</button>
            </div>
        </modal>
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    import Datatable from '../datatables/Datatable.vue';
    import Paginate from 'vuejs-paginate';
    import FormRecommendationCreate from '../forms/FormRecommendationCreate';
    import RecommendationItem from './RecommendationItem';

    export default {
        props: {
            recommendations: {
                type: Object,
                default: () => {}
            },
            recommendations_count: {
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
            paginate: Paginate,
            formRecommendationCreate: FormRecommendationCreate,
            recommendationItem: RecommendationItem
        },
        data() {

            return {
                init_done: false,
                clearForm: false,
                items: [],
                item_count: this.recommendations_count,
                modal_status_data: {
                    recommendation_id: '',
                    status: 'pending'
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
                collection_url: '/recommendations',
            }
        },
        computed: {
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
        methods: {
            showModal () {
                if (this.currUser.is_manager) {
                    this.$modal.show('recommendation-create');
                }
            },
            hideModal () {
                if (this.currUser.is_manager) {
                    this.$modal.hide('recommendation-create');
                }
            },
            clearFormDone (val) {
              this.clearForm = false;
            },
            getItems(url = this.collection_url) {
                if (!this.init_done) {
                    this.init_done = true;
                    this.items = this.recommendations.data;
                    this.configPagination(this.recommendations);
                } else {
                    axios.get(url, {params: this.tableData})
                        .then(response => {
                            let data = response.data;
                            this.items = data.recommendations.data;
                            this.item_count = this.items.length;
                            this.configPagination(data.recommendations);
                        })
                        .catch(errors => {
                            //console.log(errors);
                        });
                }
            },
            paginateCallback(pageNum){
                this.getItems(this.collection_url+'?page='+pageNum)
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
            createdRecommendationHandler(recommendation) {
                this.items.unshift(recommendation);
                this.createdSuccess();
            },
            createdSuccess () {
                this.$modal.hide('recommendation-create');
                this.$modal.hide('recommendation-create-confirm');
                //отчистить форму создания
                this.clearForm = true;
            },
            showStatusModalHandler(modalEvetData){
                this.modal_status_data = modalEvetData;
                this.$modal.show(`recommendation-status-${this.modal_status_data.status}`);
            },
            clientResolve(){
                if(this.isResolved){
                    return;
                }
                let data = {
                    status: this.modal_status_data.status
                }
                axios.post(`/recommendations/${this.modal_status_data.recommendation_id}/client-resolve`, data)
                    .then(response => {
                        if(response.data.status == 'success'){
                            const responseRecommendation = response.data.recommendation;
                            this.items = this.items.map( item => {
                                if(item.id == responseRecommendation.id){
                                    return responseRecommendation;
                                }
                                return item;
                            });
                        }

                        this.$modal.hide(`recommendation-status-${this.modal_status_data.status}`);
                    })
                    .catch(errors => {
                        this.$modal.hide(`recommendation-status-${this.modal_status_data.status}`);
                    });
            }
        },
        created() {
            this.getItems();
        },
    };
</script>
