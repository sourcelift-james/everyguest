import './bootstrap';
import Vue from 'vue';
import VueRouter from 'vue-router'
import routes from './routes';

Vue.use(VueRouter);

import SideMenu from './components/SideMenu';
import ErrorMessage from './components/ErrorMessage';
import Loader from './components/Loader';

let app = new Vue({
	el: '#app',
	data: {
		errorMessage: '',
		isLoading: false,
		axiosIntercepter: null
	},
	mounted() {
		this.enableInterceptor();
		this.enableErrorMonitor();
	},
	router: new VueRouter(routes),
	components: {
		'side-menu': SideMenu,
		'error-message': ErrorMessage,
		'loader': Loader
	},
	methods: {
		enableInterceptor() {
			this.axiosInterceptor = axios.interceptors.request.use((config) => {
				this.isLoading = true;
				return config;
			}, (error) => {
				this.isLoading = false;
				return Promise.reject(error);
			});

			axios.interceptors.response.use((response) => {
				this.isLoading = false;
				return response;
			}, function(error) {
				this.isLoading = false;
				return Promise.reject(error);
			});
		},
		disableInterceptor() {
			axios.interceptors.request.eject(this.axiosIntercepter);
		},
		enableErrorMonitor() {
			axios.interceptors.response.use((config) => {
				return config;
			}, function(error) {
				this.errorMessage = error.response.data;
				return Promise.reject(error);
			});
		}
	}
});

$('.ui.sidebar').sidebar('attach events', '.demo.menu .toggle.button')
.sidebar('attach events', '.sidebar .item');
