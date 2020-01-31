<template>
	<div v-if="this.$root.errorMessage" class="ui message red">
		{{ this.$root.errorMessage }}
	</div>
</template>

<script>
export default {};
</script>
