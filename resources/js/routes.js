import Home from './components/Home';
import CreateGroup from './components/CreateGroup';
import GroupDashboard from './components/GroupDashboard';
import GroupManage from './components/GroupManage';
import MemberDetails from './components/MemberDetails';
import SpacesList from './components/spaces/List';
import SpacesCreate from './components/spaces/Create';

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
        },
        {
            path: '/member',
            component: MemberDetails
        },
        {
            path: '/spaces',
            component: SpacesList
        },
        {
            path: '/spaces/create',
            component: SpacesCreate
        }
	]
}
