<template>
    <v-card flat>
        <v-card-title>
            <span class="font-medium">{{ item?.id ? 'Edit' : 'Add' }} Banner Form</span>
        </v-card-title>

        <v-divider />

        <v-card-text class="pt-8">
            <v-form ref="formRef" v-model="isValid" lazy-validation>
                <v-row>
                    <!-- File Input -->
                    <v-col cols="12" md="12">
                        <div>
                            <v-file-input v-model="form.banner_image" label="Banner Image" variant="outlined"
                                prepend-icon="" prepend-inner-icon="mdi-image" accept="image/*" density="comfortable"
                                :error-messages="serverErrors.banner_image" />
                        </div>
                        <!-- Image Preview -->
                        <div v-if="previewUrl" class="mt-2">
                            <v-img :src="previewUrl" ccontain style="max-height: 300px;" class="rounded" />
                        </div>
                    </v-col>


                    <!-- Link -->
                    <v-col cols="12" md="12">
                        <v-text-field label="Link (URL)" density="comfortable" v-model="form.link" variant="outlined"
                            :error-messages="serverErrors.link" />
                    </v-col>

                    <!-- Start Date -->
                    <!-- End Date -->
                    <v-col cols="12" md="6">
                        <v-date-input label="Start Date" density="comfortable" prepend-icon=""
                            :max="form.end_date ? form.end_date : null" prepend-inner-icon="mdi-calendar"
                            v-model="form.start_date" variant="outlined" :error-messages="serverErrors.end_date" />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-date-input label="End Date" density="comfortable" prepend-icon=""
                            :min="form.start_date ? form.start_date : null" prepend-inner-icon="mdi-calendar"
                            v-model="form.end_date" variant="outlined" :error-messages="serverErrors.end_date" />
                    </v-col>
                </v-row>
            </v-form>
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
import { ref, reactive, onMounted, watch } from 'vue'
import { useSnackbar } from '@/composables/snackbar'
import axios from 'axios'
const { showSuccess, showError } = useSnackbar()

const emit = defineEmits(['close', 'saved'])
const props = defineProps({
    item: {
        type: Object,
        default: () => ({}),
    },
    banner: {
        type: Object,
        default: () => ({}),
    },
})

const formRef = ref(null)
const isValid = ref(false)
const loading = ref(false)
const previewUrl = ref(null)

const form = reactive({
    id: '',
    link: '',
    start_date: null,
    end_date: null,
    banner_image: null,
})

const serverErrors = reactive({})

const rules = {
    required: (v) => !!v || 'This field is required',
}

// Reset server-side errors when fields change
watch(() => form.name, () => (serverErrors.name = ''))
watch(() => form.link, () => (serverErrors.link = ''))
// watch(() => form.banner_image, () => (serverErrors.banner_image = ''))
watch(
    () => form.banner_image,
    (newFile, oldFile) => {
        if (previewUrl.value) {
            URL.revokeObjectURL(previewUrl.value)
        }
        if (newFile instanceof File || (Array.isArray(newFile) && newFile[0] instanceof File)) {
            const file = Array.isArray(newFile) ? newFile[0] : newFile
            previewUrl.value = URL.createObjectURL(file)
            serverErrors.banner_image = '';
        } else {
            previewUrl.value = null
        }
    }
)

function handleCancel() {
    formRef.value?.reset()
    emit('close')
}

async function submitForm() {

    if (!form.banner_image && !props.item?.id) {
        serverErrors.banner_image = "Must select image";
        return;
    };

    loading.value = true
    try {
        const formData = new FormData()
        if (props.item?.id) {
            formData.append('id', form.id || '')
        }
        formData.append('link', form.link || '')
        formData.append('link', form.link || '')
        formData.append('link', form.link || '')
        formData.append('start_date', formatDateToYMD(form.start_date) || '')
        formData.append('end_date', formatDateToYMD(form.end_date) || '')
        if (form.banner_image) {
            formData.append('image', form.banner_image)
        }

        let resp = await axios.post(`banners/${props.banner?.id}/images`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })

        showSuccess(resp?.data?.message || 'Banner saved successfully')
        emit('saved')
    } catch (error) {
        if (error.response?.status === 422) {
            Object.assign(serverErrors, error.response.data.errors || {})
        } else {
            showError(error?.response?.data?.message || 'Submission failed')
        }
    } finally {
        loading.value = false
    }
}
function formatDateToYMD(date) {
    return date ? new Date(date).toISOString().split('T')[0] : ''
}


onMounted(() => {
    if (props.item?.id) {
        Object.assign(form, {
            id: props.item.id || '',
            link: props.item.link || '',
            start_date: props.item.start_date || null,
            end_date: props.item.end_date || null,
        })
        if (props.item.image_url) {
            previewUrl.value = props.item.image_url;
        }
    }
})
</script>

<style scoped></style>
