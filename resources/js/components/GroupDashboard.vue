<template>
	<div>
        <h2 class="header" v-if="group">{{ group.name }}</h2>
		<h5 class="header">Members</h5>
		<div class="ui bulleted list">
			<div class="item" v-for="member in members">
				{{ member.name }}
			</div>
		</div>
        <router-link to="/group/manage" v-if="group.owner_id == user.id">
            <button class="small ui labeled icon button">
                <i class="cog icon"></i>
                Manage
            </button>
        </router-link>
	</div>
</template>

<script>
export default {
	data: function() {
		return {
			user: '',
			group: '',
			members: []
		}
	},
	mounted: function() {
		axios.get('/api/auth/getuser', { withCredentials: true }).
		then(response => {
			this.user = response.data;

			return axios.get('/api/group/' + this.user.group_id, { withCredentials: true });
		}).
		then(response => {

			// If the authorized user does not belong to the group requested.
			if (this.user.group_id != response.data.id) {
				this.$router.push('home');
			}

			this.group = response.data;

			return axios.get('/api/group/' + this.group.id + '/members', { withCredentials: true });
		}).
		then(response => {
			this.members = response.data;
		}).
		catch(this.$root.errorHandler);
	}
};
</script>
