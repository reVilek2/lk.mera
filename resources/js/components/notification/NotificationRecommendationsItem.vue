<template>
    <li>
        <a :href="'#'">
            <div class="notification-recommendation">
                <div class="notification-recommendation__sender">
                    {{recommendation.sender.name}}
                </div>
                <div class="notification-recommendation__title">
                    {{recommendation.data.recommendation.title}}
                </div>
            </div>
            <small>
                <i class="fa fa-clock-o"></i> {{recommendation.data.recommendation.created_at_humanize}}
            </small>
        </a>
    </li>
</template>

<script>
    export default {
        props:['recommendation'],
        data(){return {}},
        mounted(){}
    }
</script>
