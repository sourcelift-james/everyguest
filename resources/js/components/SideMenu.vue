<template>
	<div class="ui sidebar inverted vertical menu">
		<router-link class="item" to="/home">Home</router-link>
		<router-link class="item" to="/about">About</router-link>
		<router-link v-if="!user.group_id" class="item" to="/group/create">Create Group</router-link>
		<router-link v-else class="item" to="/group">Group Dashboard</router-link>
	</div>
</template>

<script>
export default {
	data: function() {
		return {
			user: '',
			isLoading: true
		}
	},
	mounted: function() {
		axios.get('/api/getuser', { withCredentials: true }).
		then(response => {
			this.user = response.data;
			this.isLoading = false;
		});
	}
};
</script>
