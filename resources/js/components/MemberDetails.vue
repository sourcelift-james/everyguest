<template>
    <div>
        <h2 class="header">Member Details</h2>
        <h5 class="header">{{ member.name }}</h5>
        <p>{{ member.email }}</p>
        <div v-if="group.owner_id == user.id">
            <form @submit.prevent="submit">
                <button type="submit" class="ui red button">Remove Member</button>
            </form>
            <div v-if="message" class="ui green message">
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
                group: '',
                member: '',
                message: '',
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

                return axios.get('/api/group/' + this.group.id + '/members/' + this.$route.query.member, { withCredentials: true });
            }).
            then(response => {
                this.member = response.data;
            }).
            catch(this.$root.errorHandler);
        },
        methods: {
            submit() {
                axios.post('/api/group/' + this.group.id + '/members/' + this.member.id + '/remove', {},  { withCredentials: true }).
                then(response => {
                    // Display success message.
                    this.$router.push('/group');
                }).
                catch(this.$root.errorHandler);
            }
        }
    };
</script>
