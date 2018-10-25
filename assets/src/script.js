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
    translations: JSON.parse(root.dataset.translations),
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
    getData({ dispatch, commit }) {
      axios
        .post(ajaxurl, qs.stringify({ action: 'dominant_color_status' }), {
          headers: { 'content-type': 'application/x-www-form-urlencoded' },
        })
        .then(response => {
          commit('updateData', response.data);
          setTimeout(() => {
            dispatch('getData');
          }, 1000);
          console.log(response);
        })
        .catch(error => {
          console.log(error);
        });
    },
  },
});

if (root) {
  new Vue({
    el: root,
    store,
    components: {
      'dominant-color-app': App,
    },
  });
}
