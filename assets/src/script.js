import '@babel/polyfill';
import axios from 'axios';
import qs from 'qs';
import Vue from 'vue';
import Vuex, { Store } from 'vuex';
import App from './App.vue';

let root = document.getElementById('dominant-color-app');

Vue.use(Vuex);

const store = new Store({
  state: {
    in_progress: false,
    total: 0,
    processed_images: 0,
    unprocessed_images: 0,
  },
  mutations: {
    updateData(state, data) {
      Object.assign(state, data);
    },
  },
  actions: {
    getData({ commit }) {
      axios
        .post(ajaxurl, qs.stringify({ action: 'dominant_color_status' }), {
          headers: { 'content-type': 'application/x-www-form-urlencoded' },
        })
        .then(response => {
          commit('updateData', response.data);
          console.log(response);
        })
        .catch(error => {
          console.log(error);
        });
    },
  },
});

store.dispatch('getData');

if (root) {
  var vm = new Vue({
    el: root,
    store,
    components: {
      'dominant-color-app': App,
    },
  });
}
