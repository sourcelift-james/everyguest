<template>
    <div>
        <div>
            <h2 class="header" v-if="group">Create Space</h2>
            <div v-if="message" class="ui green message">
                {{ message }}
            </div>
            <form @submit.prevent="submit" class="ui form">
                <div class="field">
                    <label>Space Name</label>
                    <input name="name" type="text" v-model="name" required>
                </div>
                <div class="field">
                    <label>Capacity</label>
                    <input name="capacity" type="number" v-model="capacity" required>
                </div>
                <div class="field">
                    <label>Accommodations</label>
                    <textarea name="accommodations" v-model="accommodations" placeholder="Describe any accommodations specific to this location."></textarea>
                </div>
                <div class="field">
                    <label>Notes</label>
                    <textarea name="notes" v-model="notes" placeholder="Include any additional notes related to this location that may be useful for reservations in the future."></textarea>
                </div>
                <button type="submit" class="ui primary button">Submit</button>
            </form>
        </div>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                user: '',
                group: '',
                name: '',
                capacity: '',
                accommodations: '',
                notes: '',
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

                return axios.get('/api/spaces', { withCredentials: true });
            }).
            then(response => {
                this.spaces = response.data;
            }).
            catch(this.$root.errorHandler);
        },
        methods: {
            submit() {
                axios.post('/api/spaces/create', {
                    name: this.name,
                    capacity: this.capacity,
                    accommodations: this.accommodations,
                    notes: this.notes
                },  { withCredentials: true }).
                then(response => {
                    this.message = response.data;
                }).
                catch(this.$root.errorHandler);
            }
        }
    };
</script>
