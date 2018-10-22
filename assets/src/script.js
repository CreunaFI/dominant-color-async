import axios from 'axios';
import '@babel/polyfill';
import qs from 'qs';

axios.post(ajaxurl, qs.stringify({action: 'dominant_color_status'}), {
  headers: { 'content-type': 'application/x-www-form-urlencoded' },
}).then((response) => {
  console.log(response);
}).catch(error => {
  console.log(error);
});

console.log('hello world');
