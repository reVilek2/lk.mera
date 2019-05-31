<template>
    <ul class="pagination">
        <li v-if="pagination.prevPageUrl" class="paginate_button previous">
            <a href="#" @click="$emit('prev');">Назад</a>
        </li>
        <li v-else class="paginate_button previous disabled">
            <a>Назад</a>
        </li>
        <li v-if="pagination.nextPageUrl" class="paginate_button next">
            <a href="#" @click="$emit('next');">Вперед</a>
        </li>
        <li v-else class="paginate_button next disabled">
            <a href="#">Вперед</a>
        </li>
    </ul>
</template>

<script>
    export default {
        props: ['pagination', 'client', 'filtered']
    }
</script>