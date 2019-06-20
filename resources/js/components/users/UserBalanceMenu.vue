<template>
    <li class="balance-menu">
        <a href="#"><svg class="icon-finance" style="width: 18px; height: 18px;" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="#svg-finance"></use>
            <symbol id="svg-finance" viewBox="0 0 32 32">
                <path d="M30 0.688h-23.5l8 4h13.5v2h-9.5l2.625 1.313c1.063 0.563 1.938 1.563 2.438 2.688h4.438v2h-4v10h6c1.125 0 2-0.875 2-2v-18c0-1.063-0.875-2-2-2zM20.188 9.813l-16.375-8.188c-0.25-0.188-0.5-0.188-0.75-0.188-0.625 0-1.063 0.438-1.063 1.25v18c0 1.125 0.813 2.438 1.813 2.938l16.375 8.188c0.25 0.125 0.5 0.188 0.75 0.188 0.625 0 1.063-0.5 1.063-1.313v-18c0-1.063-0.813-2.375-1.813-2.875zM16 22.688c-1.125 0-2-1.313-2-3 0-1.625 0.875-3 2-3s2 1.375 2 3c0 1.688-0.875 3-2 3z"></path>
            </symbol>
        </svg>{{balance}}</a>
    </li>
</template>
<script>
    import { mapGetters } from 'vuex';
    export default {
        data: function() {
            return {
                balance: 0,
            }
        },
        computed: {
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
        watch: {
            currUser(user) {
                this.balance = this.isEmptyObject(user) || user.balance === 0 ? '0 руб.' : user.balance_humanize;
            }
        },
        methods: {
            isEmptyObject(obj) {
                for (let i in obj) {
                    if (obj.hasOwnProperty(i)) {
                        return false;
                    }
                }
                return true;
            }
        }
    }
</script>