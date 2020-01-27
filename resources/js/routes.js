import Home from './components/Home';
import About from './components/About';
import CreateGroup from './components/CreateGroup';
import GroupDashboard from './components/GroupDashboard';

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
		},
		{
			path: '/group',
			component: GroupDashboard
		}
	]
}
