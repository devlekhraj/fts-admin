<template>
    <v-card-text>
        <div class="py-4 text-center pt-5 pb-0 mb-0">
            <v-icon color="error" size="64" class="mb-4">mdi-alert-circle-outline</v-icon>
            <h3 class="text-h6 font-weight-bold">Delete Campaign?</h3>
            <p class="text-body-2 text-medium-emphasis mt-2">
                Are you sure you want to delete <strong>{{ item?.title }}</strong>? This action cannot be undone.
            </p>
        </div>
    </v-card-text>

    <v-card-actions class="pa-4 pt-0">
        <v-btn variant="text" @click="handleCancel" :disabled="loading">Cancel</v-btn>
        <v-spacer></v-spacer>
        <v-btn color="error" variant="flat" :loading="loading" :disabled="loading" @click="submitForm">
            Delete
        </v-btn>
    </v-card-actions>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { remove } from '@/api/campaigns.api'
import { useSnackbarStore } from '@/stores/snackbar.store'
import { Campaign } from '@/types/models'

const emit = defineEmits(['close', 'saved'])
const props = defineProps<{
    item: Campaign
}>()

const loading = ref(false)
const snackbar = useSnackbarStore()

function handleCancel() {
    emit('close')
}

async function submitForm() {
    if (!props.item?.id) return

    loading.value = true
    try {
        const resp = await remove(String(props.item.id))
        snackbar.show({
            message: resp?.data?.message || 'Campaign deleted successfully',
        });
        emit('close');
        emit('saved')
    } catch (error) {
        snackbar.show({
            message: 'Deletion failed',
            color: 'error'
        })
    } finally {
        loading.value = false
    }
}
</script>

<style scoped></style>
