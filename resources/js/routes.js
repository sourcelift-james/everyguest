import Home from './components/Home';
import About from './components/About';
import CreateGroup from './components/CreateGroup';

export default {
	mode: 'history',
	routes: [
		{
			path: '/home',
			component: Home
		},
		{
			path: '/about',
			component: About
		},
		{
			path: '/group/create',
			component: CreateGroup
		}
	]
}
