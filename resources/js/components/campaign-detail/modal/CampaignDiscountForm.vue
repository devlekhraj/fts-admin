<template>
    <v-card flat>
        <v-card-title class="d-flex align-center justify-space-between">
            <span class="font-medium">Update Products</span>
            <v-btn variant="text" icon @click="handleCancel"><v-icon>mdi-close</v-icon></v-btn>
        </v-card-title>

        <v-divider />

        <v-card-text class="pt-8">
            <!-- Select All Checkbox -->
            <template v-if="fetching_data">
                <v-col cols="12" md="6" v-for="i in 8" :key="'skeleton-' + i">
                    <v-skeleton-loader type="list-item-avatar-two-line" class="rounded-lg" />
                </v-col>
            </template>
            <template v-else>

                <v-form ref="formRef" lazy-validation>


                    <div class="py-5">
                        <v-row>
                            <v-col cols="12" md="6" offset-md="3">

                                <div class="mb-5">
                                    <v-select variant="outlined" :items="discount_types" item-value="id" clearable
                                        label="Discount Type" v-model="form.discount_type" :rules="[rules.required]"
                                        :error-messages="serverErrors.discount_type" density="comfortable"
                                        item-title="name" prepend-inner-icon="mdi-tag"></v-select>
                                </div>

                                <div>
                                    <v-text-field type="number" label="Discount Value" density="comfortable"
                                        v-model="form.discount_value" variant="outlined" :rules="[rules.required]"
                                        :error-messages="serverErrors.discount_value"
                                        prepend-inner-icon="mdi-currency-usd"></v-text-field>
                                </div>

                            </v-col>
                        </v-row>
                    </div>

                    <div class="text-center">
                        <v-btn color="primary" :loading="loading" :disabled="loading" size="large" @click="submitForm">
                            <v-icon>mdi-check-outline</v-icon>
                            &nbsp; Update Discount
                        </v-btn>
                    </div>


                </v-form>
            </template>

        </v-card-text>

        <!-- <v-divider></v-divider>
        <v-card-actions class="mt-0 pt-0">
            <v-btn variant="text" @click="handleCancel">No</v-btn>
            <v-spacer></v-spacer>
            <v-btn color="primary" :loading="loading" :disabled="loading" @click="submitForm">
                Add Products
            </v-btn>
        </v-card-actions> -->
    </v-card>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { useSnackbar } from '@/composables/snackbar'



const emit = defineEmits(['close', 'saved'])
const props = defineProps({
    campaign: {
        type: Object,
        default: () => ({}),
    },
})

const formRef = ref(null)
const isValid = ref(false)
const loading = ref(false)
const fetching_data = ref(false);

const { showSuccess, showError } = useSnackbar()

const form = reactive({
    id:null,
    discount_type: null,        // number or null
    discount_value: null,       // number or null
})
const discount_types = [
    { id: 2, name: 'Percentage' },
    { id: 1, name: 'Fixed Amount' },
]


const serverErrors = reactive({})

// Validation rules
const rules = {
    required: (v) => !!v || 'This field is required',
}


// Cancel and reset form
function handleCancel() {
    formRef.value?.reset()
    emit('close')
}

// Submit form (Create or Update)
async function submitForm() {


    // Clear previous errors
    Object.keys(serverErrors).forEach(key => delete serverErrors[key])

    const { valid } = await formRef.value.validate()
    if (!valid) {
        loading.value = false
        return
    }

    loading.value = true

    try {
        let resp = await axios.put(`campaigns/${props.campaign.id}/update-discount`, form)

        showSuccess(resp?.data?.message || 'Products assigned successfully')
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



// Load form data if editing
onMounted(() => {
    if (props.campaign?.id) {
        Object.assign(form, {
            id: props.campaign.id,
        })
    }
})

</script>

<style scoped></style>
