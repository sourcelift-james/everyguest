import './bootstrap';
import Vue from 'vue';
import VueRouter from 'vue-router'
import routes from './routes';

Vue.use(VueRouter);

import SideMenu from './components/SideMenu';

let app = new Vue({
	el: '#app',
	data: {},
	router: new VueRouter(routes),
	components: {
		'side-menu': SideMenu
	}
});

$('.ui.sidebar').sidebar('attach events', '.demo.menu .toggle.button')
.sidebar('attach events', '.sidebar .item');
