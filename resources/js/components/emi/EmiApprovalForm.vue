<template>
    <v-card flat>
        <v-card-title class="d-flex justify-space-between align-center py-1">
            <span class="font-semibold">Bank Request Form</span>
            <v-spacer></v-spacer>
            <v-btn icon="mdi-close" size="small" color="error" class="cursor-pointer" variant="text" aria-label="Close"
                @click="$emit('close')">
                <v-icon>mdi-close</v-icon>
            </v-btn>
        </v-card-title>

        <v-divider></v-divider>

        <v-card-text>
            <v-row>
                <v-col cols="12">
                    <v-text-field v-model="subject" label="Subject" variant="outlined" density="comfortable" />
                </v-col>
                <v-col cols="12" md="12">
                    <v-text-field v-model="to" label="To" variant="outlined" density="comfortable" />
                </v-col>
                <v-col cols="12" md="12">
                    <v-text-field v-model="cc" label="CC" variant="outlined" density="comfortable" />
                </v-col>
                <v-col cols="12" md="12">
                    <v-text-field v-model="bcc" label="BCC" variant="outlined" density="comfortable" />
                </v-col>

                <v-col cols="12">
                    <v-text-field v-model="attachmentFileName" label="Attachment File Name" variant="outlined"
                        density="comfortable" readonly />
                </v-col>
            </v-row>
        </v-card-text>
        <!-- <v-divider></v-divider> -->
        <v-card-actions class="d-flex justify-space-around">
            <div class="py-2">
                <v-btn color="primary" :loading="loading" variant="tonal" prepend-icon="mdi-check-decagram-outline"
                    @click="handleSubmit">
                    Approve & Send Email
                </v-btn>
            </div>
        </v-card-actions>

    </v-card>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { approveApplication } from '@/api/emi-requests.api';

const props = withDefaults(
    defineProps<{ id?: string | number; data?: Record<string, any> | null; application?: Record<string, any> | null }>(),
    { data: null, application: null },
);
const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved', payload: Record<string, any>): void;
}>();

const subject = ref('');
const to = ref('');
const cc = ref('');
const bcc = ref('');
const attachmentFileName = ref('');
const loading = ref(false);
const sourceData = computed(() => props.data ?? props.application ?? null);

function getFileName(path: string): string {
    if (!path) return '';
    const normalizedPath = path.split('?')[0] ?? '';
    const parts = normalizedPath.split('/');
    return parts[parts.length - 1] ?? '';
}

function fillFormValues() {
    const data = sourceData.value ?? {};

    const bankName = String(data.bank ?? data.bank_name ?? 'Bank');
    subject.value = `EMI Application - ${bankName}`;
    to.value = String(data.email ?? data.user?.email ?? '');
    cc.value = '';
    bcc.value = '';
    attachmentFileName.value = getFileName(String(data.path ?? ''));

}

async function handleSubmit() {
    const data = sourceData.value ?? {};
    const payload = {
        request_id: props.id ?? null,
        subject: subject.value,
        to: to.value,
        cc: cc.value,
        bcc: bcc.value,
        file_name: attachmentFileName.value,
        application_id: data.id ?? null,
    };

    if (!payload.application_id) return;

    loading.value = true;
    try {
        const { data: response } = await approveApplication(payload.application_id, payload);
        emit('saved', response ?? payload);
        emit('close');
    } catch (error) {
        console.error('Failed to approve application:', error);
    } finally {
        loading.value = false;
    }
}

watch(sourceData, fillFormValues, { immediate: true });
</script>
