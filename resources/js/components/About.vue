<template>
	<div>
		<div v-bind:class="{ active: isLoading }" class="ui dimmer">
			<div class="ui text loader">Loading</div>
		</div>
		<h2 class="header">About</h2>
		<p v-if="user">{{ user.name }}</p>
		<p v-if="user.group_id">{{ user.group_id }}</p>
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
