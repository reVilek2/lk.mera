<template>
    <div class="box recommendation-box">
        <div class="box-header with-border">
            <h3 class="box-title">{{recommendation.title}}</h3>
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
        <div v-if="currUser.is_client" class="box-footer">
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
        computed:{
            ...mapGetters({
                currUser: 'getCurrentUser'
            }),
            isResolved(){
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
            }
        }
    }
</script>
