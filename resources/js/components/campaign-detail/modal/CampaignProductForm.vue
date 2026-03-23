<template>
    <v-card-text class="pt-8">
        <!-- Select All Checkbox -->
        <template v-if="fetching_data">
            <v-col cols="12" md="6" v-for="i in 8" :key="'skeleton-' + i">
                <v-skeleton-loader type="list-item-avatar-two-line" class="rounded-lg" />
            </v-col>
        </template>
        <template v-else>
            <v-form ref="formRef" v-model="isValid" lazy-validation>


                <v-row>
                    <v-col cols="12" md="3">
                        <v-text-field label="Product Name" density="compact" v-model="form.name" clearable hide-details
                            variant="outlined" :error-messages="serverErrors.title"></v-text-field>

                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select variant="outlined" :items="product_categories" class="w-100" item-value="id" clearable
                            label="Categories" v-model="form.category_id" hide-details density="compact"
                            item-title="title"></v-select>

                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select variant="outlined" :items="product_brands" class="w-100" item-value="id" clearable
                            label="Brands" v-model="form.brand_id" hide-details density="compact"
                            item-title="name"></v-select>

                    </v-col>
                    <v-col cols="12" md="3">
                        <v-btn color="primary" @click="fetchProducts()" variant="flat"> <v-icon>mdi-magnify</v-icon>
                            Search</v-btn>
                    </v-col>

                </v-row>
                <div class="mt-4">
                    <v-row>
                        <v-col cols="12" md="9" lg="9">
                            <div>
                                <div>
                                    <v-row v-if="product_list.length">
                                        <v-col cols="12">
                                            <v-checkbox v-model="selectAll" label="Select All" hide-details
                                                @change="toggleSelectAll"></v-checkbox>
                                        </v-col>
                                    </v-row>
                                </div>
                                <div>
                                    <v-row>

                                        <v-col cols="12" md="6" v-for="(product, index) in product_list"
                                            :key="product.id">
                                            <v-checkbox color="primary" v-model="selectedProducts" :value="product.id"
                                                hide-details>
                                                <template #label>
                                                    <div class="d-flex align-center">
                                                        <v-img
                                                            :src="product.thumb || product.preview_image || product.image"
                                                            alt="" height="60" width="60" class="me-2"></v-img>
                                                        <div>
                                                            <p class="mb-1"
                                                                style="font-weight: 500; font-size: 0.875rem;">{{
                                                                product.name }}
                                                            </p>
                                                            <p class="text-primary mb-0" style="font-size: 0.875rem;">{{
                                                                formatAmount(product.price)
                                                                }}</p>
                                                        </div>
                                                    </div>
                                                </template>
                                            </v-checkbox>
                                        </v-col>

                                    </v-row>
                                    <div class="text-center py-4" v-if="pagination.current_page < pagination.last_page">
                                        <v-btn color="primary" :loading="loading" variant="flat"
                                            @click="fetchProducts(pagination.current_page + 1)">
                                            <v-icon>mdi-refresh</v-icon> load more</v-btn>
                                    </div>
                                </div>
                            </div>
                        </v-col>
                        <v-col cols="12" md="3" lg="3" v-if="!fetching_data">
                            <div>
                                <div class="mb-4">
                                    <p class="font-medium text-primary">Selected: {{
                                        selectedProducts.length }} Products</p>
                                </div>
                                <div class="mb-3">
                                    <v-select variant="outlined" :items="discount_types" class="w-100" item-value="id"
                                        clearable label="Discount Type" v-model="form.discount_type"
                                        :rules="[rules.required]" :error-messages="serverErrors.discount_type"
                                        density="compact" item-title="name" prepend-inner-icon="mdi-tag"></v-select>
                                </div>

                                <div class="mb-4">
                                    <v-text-field type="number" label="Discount Value" density="compact"
                                        v-model="form.discount_value" variant="outlined" :rules="[rules.required]"
                                        :error-messages="serverErrors.discount_value"
                                        prepend-inner-icon="mdi-currency-usd"></v-text-field>
                                </div>
                                <v-btn color="primary" block :loading="loading" :disabled="loading"
                                    @click="submitForm" variant="flat">
                                    <v-icon>mdi-check-outline</v-icon>
                                    Assign Products
                                </v-btn>

                            </div>
                        </v-col>
                    </v-row>
                </div>

            </v-form>
        </template>

    </v-card-text>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { formatAmount } from '@/shared/utils'
