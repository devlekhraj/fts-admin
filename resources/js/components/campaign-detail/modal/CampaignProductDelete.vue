<template>
    <v-card flat>
        <v-divider />

        <v-card-text>
            <div class="py-4 text-center pt-5 pb-0 mb-0">
                <p class="text-error mb-2">Are you sure you want to delete this item?</p>
                <p class="font-medium text-primary">{{item.name}}</p>
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
import { ref, reactive, onMounted } from 'vue'
import { useSnackbar } from '@/composables/snackbar'

const emit = defineEmits(['close', 'saved'])
const props = defineProps({
    item: {
        type: Object,
        default: () => ({}),
    },
    productId: {
        require: true,
        type: Number
    }
})

const formRef = ref(null)
const isValid = ref(false)
const loading = ref(false)

const { showSuccess, showError } = useSnackbar()

const form = reactive({
    name: '',
    description: '',
    sort_order: 0,
    is_active: true,
})

const selectedFile = ref(null)
const previewImage = ref(null)
const serverErrors = reactive({})

const rules = {
    required: (v) => !!v || 'This field is required',
}

function handleImageUpload(file) {
    const selected = Array.isArray(file) ? file[0] : file
    if (selected instanceof File) {
        // Optional: Max size validation
        if (selected.size > 2 * 1024 * 1024) {
            serverErrors.image = ['Image must be less than 2MB']
            selectedFile.value = null
            previewImage.value = null
            return
        }

        selectedFile.value = selected

        const reader = new FileReader()
        reader.onload = (e) => {
            previewImage.value = e.target?.result
        }
        reader.readAsDataURL(selected)
    } else {
        selectedFile.value = null
        previewImage.value = null
    }
}

function handleCancel() {
    formRef.value?.reset()
    selectedFile.value = null
    previewImage.value = null
    emit('close')
}

async function submitForm() {
    loading.value = true
    try {

        const resp = await axios.delete(`campaign-products/${props.item.id}/remove`)
        showSuccess(resp?.data?.message || 'Item removed successfully')
        emit('saved')
    } catch (error) {
        console.log({error});
        if (error.response?.status === 422) {
            Object.assign(serverErrors, error.response.data.errors || {})
        } else {
            showError(error?.response?.data?.message || 'Submission failed')
        }
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    if (props.item?.id) {
        Object.assign(form, {
            id: props.item.id,
            name: props.item.name || '',
        })
    }
})

</script>

<style scoped></style>
