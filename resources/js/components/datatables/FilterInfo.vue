<template>
    <div class="dataTables_info">
        <span v-if="item_count > 0" class="page-stats">
            <span v-if="pagination.from !== null">{{pagination.from}} -</span>
            <span v-if="pagination.to !== null">{{pagination.to}} из</span>
            <span v-if="pagination.total !== 0">{{pagination.total}} {{ $sklonyator(pagination.total, ['записи', 'записей', 'записей']) }}</span>
            <span v-if="pagination.total === 0">{{item_count}} {{ $sklonyator(item_count, ['запись', 'записи', 'записей']) }}</span>
        </span>
        <span v-else class="page-stats">
            0 записей
        </span>
    </div>
</template>

<script>
    export default {
        props: ['pagination', 'item_count']
    }
</script>