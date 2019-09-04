<template>
    <div class="box recommendation-box">
        <div class="box-header with-border">
            <h3 v-if="currUser.is_client" class="box-title">{{recommendation.title}} - <span :class="getReceiverStatus(clientAsReciever).labelClass">{{getReceiverStatus(clientAsReciever).text}}</span></h3>
            <h3 v-if="currUser.is_manager" class="box-title">{{recommendation.title}}</h3>
            <div class="box-tools pull-right">
                <!-- Buttons, labels, and many other things can be placed here! -->
                <!-- Here is a label for example -->
                <span class="label label-primary">{{recommendationDate}}</span>
            </div>
        <!-- /.box-tools -->
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            {{recommendation.text}}
        </div>
        <!-- /.box-body -->
        <div v-if="currUser.is_client && !isResolved" class="box-footer">
            <button
                v-if="currUser.is_client"
                @click="showModalConfirm('accepted')"
                :disabled="isResolved"
                type="button"
                class="btn btn-primary"
            >
             Принять
            </button>
            <button
                v-if="currUser.is_client"
                @click="showModalConfirm('declined')"
                :disabled="isResolved"
                type="button"
                class="btn btn-danger"
            >
                Отлонить
            </button>
        </div>

        <div v-if="currUser.is_manager && !isResolved" class="box-footer">
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
