<template>
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header">
                Оплатить онлайн:
            </h2>
            <div class="payment-boxes">
                <payment-item-card :active="itemCardActive" @itemCardChangeActive="itemCardChangeActive"></payment-item-card>
            </div>
        </div>
    </div>
</template>
<script>
    import PaymentItemCard from './PaymentItemCard';
    export default {
        components: { paymentItemCard: PaymentItemCard},
        data() {
            return {
                itemCardActive: false
            }
        },
        methods: {
            itemCardChangeActive(active) {
                if (active) {
                    this.closedAllItem();
                }

                this.itemCardActive = active;
            },
            closedAllItem() {
                this.itemCardActive = false;
            }
        }
    }
</script>