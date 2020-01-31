<template>
	<div>
		<h2 class="header" v-if="group">Manage Group Details</h2>
		<div class="ui divider"></div>
		<form @submit.prevent="updateName">
			<div class="ui labeled input">
				<div class="ui label">Group Name</div>
				<input name="name" type="text" v-model="name" required>
			</div>
			<button type="submit" class="ui primary button">Update Name</button>
		</form>
		<form @submit.prevent="inviteMember">
			<div class="ui labeled input">
				<div class="ui label">Invite Member by Email</div>
				<input name="email" type="text" v-model="email" required>
			</div>
			<button type="submit" class="ui primary button">Invite Member</button>
	</div>
</template>

<script>
export default {
	data: function() {
		return {
			user: '',
			group: '',
			name: ''
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

			// If the authorized user is not the owner of the group.
			if (this.user.id != response.data.owner_id) {
				this.$router.push('home');
			}

			this.group = response.data;
			this.name = this.group.name;
		}).
		catch(this.$root.errorHandler);
	},
	methods: {
		updateName() {
			axios.post('/api/group/update', {
				name: this.name
			},  { withCredentials: true }).
			then(response => {
				this.message = response.data;
			}).
			catch(this.$root.errorHandler);
		},
		inviteMember() {
			axios.post('/api/group/invite', {
				email: this.email
			},  { withCredentials: true }).
			then(response => {
				this.message = response.data;
			}).
			catch(this.$root.errorHandler);
		}
	}
};
</script>
