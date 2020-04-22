import Home from './components/Home';
import CreateGroup from './components/CreateGroup';
import GroupDashboard from './components/GroupDashboard';
import GroupManage from './components/GroupManage';

export default {
	mode: 'history',
	routes: [
		{
			path: '/home',
			component: Home
		},
		{
			path: '/group/create',
			component: CreateGroup
		},
		{
			path: '/group',
			component: GroupDashboard
		},
        {
            path: '/group/manage',
            component: GroupManage
        }
	]
}
