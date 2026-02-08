<template>
    <v-card flat>
        <v-card-title class="d-flex justify-space-between align-center py-1">
            <span class="font-semibold">Bank Application Form</span>
            <v-spacer></v-spacer>
            <v-btn icon="mdi-close" size="small" color="error" class="cursor-pointer" variant="text" aria-label="Close"
                @click="$emit('close')">
                <v-icon>mdi-close</v-icon>
            </v-btn>
        </v-card-title>

        <v-divider></v-divider>

        <v-card-text>
            <div class="text-subtitle-2 mb-2">EMI Summary</div>
            <div class="text-body-2">This is a demo modal content.</div>
            <div class="text-caption text-medium-emphasis mt-2">Application ID: {{ id }}</div>

            <v-form ref="formRef" @submit.prevent="onSave">
                <v-row class="mt-4">
                    <v-col cols="12" md="4">
                        <v-text-field v-model="form.name" label="Name" variant="outlined" :rules="[requiredRule]" />
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-text-field v-model="form.email" label="Email" variant="outlined"
                            :rules="[requiredRule, emailRule]" />
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-text-field v-model="form.phone" label="Phone" variant="outlined" :rules="[requiredRule]" />
                    </v-col>
                </v-row>
            </v-form>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="justify-end">
            <v-btn variant="text" @click="$emit('close')">Cancel</v-btn>
            <v-btn variant="tonal" color="primary" :loading="loading" @click="onSave">Save</v-btn>
        </v-card-actions>
    </v-card>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';

const props = defineProps<{ id?: string | number }>();
const emit = defineEmits<{ (e: 'saved', payload: Record<string, unknown>): void; (e: 'close'): void }>();

const formRef = ref();
const loading = ref(false);
const form = reactive({
    name: '',
    email: '',
    phone: '',
});

const requiredRule = (v: string) => Boolean(v) || 'Required';
const emailRule = (v: string) => /.+@.+\..+/.test(v) || 'Invalid email';

async function onSave() {
    const { valid } = await formRef.value?.validate();
    if (!valid) return;

    emit('saved', { id: props.id, ...form });

    loading.value = true;
    setTimeout(() => {
        loading.value = false;
        emit('close');
    }, 2000);
}
</script>
