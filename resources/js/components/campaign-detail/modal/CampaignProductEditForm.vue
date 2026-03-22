<template>
    <v-card flat>
        <v-card-title class="d-flex align-center justify-space-between">
            <span class="font-medium">Edit Product Discount</span>
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

                <v-form ref="formRef" v-model="isValid" lazy-validation>

                    <div>
                        <div class="d-flex align-center">
                            <div>
                                <v-img :src="item.thumb?.url" alt="" height="100" width="100" class="me-2"
                                    contain></v-img>
                            </div>
                            <div class="pl-3">
                                <p style="font-weight: 600;" class="text-primary">
                                    {{ item.name }}
                                </p>
                                <p class="text-decoration-line-through text-muted">{{ formatAmount(item.price?.original_price) }}</p>
                                <p class="text-primary font-medium">{{ formatAmount(item.price?.discounted_price) }}</p>
                            </div>
                        </div>
                        <div>
                            <div class="mt-6">
                                <v-row>
                                    <v-col cols="12" md="6">
                                        <div>
                                            <v-select variant="outlined" :items="discount_types" item-value="id"
                                                clearable label="Discount Type" v-model="form.discount_type"
                                                :rules="[rules.required]" :error-messages="serverErrors.discount_type"
                                                density="comfortable" item-title="name"
                                                prepend-inner-icon="mdi-tag"></v-select>
                                        </div>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <div>
                                            <div class="mb-4">
                                                <v-text-field type="number" label="Discount Value" density="comfortable"
                                                    v-model="form.discount_value" variant="outlined"
                                                    :rules="[rules.required]"
                                                    :error-messages="serverErrors.discount_value"
                                                    prepend-inner-icon="mdi-currency-usd"></v-text-field>
                                            </div>
                                        </div>
                                    </v-col>
                                </v-row>
                            </div>

                            <div class="text-center">
                                <v-btn color="primary" :loading="loading" :disabled="loading" size="large"
                                    @click="submitForm">
                                    <v-icon>mdi-check-outline</v-icon>
                                    Assign Products
                                </v-btn>
                            </div>
                        </div>


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
import { formatAmount } from '@/utils/format'


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
const fetching_data = ref(false);

const { showSuccess, showError } = useSnackbar()

const form = reactive({
    discount_type: null,        // number or null
    discount_value: null,       // number or null
    product_ids: [],            // empty array
    id: null,                   // number or null
    product_id: null,           // number or null
    name: '',                   // empty string
    price: null,                // number or null
    original_price: null,       // number or null
    preview_image: '',          // empty string
    discount_price: null,       // number or null
    categories: [],
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


        let resp = await axios.put(`campaign-products/${props.item.id}/update`, form)

        showSuccess(resp?.data?.message || 'Products assigned successfully')
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
        Object.assign(form, props.item)
    }
})

</script>

<style scoped></style>
