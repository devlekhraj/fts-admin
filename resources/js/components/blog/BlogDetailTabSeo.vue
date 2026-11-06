<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex align-center justify-space-between mb-6">
          <div>
            <div class="text-h6">SEO Settings</div>
            <div class="text-body-2 text-medium-emphasis">Optimize this blog for search engines.</div>
          </div>
          <v-btn color="primary" variant="flat" :loading="saving" @click="onUpdate">
            <v-icon start size="16">mdi-content-save-outline</v-icon>
            Update
          </v-btn>
        </div>

        <v-form ref="seoFormRef">
          <div class="mb-4">
            <app-field-label label="Meta Title" />
            <v-textarea
              v-model="form.meta_title"
              variant="outlined"
              density="comfortable"
              rows="2"
              auto-grow
              persistent-placeholder
              placeholder="Enter SEO title"
            />
          </div>

          <div class="mb-4">
            <app-field-label label="Meta Keywords" />
            <v-textarea
              v-model="form.meta_keywords"
              variant="outlined"
              density="comfortable"
              rows="2"
              auto-grow
              persistent-placeholder
              placeholder="e.g. blog, tech, news"
            />
          </div>

          <div class="mb-4">
            <app-field-label label="Meta Description" />
            <v-textarea
              v-model="form.meta_description"
              variant="outlined"
              density="comfortable"
              rows="4"
              auto-grow
              persistent-placeholder
              placeholder="Brief description for search results"
            />
          </div>

          <v-divider class="my-6" />

          <div class="mb-1 text-subtitle-1 font-weight-medium">Schema (structured data)</div>
          <div class="text-body-2 text-medium-emphasis mb-4">
            The Article and breadcrumb schema are added automatically. Use this section for extra schema on this post.
          </div>

          <div class="mb-4">
            <div class="d-flex align-center justify-space-between mb-2">
              <app-field-label label="FAQ" />
              <v-btn size="small" variant="tonal" :disabled="form.faqs.length >= 30" @click="addFaq">
                <v-icon start size="16">mdi-plus</v-icon>
                Add question
              </v-btn>
            </div>
            <div v-if="!form.faqs.length" class="text-body-2 text-medium-emphasis">
              No questions yet. Add the questions and answers shown in this post to get FAQ rich results.
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
        </v-form>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { update as updateBlog, type BlogDetailResponse } from '@/api/blogs.api';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  item: BlogDetailResponse | null;
  blogId: string | number;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const snackbar = useSnackbarStore();
const saving = ref(false);
const seoFormRef = ref();

const form = reactive({
  meta_title: '',
  meta_keywords: '',
  meta_description: '',
  faqs: [] as { question: string; answer: string }[],
  customSchema: '',
});

watch(
  () => props.item,
  (item) => {
    form.meta_title = item?.meta_title ? String(item.meta_title) : '';
    form.meta_keywords = item?.meta_keywords ? String(item.meta_keywords) : '';
    form.meta_description = item?.meta_description ? String(item.meta_description) : '';
    const schema = item?.schema_markup;
    form.faqs = Array.isArray(schema?.faqs)
      ? schema.faqs.map((f) => ({ question: String(f.question ?? ''), answer: String(f.answer ?? '') }))
      : [];
    form.customSchema = schema?.custom ? String(schema.custom) : '';
  },
  { immediate: true },
);

const customPlaceholder = '{"@context": "https://schema.org", "@type": "HowTo"}';

function addFaq() {
  form.faqs.push({ question: '', answer: '' });
}

function removeFaq(index: number) {
  form.faqs.splice(index, 1);
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

async function onUpdate() {
  const id = String(props.blogId ?? '').trim();
  if (!id) return;

  const { valid } = await seoFormRef.value?.validate();
  if (!valid) return;

  saving.value = true;
  try {
    await updateBlog(id, {
      meta_title: form.meta_title.trim(),
      meta_keywords: form.meta_keywords.trim(),
      meta_description: form.meta_description.trim(),
      schema_markup: {
        faqs: form.faqs.map((f) => ({ question: f.question.trim(), answer: f.answer.trim() })),
        custom: form.customSchema.trim() || null,
      },
    });

    snackbar.show({
      message: 'SEO settings updated successfully',
      color: 'success',
    });

    emit('updated');
  } catch (error: any) {
    snackbar.show({
      message: error.response?.data?.message || 'Failed to update SEO settings',
      color: 'error',
    });
  } finally {
    saving.value = false;
  }
}
</script>
