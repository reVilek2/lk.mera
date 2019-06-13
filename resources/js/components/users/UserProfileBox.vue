<template>
    <div v-show="loadedComponent">
        <div class="avatar">
            <label class="avatar-icon-touch">
                <input v-if="!isUploadingFile" type="file" accept="image/*" name="avatar" @change="onFileUpload">
                <input v-else type="file" accept="image/*" name="avatar" disabled>
            </label>
            <img class="profile-user-img img-responsive img-circle" :src="user.avatar_medium" alt="User avatar">
        </div>
        <div v-if="fileUploadErrors.length > 0" class="avatar-upload-errors">
            <div class="form-group has-error">
                <div class="help-block with-errors">
                    {{fileUploadErrors}}
                </div>
            </div>
        </div>
        <h3 class="profile-username text-center">{{user.name}}</h3>
        <p class="text-muted text-center">{{user.role}}</p>
        <ul v-if="is_role_user_or_client" class="list-group list-group-unbordered">
            <li class="list-group-item box-profile-list">
                <b class="box-profile-list__title">Баланс:</b>
                <div v-if="!isProfile" class="btn-group box-profile-list__btn">
                    <button type="button" class="btn btn-success" @click="openBalanceBox()">{{user.humanize_balance}}</button>
                    <button type="button" class="btn btn-success" @click="openBalanceBox()">
                        <i class="fa fa-edit"></i>
                    </button>
                </div>
                <div v-else class="btn-group box-profile-list__btn">
                    <span>{{user.humanize_balance}}</span>
                </div>
            </li>
            <li class="list-group-item box-profile-list">
                <b class="box-profile-list__title">Менеджер:</b>
                <div v-if="!isProfile" class="btn-group box-profile-list__btn">
                    <button type="button" class="btn btn-info" @click="openManagerBox()">{{managerName}}</button>
                    <button type="button" class="btn btn-info" @click="openManagerBox()">
                        <i class="fa fa-edit"></i>
                    </button>
                </div>
                <div v-else class="btn-group box-profile-list__btn">
                    <a href="#">{{managerName}}</a>
                </div>
            </li>
        </ul>

        <div v-if="!isProfile && is_role_user_or_client" class="box-profile-popup" :class="{'active': activeBoxManager}">
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

        <form-manual-balance v-if="!isProfile && is_role_user_or_client"
                             :active="activeBoxBalance"
                             :profile-user="user"
                             @closeBalanceBoxEvent="closeBalanceBox"
                             @changedBalanceDone="changedBalanceDone"></form-manual-balance>
    </div>
</template>

<script>
    import FormManualBalance from '../forms/FormManualBalance';
    export default {
        props: {
            managers: {
                type: Array,
                default: () => []
            },
            currentUser: {
                type: Object,
                default: () => {}
            },
            profileUser: {
                type: Object,
                default: () => {}
            },
            currentManager: {
                type: Object,
                default: () => {}
            },
            isProfile: {
                type: Boolean,
                default: () => true
            }
        },
        components: { formManualBalance: FormManualBalance},
        data: function() {
            return {
                manager_saved_process: false,
                balance_saved_process: false,
                user: this.profileUser,
                manager: this.currentManager,
                managerName: 'Не закреплен',
                activeBoxManager: false,
                activeBoxBalance: false,
                managerSelected: 0,
                managerOptions: this.getOptionsManager(),
                is_role_user_or_client: false,
                selectedFile: null,
                fileUploadErrors: '',
                isUploadingFile: false,
                loadedComponent: false,
            }
        },

        methods: {
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

            changedBalanceDone(user) {
                this.user = user;
            },

            attachManager() {
                if (!this.manager_saved_process) {
                    this.manager_saved_process = true;
                    axios.post('/users/' + this.user.id + '/attach-manager', {'manager_id': this.managerSelected}).then(response => {
                        if (response.data.status === 'success') {
                            let newManager = response.data.currentManager ? response.data.currentManager : {};
                            this.user = response.data.user;
                            this.setManager(newManager);
                            this.closeManagerBox();
                        }
                        this.manager_saved_process = false;
                    }).catch(errors => {
                        console.log(errors);
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

            checkUserOrClientRoles() {
                for(let key in this.user.role_names) {
                    if (this.user.role_names.hasOwnProperty(key)) {
                        if (this.user.role_names[key] === 'user' || this.user.role_names[key] === 'client') {
                            this.is_role_user_or_client = true;
                        }
                    }
                }
            },

            onFileUpload(event) {
                this.selectedFile = event.target.files[0];
                if (!this.isUploadingFile && this.selectedFile) {
                    this.isUploadingFile = true;
                    const formData = new FormData();
                    formData.append('avatar', this.selectedFile, this.selectedFile.name);
                    axios.post('/profile/' + this.user.id + '/avatar', formData, {
                        onUploadProgress: uploadEvent => {
                            console.log('Upload progress: ' + Math.round(uploadEvent.loaded / uploadEvent.total * 100) + '%')
                        }
                    }).then(response => {
                        console.log(response);
                        if (response.data.status === 'success') {
                            this.fileUploadErrors = '';
                            if (response.data.hasOwnProperty('user')) {
                                this.user = response.data.user;
                                if (parseInt(this.user.id) === parseInt(this.currentUser.id)) {
                                    let user_avatar_thumb = document.querySelectorAll('.js-user-avatar-thumb');
                                    let user_avatar_small = document.querySelectorAll('.js-user-avatar-small');
                                    for (let i = 0; i < user_avatar_thumb.length; i++) {
                                        user_avatar_thumb[i].setAttribute('src', this.user.avatar);
                                    }
                                    for (let i = 0; i < user_avatar_small.length; i++) {
                                        user_avatar_small[i].setAttribute('src', this.user.avatar_small);
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
                        console.log(errors);
                        this.isUploadingFile = false;
                    });
                }
            }
        },

        created() {
            this.setManager();
            this.checkUserOrClientRoles();
        },

        mounted() {
            console.log('component UserProfileBox mounted');
            let _this = this;
            // прослушивание скролла
            setTimeout(function(){
                _this.loadedComponent = true;
            }, 10);
        },
    }
</script>
