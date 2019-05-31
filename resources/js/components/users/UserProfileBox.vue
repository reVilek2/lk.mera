<template>
    <div>
        <div class="avatar">
            <form method="post" enctype="multipart/form-data">
                <label class="avatar-icon-touch">
                    <input type="file" accept="image/*" name="avatar" class="js-input-avatar">
                </label>
            </form>
            <img class="profile-user-img img-responsive img-circle" :src="user.avatar_medium" alt="User avatar">
        </div>
        <h3 class="profile-username text-center">{{user.name}}</h3>
        <p class="text-muted text-center">{{user.role}}</p>
        <ul class="list-group list-group-unbordered">
            <li class="list-group-item box-profile-manager">
                <b class="box-profile-manager__title">Менеджер:</b>
                <div class="btn-group box-profile-manager__btn">
                    <button type="button" class="btn btn-info" @click="openManagerBox()">{{managerName}}</button>
                    <button type="button" class="btn btn-info" @click="openManagerBox()">
                        <i class="fa fa-edit"></i>
                    </button>
                </div>
            </li>
        </ul>

        <div class="box-manager" :class="{'active': activeBoxManager}">
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
                    <button type="button" class="btn btn-danger pull-right box-manager__cancel-btn" @click="closeManagerBox()">отмена</button>
                    <button type="button" class="btn btn-success pull-right box-manager__success-btn" @click="attachManager()">сохранить</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
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
            currentManager: {
                type: Object,
                default: () => {}
            }
        },
        data: function() {
            return {
                saved_process: false,
                user: this.currentUser,
                manager: this.currentManager,
                managerName: 'Не закреплен',
                activeBoxManager: false,
                managerSelected: 0,
                managerOptions: this.getOptionsManager()
            }
        },

        methods: {
            openManagerBox() {
                this.activeBoxManager = true;
            },
            closeManagerBox() {
                this.activeBoxManager = false;
            },
            attachManager() {
                if (!this.saved_process) {
                    this.saved_process = true;
                    axios.post('/users/' + this.currentUser.id + '/attach-manager', {'manager_id': this.managerSelected}).then(response => {
                        if (response.data.status === 'success') {
                            this.setManager(response.data.currentManager);
                            this.closeManagerBox();
                        }
                        this.saved_process = false;
                    }).catch(errors => {
                        console.log(errors);
                        this.saved_process = false;
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
            }
        },
        mounted() {
            console.log('component UserProfileBox mounted');
            console.log(this.currentUser.name);
        },
        created() {
            this.setManager();
        },
    }
</script>
