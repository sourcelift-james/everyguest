<template>
	<div>
		<div v-bind:class="{ active: isLoading }" class="ui dimmer">
			<div class="ui text loader">Loading</div>
		</div>
		<h2 class="header">Create a Group</h2>
		<div v-if="user.group">
			<p>Users may not have more than one group.</p>
		</div>
		<div v-else>
			<form @submit.prevent="submit">
				<div class="ui labeled input">
					<div class="ui label">Group Name</div>
					<input name="name" type="text" v-model="name" required>
				</div>
				<button type="submit" class="ui primary button">Submit</button>
			</form>
			<div v-if="message" v-bind:class="[messageColor]" class="ui message">
				{{ message }}
			</div>
		</div>
	</div>
</template>

<script>
export default {
	data: function() {
		return {
			user: '',
			isLoading: true,
			name: '',
			message: '',
			messageColor: 'green',
		}
	},
	mounted: function() {
		axios.get('/api/getuser', { withCredentials: true }).
		then(response => {
			this.user = response.data;
			this.isLoading = false;
		});
	},
	methods: {
		submit() {
			axios.post('/api/group/create', {
				name: this.name
			},  { withCredentials: true }).
			then(response => {
				this.message = response.data;
				if (response.status != 200) {
					this.messageColor = red;
				}
			});
		}
	}
};
</script>
