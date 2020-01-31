import './bootstrap';
import Vue from 'vue';
import VueRouter from 'vue-router'
import routes from './routes';

Vue.use(VueRouter);

import SideMenu from './components/SideMenu';
import ErrorMessage from './components/ErrorMessage';

let app = new Vue({
	el: '#app',
	data: {
		errorMessage: '',
	},
	router: new VueRouter(routes),
	components: {
		'side-menu': SideMenu,
		'error-message': ErrorMessage
	}
});

$('.ui.sidebar').sidebar('attach events', '.demo.menu .toggle.button')
.sidebar('attach events', '.sidebar .item');
