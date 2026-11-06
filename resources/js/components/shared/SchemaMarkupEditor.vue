<template>
  <div>
    <div class="mb-1 text-subtitle-1 font-weight-medium">Schema (structured data)</div>
    <div class="text-body-2 text-medium-emphasis mb-4">{{ hint }}</div>

    <div class="mb-4">
      <div class="d-flex align-center justify-space-between mb-2">
        <app-field-label label="FAQ" />
        <v-btn size="small" variant="tonal" :disabled="form.faqs.length >= 30" @click="addFaq">
          <v-icon start size="16">mdi-plus</v-icon>
          Add question
        </v-btn>
      </div>
      <div v-if="!form.faqs.length" class="text-body-2 text-medium-emphasis">
        No questions yet. Add the questions and answers shown on this page to get FAQ rich results.
      </div>
      <v-card v-for="(faq, index) in form.faqs" :key="index" variant="outlined" class="pa-3 mb-3">
        <div class="d-flex align-start ga-2">
          <div class="flex-grow-1">
            <v-text-field
              v-model="faq.question"
              variant="outlined"
              density="comfortable"
              label="Question"
              :rules="[requiredRule]"
              maxlength="300"
              class="mb-2"
            />
            <v-textarea
              v-model="faq.answer"
              variant="outlined"
              density="comfortable"
              label="Answer"
              rows="2"
              auto-grow
              :rules="[requiredRule]"
              maxlength="3000"
            />
          </div>
          <v-btn icon variant="text" size="small" aria-label="Remove question" @click="removeFaq(index)">
            <v-icon size="18">mdi-delete-outline</v-icon>
          </v-btn>
        </div>
      </v-card>
    </div>

    <div class="mb-4">
      <app-field-label label="Custom schema (JSON-LD)" />
      <v-textarea
        v-model="form.customSchema"
        variant="outlined"
        density="comfortable"
        rows="6"
        auto-grow
        persistent-placeholder
        :placeholder="customPlaceholder"
        :rules="[jsonRule]"
        hint="Paste only the JSON, without script tags. Leave empty if not needed."
        persistent-hint
      />
    </div>
  </div>
</template>

<script lang="ts">
export type SchemaForm = {
  faqs: { question: string; answer: string }[];
  customSchema: string;
};

export type SchemaMarkupValue = {
  faqs?: { question: string; answer: string }[];
  custom?: string | null;
} | null | undefined;

export function emptySchemaForm(): SchemaForm {
  return { faqs: [], customSchema: '' };
}

export function schemaToForm(schema: SchemaMarkupValue, form: SchemaForm) {
  form.faqs = Array.isArray(schema?.faqs)
    ? schema.faqs.map((f) => ({ question: String(f.question ?? ''), answer: String(f.answer ?? '') }))
    : [];
  form.customSchema = schema?.custom ? String(schema.custom) : '';
}

export function schemaFromForm(form: SchemaForm) {
  return {
    faqs: form.faqs.map((f) => ({ question: f.question.trim(), answer: f.answer.trim() })),
    custom: form.customSchema.trim() || null,
  };
}
</script>

<script setup lang="ts">
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';

const props = defineProps<{
  form: SchemaForm;
  hint?: string;
}>();

const customPlaceholder = '{"@context": "https://schema.org", "@type": "HowTo"}';

function addFaq() {
  props.form.faqs.push({ question: '', answer: '' });
}

function removeFaq(index: number) {
  props.form.faqs.splice(index, 1);
}

const requiredRule = (v: string) => !!String(v ?? '').trim() || 'Required';

function jsonRule(v: string) {
  const text = String(v ?? '').trim();
  if (!text) return true;
  try {
    const parsed = JSON.parse(text);
    return (parsed && typeof parsed === 'object') || 'Custom schema must be a JSON object or list';
  } catch {
    return 'Custom schema must be valid JSON';
  }
}
</script>
