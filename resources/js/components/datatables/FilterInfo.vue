<template>
    <div class="dataTables_info">
        <span class="page-stats">
            {{pagination.from}} - {{pagination.to}} из {{pagination.total}} {{ $sklonyator(pagination.total, ['записи', 'записей', 'записей']) }}
        </span>
    </div>
</template>

<script>
    export default {
        props: ['pagination']
    }
</script>