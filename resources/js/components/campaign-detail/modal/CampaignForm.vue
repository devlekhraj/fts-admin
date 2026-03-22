<template>
    <v-card flat>
        <v-card-title>
            <span class="font-medium">{{ item?.id ? 'Edit' : 'Add' }} Banner Form</span>
        </v-card-title>

        <v-divider />

        <v-card-text class="pt-8">
            <v-form ref="formRef" v-model="isValid" lazy-validation>
                <!-- Title -->

                <!-- Slug -->


                <v-row>
                    <v-col cols="12">
                        <v-text-field label="Title" density="comfortable" v-model="form.title" :rules="[rules.required]"
                            variant="outlined" :error-messages="serverErrors.title"></v-text-field>

                    </v-col>
                    <v-col cols="12">

                        <v-text-field label="Slug" density="comfortable" v-model="form.slug" :rules="[rules.required]"
                            variant="outlined" :error-messages="serverErrors.slug"></v-text-field>
                    </v-col>
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

const emit = defineEmits(['close', 'saved'])
const props = defineProps({
    item: {
        type: Object,
        default: () => ({}),
    },
})

const formRef = ref(null)
const isValid = ref(false)
const loading = ref(false)
const { showSuccess, showError } = useSnackbar()

const form = reactive({
    title: '',
    slug: '',
    start_date: '',
    end_date: '',
})

const serverErrors = reactive({})

// Validation rules
const rules = {
    required: (v) => !!v || 'This field is required',
}

// Reset server-side error when user types
watch(
    () => ({ ...form }),
    () => Object.keys(serverErrors).forEach((key) => (serverErrors[key] = ''))
)

// Cancel and reset form
function handleCancel() {
    formRef.value?.reset()
    emit('close')
}

// Submit form (Create or Update)
async function submitForm() {
    const { valid } = await formRef.value.validate()
    if (!valid) return

    loading.value = true
    try {
        let resp = await axios.post('campaigns', form)

        showSuccess(resp?.data?.message || 'Campaign saved successfully')
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

// Load form data if editing
onMounted(() => {
    if (props.item?.id) {
        Object.assign(form, {
            id: props.item.id,
            title: props.item.title || '',
            slug: props.item.slug || '',
            start_date: props.item.start_date || '',
            end_date: props.item.end_date || '',
        })
    }
})
</script>

<style scoped></style>
