export default {
    state:{
        currentUser: {},
        currentManager: {},
        currentClients: [],
    },
    mutations:{
        SET_CURRENT_USER (state, payload) {
            state.currentUser = payload;
        },
        SET_CURRENT_MANAGER (state, payload) {
            state.currentManager = payload;
        },
        SET_CURRENT_CLIENTS (state, payload) {
            state.currentClients = payload;
        },
    },
    actions:{
        setCurrentUser({commit}, payload) {
            commit('SET_CURRENT_USER', payload);
        },
        setCurrentManager({commit}, payload) {
            commit('SET_CURRENT_MANAGER', payload);
        },
        setCurrentClients({commit}, payload) {
            commit('SET_CURRENT_CLIENTS', payload);
        },
    },
    getters:{
        getCurrentUser (state) {
            return state.currentUser
        },
        getCurrentManager (state) {
            return state.currentManager
        },
        getCurrentClients (state) {
            return state.currentClients
        }
    }
}