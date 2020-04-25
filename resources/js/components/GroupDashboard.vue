<template>
	<div>
        <div>
            <h2 class="header" v-if="group">{{ group.name }}</h2>
            <router-link to="/group/manage" v-if="group.owner_id == user.id">
                <button class="small ui labeled icon button">
                    <i class="cog icon"></i>
                    Manage
                </button>
            </router-link>
        </div>
		<h5 class="header">Members</h5>
		<div class="ui bulleted list">
			<div class="item" v-for="member in members">
                <router-link :to="{ path: '/member', query: { member: member.id }}" >{{ member.name }}</router-link>
			</div>
		</div>
        <form @submit.prevent="submit">
            <div class="ui labeled input">
                <div class="ui label">Invite Member</div>
                <input name="name" type="text" v-model="email" required>
            </div>
            <button type="submit" class="ui primary button">Invite</button>
        </form>
        <div v-if="message" class="ui green message">
            {{ message }}
        </div>
	</div>
</template>

<script>
export default {
	data: function() {
		return {
			user: '',
			group: '',
            email: '',
			members: [],
            message: ''
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
	},
    methods: {
        submit() {
            axios.post('/api/group/' + this.group.id + '/invite', {
                email: this.email
            },  { withCredentials: true }).
            then(response => {
                // Display success message.
                this.message = 'User added to group.';

                // Empty email field.
                this.email = '';

                // Refresh members list.
                return axios.get('/api/group/' + this.group.id + '/members', { withCredentials: true });
            }).
            then(response => {
                this.members = response.data;
            }).
            catch(this.$root.errorHandler);
        }
    }
};
</script>
