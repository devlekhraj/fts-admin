<template>
	<v-snackbar v-model="snackbar.visible" :color="snackbar.color" :timeout="snackbar.timeout"
		:location="snackbar.location">
		{{ snackbar.message }}
	</v-snackbar>
</template>

<script setup lang="ts">
import { useSnackbarStore } from '@/stores/snackbar.store';

const snackbar = useSnackbarStore();
</script>
