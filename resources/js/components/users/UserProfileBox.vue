<template>
    <div v-show="loadedComponent">
        <div class="avatar">
            <label class="avatar-icon-touch">
                <input v-if="!isUploadingFile" type="file" accept="image/*" name="avatar" @change="onFileUpload">
                <input v-else type="file" accept="image/*" name="avatar" disabled>
            </label>
            <img v-if="profUser.avatar_medium" class="profile-user-img img-responsive img-circle" :src="profUser.avatar_medium" alt="User avatar">
        </div>
        <div v-if="fileUploadErrors.length > 0" class="avatar-upload-errors">
            <div class="form-group has-error">
                <div class="help-block with-errors">
                    {{fileUploadErrors}}
                </div>
            </div>
        </div>
        <h3 class="profile-username text-center">{{profUser.name}}</h3>
        <p class="text-muted text-center">{{profUser.role}}</p>
        <ul v-if="profUser.is_client || profUser.is_user || profUser.is_introducer" class="list-group list-group-unbordered">
            <li v-if="profUser.is_client || profUser.is_user" class="list-group-item box-profile-list">
                <b class="box-profile-list__title">Баланс:</b>
                <div v-if="!isProfile && currUser.is_admin" class="btn-group box-profile-list__btn">
                    <button type="button" class="btn btn-success" @click="openBalanceBox()">{{profUser.balance_humanize}}</button>
                    <button type="button" class="btn btn-success" @click="openBalanceBox()">
                        <i class="fa fa-edit"></i>
                    </button>
                </div>
                <div v-else class="btn-group box-profile-list__btn">
                    <span>{{profUser.balance_humanize}}</span>
                </div>
            </li>
            <li v-if="profUser.is_client || profUser.is_user" class="list-group-item box-profile-list">
                <b class="box-profile-list__title">Менеджер:</b>
                <div v-if="!isProfile" class="btn-group box-profile-list__btn">
                    <button type="button" class="btn btn-info" @click="openManagerBox()">{{managerName}}</button>
                    <button type="button" class="btn btn-info" @click="openManagerBox()">
                        <i class="fa fa-edit"></i>
                    </button>
                </div>
                <div v-else class="btn-group box-profile-list__btn">
                    <span>{{managerName}}</span>
                </div>
            </li>
            <li v-if="profUser.is_introducer && currUser.is_admin" class="list-group-item box-profile-list">
                <b class="box-profile-list__title">Наблюдаемые Клиенты:</b>
                <div class="btn-group box-profile-list__btn">
                    <vue-multiselect
                        tagPlaceholder="Имя клиента"
                        placeholder="Выберите клиентов"
                        :value="selectedClients"
                        :options="clientOptions"
                        :multiple="true"
                        :taggable="true"                                
                        @input="multiSelectHandler">
                    </vue-multiselect>
                </div>
            </li>
        </ul>

        <div v-if="!isProfile && (profUser.is_client || profUser.is_user)" class="box-profile-popup" :class="{'active': activeBoxManager}">
            <h4>Закрепить менеджера</h4>
            <div class="form-group">
                <label for="manager-select" >выберите менеджера:</label>
                <select id="manager-select" class="form-control select2" style="width: 100%;" v-model="managerSelected">
                    <option v-for="option in managerOptions" v-bind:value="option.value">
                        {{ option.text }}
                    </option>
                </select>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <button type="button" class="btn btn-danger pull-right box-profile-popup__cancel-btn" @click="closeManagerBox()">отмена</button>
                    <button type="button" class="btn btn-success pull-right box-profile-popup__success-btn" @click="attachManager()">сохранить</button>
                </div>
            </div>
        </div>
        <form-manual-balance v-if="!isProfile && currUser.is_admin && (profUser.is_client || profUser.is_user)"
                             :active="activeBoxBalance"
                             :profile-user="profUser"
                             @closeBalanceBoxEvent="closeBalanceBox"
                             @changedBalanceDone="changedBalanceDone"></form-manual-balance>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import FormManualBalance from '../forms/FormManualBalance';
    import MultiSelect from '../forms/MultiSelect'
    export default {
        props: {
            managers: {
                type: Array,
                default: () => []
            },
            profileUser: {
                type: Object,
                default: () => {}
            },
            currentManager: {
                type: Object,
                default: () => {}
            },
            allClients: {
                type: Array,
                default: () => []
            },
            usersIntruduceList: {
                type: Array,
                default: () => []
            },
            isProfile: {
                type: Boolean,
                default: () => true
            },
        },
        components: { 
            formManualBalance: FormManualBalance,
            vueMultiselect: MultiSelect
        },
        data: function() {
            return {
                selectedClients: [],
                clientOptions: [],
                manager_saved_process: false,
                balance_saved_process: false,
                profUser: this.profileUser,
                manager: this.currentManager,
                managerName: 'Не закреплен',
                activeBoxManager: false,
                activeBoxBalance: false,
                activeBoxIntroduced: false,
                managerSelected: 0,
                managerOptions: this.getOptionsManager(),
                selectedFile: null,
                fileUploadErrors: '',
                isUploadingFile: false,
                loadedComponent: false,
            }
        },

        computed: {
            // смешиваем результат mapGetters с внешним объектом computed
            ...mapGetters({
                currUser: 'getCurrentUser'
            })
        },

        methods: {
            multiSelectHandler (selected) {
                this.selectedClients = selected;
                axios.post('/users/' + this.profUser.id + '/sync-introducer', {selected: selected}).then(response => {
                    console.log(response)
                })
            },
            removeTag (tag) {
                this.selectedClients = this.selectedClients.filter( client => {
                    return client.code != tag.code
                });
            },
            openBalanceBox() {
                this.activeBoxBalance = true;
            },
            closeBalanceBox() {
                this.activeBoxBalance = false;
            },
            openManagerBox() {
                this.activeBoxManager = true;
            },

            closeManagerBox() {
                this.activeBoxManager = false;
            },

            openIntroducerBox() {
                this.activeBoxIntroduced = true;
            },

            closeIntroducerBox() {
                this.activeBoxIntroduced = false;
            },

            changedBalanceDone(user) {
                if (parseInt(user.id) === parseInt(this.currUser.id)) {
                    this.$store.dispatch('setCurrentUser', user);
                }
                this.profUser = user;
            },

            attachManager() {
                if (!this.manager_saved_process) {
                    this.manager_saved_process = true;
                    axios.post('/users/' + this.profUser.id + '/attach-manager', {'manager_id': this.managerSelected}).then(response => {
                        if (response.data.status === 'success') {
                            let newManager = response.data.currentManager ? response.data.currentManager : {};
                            this.profUser = response.data.user;
                            this.setManager(newManager);
                            this.closeManagerBox();
                        }
                        this.manager_saved_process = false;
                    }).catch(errors => {
                        //console.log(errors);
                        this.manager_saved_process = false;
                    });
                }
            },

            getOptionsManager() {
                let managerOptions = [{ text:'не выбран', value:0 }];
                this.managers.forEach(el => {
                    managerOptions.push({ text:el.name, value:el.id });
                });
                return managerOptions;
            },

            setManager(newManager) {
                // если есть менеджер то сетим его или пустой объект (в случаее открепления менеджера)
                if (newManager) {
                    this.manager = newManager;
                }
                if (this.manager.hasOwnProperty('name')) {
                    this.managerName = this.manager.name
                } else {
                    this.managerName = 'Не закреплен'
                }

                if (this.manager.hasOwnProperty('id')) {
                    this.managers.forEach(el => {
                        if (parseInt(el.id) === parseInt(this.manager.id)) {
                            this.managerSelected = parseInt(this.manager.id);
                        }
                    });
                } else {
                    this.managerSelected = 0;
                }
            },

            onFileUpload(event) {
                this.selectedFile = event.target.files[0];
                if (!this.isUploadingFile && this.selectedFile) {
                    this.isUploadingFile = true;
                    const formData = new FormData();
                    formData.append('avatar', this.selectedFile, this.selectedFile.name);
                    axios.post('/profile/' + this.profUser.id + '/avatar', formData, {
                        onUploadProgress: uploadEvent => {
                            //console.log('Upload progress: ' + Math.round(uploadEvent.loaded / uploadEvent.total * 100) + '%')
                        }
                    }).then(response => {
                        if (response.data.status === 'success') {
                            this.fileUploadErrors = '';
                            if (response.data.hasOwnProperty('user')) {
                                this.profUser = response.data.user;
                                if (parseInt(this.profUser.id) === parseInt(this.currUser.id)) {
                                    let user_avatar_thumb = document.querySelectorAll('.js-user-avatar-thumb');
                                    let user_avatar_small = document.querySelectorAll('.js-user-avatar-small');
                                    for (let i = 0; i < user_avatar_thumb.length; i++) {
                                        user_avatar_thumb[i].setAttribute('src', this.profUser.avatar);
                                    }
                                    for (let i = 0; i < user_avatar_small.length; i++) {
                                        user_avatar_small[i].setAttribute('src', this.profUser.avatar_small);
                                    }
                                }
                            }
                        }
                        if (response.data.status === 'error') {
                            if (response.data.errors.hasOwnProperty('avatar')) {
                                this.fileUploadErrors = response.data.errors.avatar[0];
                            }
                        }
                        this.isUploadingFile = false;
                    }).catch(errors => {
                        //console.log(errors);
                        this.isUploadingFile = false;
                    });
                }
            },
            profileUserEdit (user) {
                if (parseInt(this.profUser.id) === parseInt(user.id)) {
                    this.profUser = user;
                }
            },
            setClients() {
                this.allClients.forEach(client => {
                    this.clientOptions.push({ name:client.name, code:client.id });
                });
                this.usersIntruduceList.forEach(intruduced => {
                    this.selectedClients.push({ name:intruduced.name, code:intruduced.id });
                });
            }
        },

        created() {
            this.setManager();
        },

        mounted() {
            this.setClients()
            let _this = this;
            setTimeout(function() {
                _this.loadedComponent = true;
            }, 10);

            this.$root.$on('profileUserEdit', this.profileUserEdit)
        },
    }
</script>
