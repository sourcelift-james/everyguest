<template>
	<div>
		<div v-bind:class="{ active: isLoading }" class="ui dimmer">
			<div class="ui text loader">Loading</div>
		</div>
		<h2 class="header" v-if="group">{{ group.name }}</h2>
		<h5 class="header">Members</h5>
		<div class="ui bulleted list">
			<div class="item" v-for="member in members">
				{{ member.name }}
			</div>
		</div>
	</div>
</template>

<script>
export default {
	data: function() {
		return {
			user: '',
			group: '',
			members: [],
			isLoading: true
		}
	},
	mounted: function() {
		axios.get('/api/getuser', { withCredentials: true }).
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
			this.isLoading = false;
		}).
		catch(error => {

			this.$root.errorMessage = error.response.data;

			this.isLoading = false;
		});
	}
};
</script>
