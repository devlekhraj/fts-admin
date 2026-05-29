<template>
  <div class="my-4">
    <div v-if="canComment" class="px-4 pb-6">
      <v-textarea
        v-model="comment"
        variant="outlined"
        hide-details
        density="comfortable"
        block
        label="Add a comment"
        maxlength="220"
        rows="2"
        auto-grow
        @keydown.enter.exact.prevent="submitComment"
      />
      <div class="text-right mt-3">
        <v-btn
          color="primary"
          variant="flat"
          :disabled="isSubmitting || comment.trim().length === 0"
          @click="submitComment"
        >
          <template #prepend>
            <v-progress-circular v-if="isSubmitting" indeterminate size="16" width="2" color="white" />
            <v-icon v-else>mdi-comment-plus-outline</v-icon>
          </template>
          Add Comment
        </v-btn>
      </div>
    </div>

    <ul class="timeline pa-4">
      <li v-for="(item, idx) in items" :key="String(item.id ?? idx)" class="timeline__item">
        <v-icon size="16" class="dot" :color="dotColor">mdi-check-circle</v-icon>
        <div>
          <div class="font-weight-medium" style="font-size: 0.8rem; font-weight: 400; color: #2467c0;">
            {{ item.description ?? 'Activity' }}
          </div>
          <div v-if="item.note" class="text-caption py-1 text-black">{{ item.note }}</div>
          <div class="text-caption text-medium-emphasis">
            <span v-if="item.actor">By {{ item.actor }}</span>
            <span v-if="item.actor && item.created_at"> · </span>
            <span v-if="item.created_at">{{ timeAgo(item.created_at) }}</span>
          </div>
        </div>
      </li>
    </ul>
  </div>


</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { timeAgo } from '@/shared/utils';
import { submitEmiRequestComment } from '@/api/emi-requests.api';

const props = defineProps<{
  items: Array<{
    id?: number | string;
    label?: string;
    description?: string;
    created_at?: string;
    actor?: string;
    note?: string | null;
  }>;
  emiRequestId?: string | number;
  statusLabel?: string;
  dotColor?: string;
}>();

const emit = defineEmits<{ (e: 'commented'): void }>();

const isSubmitting = ref(false);
const comment = ref('');

const canComment = computed(() => {
  const raw = String(props.statusLabel ?? '').trim().toLowerCase();
  return raw !== 'completed' && raw !== 'cancelled' && raw !== 'canceled' && raw !== 'rejected';
});

const dotColor = computed(() => String(props.dotColor ?? 'primary'));

async function submitComment() {
  if (isSubmitting.value) return;
  const text = comment.value.trim();
  if (!text) return;
  if (props.emiRequestId === undefined || props.emiRequestId === null || props.emiRequestId === '') return;

  isSubmitting.value = true;
  try {
    await submitEmiRequestComment(props.emiRequestId, text);
    comment.value = '';
    emit('commented');
  } finally {
    isSubmitting.value = false;
  }
}
</script>

<style scoped>
.timeline {
  list-style: none;
  margin: 0;
  padding: 0;
}

.timeline__item {
  display: grid;
  grid-template-columns: 16px 1fr;
  gap: 10px;
  position: relative;
  padding-bottom: 14px;
}

.timeline__item:last-child {
  padding-bottom: 0;
}

.timeline__item::before {
  content: '';
  position: absolute;
  left: 7px;
  top: 12px;
  bottom: -2px;
  width: 2px;
  background: #b1dbff;
}

.timeline__item:last-child::before {
  display: none;
}

.dot {
  position: relative;
  top: 1px;
}
</style>
