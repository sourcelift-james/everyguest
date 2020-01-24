<template>
	<div class="ui basic segment">
		<div class="ui cards">
			<div class="card">
				<div class="content">
					<div class="header">About</div>
					<div class="description">
						<p v-if="user">{{ user }}</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	data() {
		return {
			user: null
		}
	},

	mounted() {
		axios.get('/api/about', { withCredentials: true })
			.then(response => {
				console.log(response.data);
				this.user = response.data;
			})
	}
};
</script>
