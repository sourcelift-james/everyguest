import './bootstrap';
import Vue from 'vue';
import VueRouter from 'vue-router'
import routes from './routes';

Vue.use(VueRouter);

let app = new Vue({
	el: '#app',
	data: {},
	router: new VueRouter(routes)
});

$('.ui.sidebar').sidebar('attach events', '.demo.menu .toggle.button')
.sidebar('attach events', '.sidebar .item');
