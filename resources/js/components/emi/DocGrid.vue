<template>
  <div class="doc-grid-wrapper">
    <div class="doc-header">
      <p class="doc-section-title">{{ title }} ({{ items.length }})</p>
    </div>
    <div class="docs-grid">
      <v-card
        v-for="doc in items"
        :key="doc.url"
        variant="flat"
        class="pa-3 doc-card"
      >
        <div>
          <div class="doc-thumb mb-2">
            <v-img
              v-if="isImage(doc)"
              :src="doc.url"
              contain
              class="thumb"
            />
            <div v-else class="doc-pdf">
              <v-icon size="42" color="primary">{{ docIcon(doc.mime_type) }}</v-icon>
            </div>
          </div>
          <div>
            <p class="doc-item-title">{{ docLabel(doc.title) }}</p>
          </div>
          <div class="d-flex ga-2 mt-2">
            <v-btn
              block
              variant="tonal"
              color="primary"
              prepend-icon="mdi-download"
              :href="doc.url"
              target="_blank"
            >
              Download
            </v-btn>
          </div>
        </div>
      </v-card>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
  items: Array<{ title: string; mime_type?: string; url: string }>;
  title?: string;
}>(), {
  title: 'Documents',
});

const docIcon = (mime?: string) => {
  const val = String(mime ?? '').toLowerCase();
  if (val.includes('pdf')) return 'mdi-file-pdf-box';
  if (val.includes('image')) return 'mdi-file-image-outline';
  return 'mdi-file-document-outline';
};

const isImage = (doc: { mime_type?: string; url?: string }) => {
  const mime = String(doc.mime_type ?? '').toLowerCase();
  if (mime.includes('image')) return true;
  const url = String(doc.url ?? '').toLowerCase();
  return /\.(png|jpe?g|webp|gif|svg)$/.test(url);
};

const docLabel = (title?: string) => {
  const val = String(title ?? '').replace(/_/g, ' ').trim();
  return val ? val.replace(/\b\w/g, (c) => c.toUpperCase()) : 'Document';
};
</script>

<style scoped>
.doc-grid-wrapper {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.doc-header {
  padding: 0 16px;
}

.doc-section-title {
  margin: 0;
  font-weight: 600;
  font-size: 0.95rem;
}

.docs-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 10px;
}

.doc-card {
  min-height: 110px;
}

.doc-thumb {
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 140px;
  background: #f6f8fb;
}

.doc-pdf {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}

.thumb {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.doc-item-title {
  margin: 0;
  font-weight: 600;
  font-size: 0.9rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

@media (max-width: 600px) {
  .docs-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>
