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
		axiosIntercepter: null,
		access: true
	},
	mounted() {
		this.enableInterceptor();
		this.enableErrorMonitor();
	},
	updated() {
		if (this.$route.path != '/home') {
			this.errorMessage = '';
		}
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
			}, (error) => {
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
			}, (error) => {

				if (error.response) {

					this.errorMessage = error.response.data;

					if (error.response.status == 401) {
						this.$router.push('home');
					}
				}

				return Promise.reject(error);
			});
		},
		errorHandler(error) {
	        // Stolen shamelessly from https://gist.github.com/fgilio/230ccd514e9381fafa51608fcf137253
	        if (error.response) {
	            /*
	             * The request was made and the server responded with a
	             * status code that falls out of the range of 2xx
	             */
	            console.log(error.response.data);
	            console.log(error.response.status);
	            console.log(error.response.headers);
	        } else if (error.request) {
	            /*
	             * The request was made but no response was received, `error.request`
	             * is an instance of XMLHttpRequest in the browser and an instance
	             * of http.ClientRequest in Node.js
	             */
	            console.log(error.request);
	        } else {
	            // Something happened in setting up the request and triggered an Error
	            console.log('Error', error.message);
	        }
		}
	}
});

$('.ui.sidebar').sidebar('attach events', '.demo.menu .toggle.button')
.sidebar('attach events', '.sidebar .item');
