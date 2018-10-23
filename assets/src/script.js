import '@babel/polyfill';
import axios from 'axios';
import qs from 'qs';
import Vue from 'vue';
import App from './App.vue';

let root = document.getElementById('dominant-color-app');

if (root) {
  var vm = new Vue({
    el: root,
    components: {
      'dominant-color-app': App,
    }
  });
}

axios.post(ajaxurl, qs.stringify({action: 'dominant_color_status'}), {
  headers: { 'content-type': 'application/x-www-form-urlencoded' },
}).then((response) => {
  console.log(response);
}).catch(error => {
  console.log(error);
});
