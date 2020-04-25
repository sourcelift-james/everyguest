<template>
    <div>
        <div>
            <h2 class="header" v-if="group">Spaces</h2>
            <router-link to="/spaces/create" v-if="group.owner_id == user.id">
                <button class="small ui labeled icon button">
                    <i class="plus icon"></i>
                    Create Space
                </button>
            </router-link>
            <div class="ui relaxed divided list">
                <div class="item" v-for="space in spaces">
                    <i class="marker icon"></i>
                    <div class="content">
                        <router-link :to="{ path: '/space', query: { space: space.id }}" class="header">{{ space.name }}</router-link>
                        <div class="description">Capacity: {{ space.capacity }}</div>
                    </div>
                </div>
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
                spaces: []
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

                return axios.get('/api/spaces', { withCredentials: true });
            }).
            then(response => {
                this.spaces = response.data;
            }).
            catch(this.$root.errorHandler);
        }
    };
</script>
