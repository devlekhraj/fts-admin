<template>
    <v-card flat>
        <v-divider />

        <v-card-text>
            <div class="py-4 text-center pt-5 pb-0 mb-0">
                <p class="text-error mb-2">Are you sure you want to delete this item?</p>
                <p class="font-medium text-primary">{{ item.name }}</p>
                <div class="mt-4">
                    <v-img :src="item.thumb?.url" style="max-height: 100px;"></v-img>
                </div>
            </div>
        </v-card-text>

        <v-card-actions class="mt-0 pt-0">
            <v-btn variant="text" @click="handleCancel">No</v-btn>
            <v-spacer></v-spacer>
            <v-btn color="primary" :loading="loading" :disabled="loading" @click="submitForm">
                Yes
            </v-btn>
        </v-card-actions>
    </v-card>
</template>

<script setup>
import { ref } from 'vue'
import { useSnackbar } from '@/composables/snackbar'
import { removeCampaignProduct } from '@/api/campaigns.api'

const emit = defineEmits(['close', 'saved'])
const props = defineProps({
    item: {
        type: Object,
        default: () => ({}),
    },
})

const loading = ref(false)
const { showSuccess, showError } = useSnackbar()

function handleCancel() {
    emit('close')
}

async function submitForm() {
    loading.value = true
    try {
        const resp = await removeCampaignProduct(props.item.id)
        showSuccess(resp?.message || 'Item removed successfully');
        emit('close');
        emit('saved');
    } catch (error) {
        showError(error?.response?.data?.message || 'Submission failed')
    } finally {
        loading.value = false
    }
}

</script>

<style scoped></style>
