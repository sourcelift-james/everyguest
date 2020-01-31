<template>
	<div>
		<h2 class="header">Create a Group</h2>
		<div v-if="user.group_id" class="ui message red">
			<p>You already belong to a group, and may not create another.</p>
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
			name: '',
			message: '',
			messageColor: 'green',
		}
	},
	mounted: function() {
		axios.get('/api/getuser', { withCredentials: true }).
		then(response => {
			this.user = response.data;
		}).
		catch(this.$root.errorHandler);
	},
	methods: {
		submit() {
			axios.post('/api/group/create', {
				name: this.name
			},  { withCredentials: true }).
			then(response => {
				this.message = response.data;
			}).
			catch(this.$root.errorHandler);
		}
	}
};
</script>
