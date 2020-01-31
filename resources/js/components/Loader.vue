<template>
	<div v-if="isVisible" class="ui active dimmer">
		<div class="ui text loader">Loading</div>
	</div>
</template>

<script>
export default {
	name: 'Loader',
	props: {
		isVisible: { type: Boolean, required: true }
	}
};
</script>
