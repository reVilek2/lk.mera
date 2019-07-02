<template>
    <div>
        <div class="row">
            <div class="col-lg-12">
                <h4>Информация о пользователе</h4>
                <div class="info-mb-5"><strong>ФИО:</strong> {{user.last_name}} {{user.first_name}} {{user.second_name}}</div>
                <div class="info-mb-5"><strong>Роль:</strong> {{user.role}}</div>
                <div class="info-mb-5"><strong>Зарегистрирован:</strong> {{user.created_at_short}}</div>
                <hr>
                <h4>Контактные данные</h4>
                <div class="info-mb-5">
                    <strong>Email:</strong>
                    <a v-if="user.email" :href="'mailto:'+user.email"> {{user.email}}</a>
                    <span v-else>—</span>
                </div>
                <div class="info-mb-5">
                    <strong>Дата подтверждения email:</strong>
                    <span v-if="user.email_verified_at">{{user.email_verified_at}}</span>
                    <span v-else>—</span>
                </div>
                <div class="info-mb-5">
                    <strong>Телефон:</strong>
                    <a v-if="user.phone" :href="'tel:'+user.phone">{{user.phone}}</a>
                    <span v-else>—</span>
                </div>
                <div class="info-mb-5">
                    <strong>Дата подтверждения телефона:</strong>
                    <span v-if="user.phone_verified_at">{{user.phone_verified_at}}</span>
                    <span v-else>—</span>
                </div>

                <br>
                <br>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import FormUserPassword from '../forms/FormUserPassword';
    export default {
        props: {
            user: {
                type: Object,
                default: () => {}
            },
        },
        computed: {
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },
        methods: {

        },
    }
</script>
