<template>
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li v-for="(navTab, index) in navTabs" v-if="!navTab.disabled" :key="index" :class="{'active' : navTab.active}"><a :href="'#'+navTab.id" :data-target="navTab.id" @click="setActiveTab($event)">{{navTab.name}}</a></li>
        </ul>
        <div class="tab-content">
            <div v-for="(navTab, index) in navTabs" class="tab-pane" v-if="!navTab.disabled" :key="index" :id="navTab.id" :class="{'active' : navTab.active}">
                <user-profile-tab-info v-if="navTab.id === 'info' && !navTab.disabled" :user="profUser"></user-profile-tab-info>
                <user-profile-tab-settings v-if="navTab.id === 'settings' && !navTab.disabled" :user="profUser" @updatedUserSettings="updatedUserSettings"></user-profile-tab-settings>
                <user-profile-tab-password v-if="navTab.id === 'password' && !navTab.disabled" :user="profUser" @updatedUserPassword="updatedUserPassword"></user-profile-tab-password>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import UserProfileTabInfo from './UserProfileTabInfo';
    import UserProfileTabSettings from './UserProfileTabSettings';
    import UserProfileTabPassword from './UserProfileTabPassword';

    export default {
        props: {
            isProfile: {
                type: Boolean,
                default: () => true
            },
            profileUser: {
                type: Object,
                default: () => {}
            },
        },
        components: { userProfileTabInfo: UserProfileTabInfo, userProfileTabSettings: UserProfileTabSettings, userProfileTabPassword: UserProfileTabPassword},
        data: function() {
            return {
                defaultTab: this.isProfile ? 'settings': 'info',
                navTabs: [
                    {
                        id: 'info',
                        name: 'Информация',
                        active: false,
                        disabled: this.isProfile,
                    },
                    {
                        id: 'settings',
                        name: 'Учетные данные',
                        active: false,
                        disabled: false,
                    },
                    {
                        id: 'password',
                        name: 'Смена пароля',
                        active: false,
                        disabled: false,
                    }
                ],
                profUser: this.profileUser,
            }
        },

        computed: {
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },

        methods: {
            setActiveTab(event) {
                let tab;
                if (event) {
                    event.preventDefault();
                    tab = event.target.getAttribute('data-target');
                    window.location = "#"+tab;
                } else {
                    tab = location.hash.slice(1);
                }

                if (!tab) {
                    tab = this.defaultTab;
                }

                this.navTabs.forEach(el => {
                    el.active = el.id === tab
                });
            },
            updatedUserSettings(user) {
                this.profUser = user;
                this.$root.$emit('profileUserEdit', user);
            },
            updatedUserPassword(user) {
                this.profUser = user;
                this.$root.$emit('profileUserEdit', user);
            }
        },
        created() {
            this.setActiveTab();
        }
    }
</script>
