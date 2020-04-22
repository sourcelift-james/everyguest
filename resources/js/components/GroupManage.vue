<template>
	<div>
		<h2 class="header" v-if="group">Manage Group Details</h2>
		<div class="ui divider"></div>
		<form @submit.prevent="updateGroup" class="ui form">
            <div class="field">
                <label>Group Name</label>
                <input name="name" type="text" v-model="name" required>
            </div>
            <div class="field">
                <label>Group Owner</label>
                <select name="owner" class="ui fluid dropdown" v-model="owner">
                    <option v-for="member in members" v-bind:value="member.id">{{ member.name }}</option>
                </select>
            </div>
			<button type="submit" class="ui primary button">Update Name</button>
		</form>
        <div class="ui divider"></div>
		<form @submit.prevent="inviteMember" class="ui form">
			<div class="field">
				<label>Invite Member by Email</label>
				<input name="email" type="text" v-model="email" required>
			</div>
			<button type="submit" class="ui primary button">Invite Member</button>
        </form>
	</div>
</template>

<script>
export default {
	data: function() {
		return {
			user: '',
			group: '',
            members: [],
            owner: '',
			name: '',
            email: ''
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

			// If the authorized user is not the owner of the group.
			if (this.user.id != response.data.owner_id) {
				this.$router.push('home');
			}

			this.group = response.data;
			this.owner = this.group.owner_id;
			this.name = this.group.name;

            return axios.get('/api/group/' + this.group.id + '/members', { withCredentials: true });
        }).
        then(response => {
            this.members = response.data;
		}).
		catch(this.$root.errorHandler);
	},
	methods: {
		updateGroup() {
			axios.post('/api/group/' + this.group.id + '/update', {
				name: this.name,
                owner: this.owner
			},  { withCredentials: true }).
			then(response => {
				this.message = response.data;
			}).
			catch(this.$root.errorHandler);
		},
		inviteMember() {
			axios.post('/api/group/' + this.group.id + '/invite', {
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
