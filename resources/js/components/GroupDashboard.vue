<template>
	<div>
		<div v-bind:class="{ active: isLoading }" class="ui dimmer">
			<div class="ui text loader">Loading</div>
		</div>
		<h2 class="header" v-if="group">{{ group.name }}</h2>
	</div>
</template>

<script>
export default {
	data: function() {
		return {
			user: '',
			group: '',
			isLoading: true
		}
	},
	mounted: function() {
		axios.get('/api/getuser', { withCredentials: true }).
		then(response => {
			this.user = response.data;

			return axios.get('/api/group', {
				params: {
					id: this.user.group_id
				},
				withCredentials: true
			});
		}).
		then(response => {
			if (response.status != 200) {
				this.$router.push('home');
			}

			// If the authorized user does not belong to the group requested.
			if (this.user.group_id != response.data.id) {
				this.$router.push('home');
			}
			this.group = response.data;
			this.isLoading = false;
		});
	}
};
</script>
