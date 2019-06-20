export default {
    state:{
        currentUser: {}
    },
    mutations:{
        SET_CURRENT_USER (state, payload) {
            state.currentUser = payload;
        }
    },
    actions:{
        setCurrentUser({commit}, payload) {
            commit('SET_CURRENT_USER', payload);
        }
    },
    getters:{
        getCurrentUser (state) {
            return state.currentUser
        }
    }
}