<template>
    <v-card-text class="pt-8">
        <v-form ref="formRef" v-model="isValid" lazy-validation>
            <v-row>
                <v-col cols="12">
                    <v-text-field label="Title" density="comfortable" v-model="form.title" :rules="[rules.required]"
                        variant="outlined" :error-messages="serverErrors.title"></v-text-field>
                </v-col>
                <v-col cols="12">
                    <v-text-field label="Slug" density="comfortable" v-model="form.slug" :rules="[rules.required]"
                        variant="outlined" :error-messages="serverErrors.slug" @input="onSlugInput"></v-text-field>
                </v-col>
                <v-col cols="12" md="4">
                    <v-date-input label="Start Date" density="comfortable" prepend-icon=""
                        :max="form.end_date ? form.end_date : null" prepend-inner-icon="mdi-calendar"
                        v-model="form.start_date" variant="outlined" :error-messages="serverErrors.end_date" />
                </v-col>
                <v-col cols="12" md="4">
                    <v-date-input label="End Date" density="comfortable" prepend-icon=""
                        :min="form.start_date ? form.start_date : null" prepend-inner-icon="mdi-calendar"
                        v-model="form.end_date" variant="outlined" :error-messages="serverErrors.end_date" />
                </v-col>
                <v-col cols="12" md="4">
                    <v-select label="Published" v-model="form.is_published"
                        :items="[{ title: 'Yes', value: 1 }, { title: 'No', value: 0 }]" variant="outlined"
                        density="comfortable" hide-details></v-select>
                </v-col>
            </v-row>
        </v-form>
    </v-card-text>

    <v-card-actions class="mt-0 pt-0">
        <v-btn variant="text" @click="handleCancel">No</v-btn>
        <v-spacer></v-spacer>
        <v-btn color="primary" :loading="loading" :disabled="loading" @click="submitForm">
            Save
        </v-btn>
    </v-card-actions>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue'
import { create, update } from '@/api/campaigns.api'
import { useSnackbarStore } from '@/stores/snackbar.store'
import type { Campaign } from '@/types/models'

const emit = defineEmits(['close', 'saved'])
const props = defineProps({
    item: {
        type: Object,
        default: () => ({}),
    },
})

const formRef = ref<any>(null)
const isValid = ref(false)
const loading = ref(false)
const slugEdited = ref(false)
const snackbar = useSnackbarStore()

interface CampaignForm {
    id: number | string | null;
    title: string;
    slug: string;
    start_date: Date | null;
    end_date: Date | null;
    is_published: number;
}

const form = reactive<CampaignForm>({
    id: null,
    title: '',
    slug: '',
    start_date: null,
    end_date: null,
    is_published: 0,
})

const serverErrors = reactive<Record<string, any>>({})

// Validation rules
const rules = {
    required: (v: any) => !!v || 'This field is required',
}

// Reset server-side error when user types
watch(
    () => ({ ...form }),
    () => Object.keys(serverErrors).forEach((key) => (serverErrors[key] = ''))
)

// Auto-generate slug from title
watch(
    () => form.title,
    (val) => {
        if (!slugEdited.value && val) {
            form.slug = val.toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/[^a-z0-9-]/gi, '')
        }
    }
)

function onSlugInput() {
    slugEdited.value = true
    form.slug = form.slug.toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^a-z0-9-]/gi, '')
}

// Cancel and reset form
function handleCancel() {
    formRef.value?.reset()
    emit('close')
}

async function submitForm() {
    if (!formRef.value) return

    const { valid } = await formRef.value.validate()
    if (!valid) return

    loading.value = true
    try {
        let resp;
        const payload = { ...form }
        
        if (form.id) {
            resp = await update(form.id, payload)
        } else {
            resp = await create(payload)
        }

        const savedData = (resp as any)?.data?.data ?? (resp as any)?.data ?? payload;

        snackbar.show({
            message: resp?.data?.message || 'Campaign saved successfully',
            color: 'success'
        })
        emit('saved', savedData)
        emit('close')
    } catch (error: any) {
        if (error.response?.status === 422) {
            Object.assign(serverErrors, error.response.data.errors || {})
        } else {
            snackbar.show({
                message: error?.response?.data?.message || 'Submission failed',
                color: 'error'
            })
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
            start_date: props.item.start_date ? new Date(props.item.start_date) : null,
            end_date: props.item.end_date ? new Date(props.item.end_date) : null,
            is_published: props.item.is_published ? 1 : 0,
        })
        slugEdited.value = true
    }
})
</script>

<style scoped></style>