import { getProductCategories, getProductBrands, getProducts, assignProducts } from '@/api/campaigns.api'

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
const product_categories = ref([]);
const product_brands = ref([]);
const product_list = ref([]);
const selectedProducts = ref([])

// const { showSuccess, showError } = useSnackbar()

const form = reactive({
    discount_type: 2,
    discount_value: null,
    product_ids: [],
})
const discount_types = [
    { id: 2, name: 'Percentage' },
    { id: 1, name: 'Fixed Amount' },
]

// store pagination info here
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    per_page: 30,
    total: 0,
    from: 0,
    to: 0,
})

const selectAll = ref(false)

const toggleSelectAll = () => {
    if (selectAll.value) {
        // Select all product IDs
        selectedProducts.value = product_list.value.map(p => p.id)
    } else {
        // Deselect all
        selectedProducts.value = []
    }
}

// Watch product_list to reset selectAll when products change
watch(product_list, (newList) => {
    if (!newList.length) {
        selectAll.value = false
    } else if (selectedProducts.value.length === newList.length) {
        selectAll.value = true
    } else {
        selectAll.value = false
    }
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


    // Clear previous errors
    Object.keys(serverErrors).forEach(key => delete serverErrors[key])
    if (selectedProducts.value.length === 0) {
        // showError('Please select at least one product.')
        loading.value = false
        return
    }
    const { valid } = await formRef.value.validate()
    if (!valid) {
        loading.value = false
        return
    }

    loading.value = true
    console.log(form);
    console.log(selectedProducts.value);
    try {
        // let form = {
        //     product_ids: selectedProducts.value
        // }
        form.product_ids = selectedProducts.value;

        let resp = await assignProducts(props.item.id, form)

        fetchProducts();
         emit('close');
        // showSuccess(resp?.data?.message || 'Products assigned successfully')
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

const fetchProductCategories = async () => {

    try {


        const resp = await getProductCategories()

        product_categories.value = resp.sort((a, b) => {
            return a.title.localeCompare(b.title)
        })

        console.log(product_categories.value);

    } catch (error) {
        console.error('Error fetching products:', error)
    } finally {
        fetching_data.value = false
    }
}
const fetchProductBrands = async () => {

    try {


        const resp = await getProductBrands()
        product_brands.value = resp.sort((a, b) => {
            return a.name.localeCompare(b.name)
        })

    } catch (error) {
        console.error('Error fetching products:', error)
    } finally {
        fetching_data.value = false
    }
}
const fetchProducts = async (page = 1) => {

    try {
        if (product_list.value.length === 0) {
            fetching_data.value = true
        } else {
            loading.value = true;
        }

        const resp = await getProducts({
            campaign_id: props.item.id,
            name: form.name,
            category_id: form.category_id,
            brand_id: form.brand_id,
            page: page, // send page to backend
            per_page: pagination.per_page, // optional
        })
        fetching_data.value = false;
        loading.value = false;
        if (page === 1) {
            // first load → replace
            product_list.value = resp.data
        } else {
            // subsequent pages → append
            product_list.value = [...product_list.value, ...resp.data]
        }

        Object.assign(pagination, resp.meta)

    } catch (error) {
        console.error('Error fetching products:', error)
    } finally {
        fetching_data.value = false
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
    fetchProductCategories();
    fetchProductBrands();
    fetchProducts();
})
</script>

<style scoped></style>
