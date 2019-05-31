<template>
    <table class="table table-bordered table-striped dataTable">
        <thead>
            <tr>
                <th v-for="column in columns" :key="column.name" @click="$emit('sort', column.name)"
                    :class="!excludeSortOrders.hasOwnProperty(column.name) ? (sortKey === column.name ? (sortOrders[column.name] > 0 ? 'sorting_asc' : 'sorting_desc') : 'sorting') : ''"
                    :style="'width:'+column.width+';'+'cursor:pointer;'">
                    {{column.label}}
                </th>
            </tr>
        </thead>
        <slot></slot>
    </table>
</template>

<script>
    export default {
        props: ['columns', 'sortKey', 'sortOrders', 'excludeSortOrders']
    }
</script>