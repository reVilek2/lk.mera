<template>
    <div class="box recommendation-box">
        <div class="box-header with-border">
            <div class="recommendation-box__labels-wrapper">
                <div class="recommendation-box__label-container label-container-common">
                    <span v-if="currUser.is_client" :class="getReceiverStatus(clientAsReciever).labelClass">{{getReceiverStatus(clientAsReciever).text}}</span>
                </div>
                <div class="recommendation-box__label-container label-container-date">
                    <span class="label label-primary">{{recommendationDate}}</span>
                </div>
            </div>
        </div>
        <div class="box-header">
            <h3 class="box-title">{{recommendation.title}}</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <nl2br tag="div" :text="recommendation.text" />
        </div>
        <!-- /.box-body -->
        <div v-if="currUser.is_client && !isResolved" class="box-footer">
            <button
                v-if="currUser.is_client"
                @click="showModalConfirm('declined')"
                :disabled="isResolved"
                type="button"
                class="btn btn-danger"
            >
                Отклонить
            </button>
            <button
                v-if="currUser.is_client"
                @click="showModalConfirm('accepted')"
                :disabled="isResolved"
                type="button"
                class="btn btn-success"
            >
                Принять
            </button>
        </div>

        <div v-if="(currUser.is_manager || currUser.is_introducer) && !isResolved" class="box-footer">
            <div class="box recommendation-cliets-box">
                <div class="box-header with-border">
                    <a href="#"
                        @click.prevent="toogleUserClientTable()"
                    >
                        <i class="fa fa-fw fa-users"></i> Клиенты
                    </a>
                </div>
                <div v-if="clientTableIsVisble" class="box-body">
                    <table class="table table-striped">
                        <tbody>
                        <tr>
                            <th>Имя</th>
                            <th style="width: 40px">Статус</th>
                        </tr>
                        <tr v-for="receiver in recommendation.receivers" :key="receiver.client_id">
                            <td>{{receiver.client_name}}</td>
                            <td><span :class="getReceiverStatus(receiver).labelClass">{{getReceiverStatus(receiver).text}}</span></td>
                        </tr>
                    </tbody></table>
                </div>
            </div>
        </div>

        <!-- box-footer -->
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import moment from 'moment'

export default {
        props: {
            recommendation: {
                type: Object,
            }
        },
        data() {
            return {
                clientTableIsVisble: false,
            }
        },
        computed:{
            ...mapGetters({
                currUser: 'getCurrentUser'
            }),
            isResolved(){
                if(!this.currUser.is_client){
                    return false;
                }

                let receivers = this.recommendation.receivers;
                if(!receivers || !receivers.length){
                    return false;
                }
                if(receivers[0].status == 'pending'){
                    return false;
                }

                return true;
            },
            recommendationDate(){
                console.log(this.currUser)
                return moment(this.recommendation.created_at).format("YYYY-MM-DD HH:mm")
            },
            clientAsReciever(){
                if(!this.currUser.is_client){
                    return null;
                }

                let receivers = this.recommendation.receivers;
                if(!receivers || !receivers.length){
                    return false;
                }

                return receivers[0];
            }
        },
        methods:{
            showModalConfirm (status) {
                if(this.isResolved){
                    return;
                }

                this.$emit('showRecommendationStatusModal', {
                    recommendation_id: this.recommendation.id,
                    status: status
                });
            },
            getReceiverStatus(receiver){
                let data = {
                    text: 'неизвестно',
                    labelClass: {
                        'label': true,
                        'label-default': true

                    }
                }

                if(!receiver){
                    return data;
                }

                data.status = receiver.status;
                switch(receiver.status){
                    case 'pending':
                        data.text = 'без ответа';
                        data.labelClass = {
                            'label': true,
                            'label-warning': true

                        }
                        break;
                    case 'accepted':
                        data.text = 'принято';
                        data.labelClass = {
                            'label': true,
                            'label-success': true
                        }
                        break;
                    case 'declined':
                        data.text = 'отклонено';
                        data.labelClass = {
                            'label': true,
                            'label-danger': true
                        }
                }

                return data
            },
            toogleUserClientTable(){
                this.clientTableIsVisble = !this.clientTableIsVisble
            }
        }
    }
</script>
