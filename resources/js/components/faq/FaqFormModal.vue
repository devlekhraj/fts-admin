<template>
  <v-card-text class="py-6">
    <v-form ref="formRef" @submit.prevent="onSubmit">
      <v-row>
        <v-col cols="12" class="pb-0">
          <v-textarea
            v-model="form.question"
            label="Question"
            variant="outlined"
            density="comfortable"
            auto-grow
            rows="2"
            :rules="[rules.required]"
          />
        </v-col>

        <v-col cols="12" class="pt-2">
          <div class="text-subtitle-2 mb-2">Answer</div>
          <RichText v-model="form.answer" :min-height="280" />
        </v-col>
      </v-row>
    </v-form>
  </v-card-text>

  <v-card-actions class="pb-4">
    <div class="w-100 d-flex align-center justify-end px-4 pt-2 ga-2">
      <v-btn variant="text" :disabled="loading" @click="emit('close')">Cancel</v-btn>
      <v-btn color="primary" variant="tonal" class="px-4" :disabled="loading" @click="onSubmit">
        <template v-if="!loading" #prepend>
          <v-icon>mdi-content-save-outline</v-icon>
        </template>
        <template v-else #prepend>
          <v-progress-circular indeterminate size="18" width="2" />
        </template>
        Save
      </v-btn>
    </div>
  </v-card-actions>
</template>

<script setup lang="ts">
import { defineAsyncComponent, reactive, ref } from 'vue';
import { saveFaq } from '@/api/faqs.api';

const RichText = defineAsyncComponent(() => import('@/components/RichText.vue'));

const props = defineProps<{
  type: string;
  type_id?: string | number | null;
  faq?: {
    id?: number | string;
    question?: string;
    answer?: string;
  } | null;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'saved', payload: { question: string; answer: string }): void;
}>();

const formRef = ref();
const loading = ref(false);

const form = reactive({
  question: props.faq?.question ? String(props.faq.question) : '',
  answer: props.faq?.answer ? String(props.faq.answer) : '',
});

const rules = {
  required: (value: unknown) => (String(value ?? '').trim() ? true : 'Required'),
};

async function onSubmit() {
  const { valid } = (await formRef.value?.validate?.()) ?? { valid: true };
  if (!valid) return;

  loading.value = true;
  try {
    const payload = {
      id: props.faq?.id ?? null,
      type: props.type,
      type_id: props.type_id ?? null,
      question: String(form.question ?? '').trim(),
      answer: String(form.answer ?? ''),
    };

    const response = await saveFaq(payload);
    emit('saved', response?.data ?? payload);
    emit('close');
  } finally {
    loading.value = false;
  }
}
</script>
