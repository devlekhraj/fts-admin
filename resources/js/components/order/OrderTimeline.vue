<template>
  <div v-if="canComment" class="px-4 py-6">
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
          <v-progress-circular
            v-if="isSubmitting"
            indeterminate
            size="16"
            width="2"
            color="white"
          />
          <v-icon v-else>mdi-comment-plus-outline</v-icon>
        </template>
        Add Comment
      </v-btn>
    </div>
  </div>
  <div class="card customer-card">
    <div v-if="activities.length === 0" class="empty-state text-medium-emphasis">
      No activities yet.
    </div>

    <ul v-else class="timeline">
      <li
        v-for="(activity, index) in activities"
        :key="String(activity.id ?? index)"
        class="timeline-item"
      >
        <div class="timeline-marker" :class="{ latest: index === 0 }"></div>
        <div class="timeline-content">
          <div class="timeline-title">{{ activity.description || '-' }}</div>
          <div v-if="activity.note" class="timeline-desc">
            {{ activity.note }}
          </div>
          <div class="timeline-meta">
            <span v-if="activity.actor">By {{ activity.actor }}</span>
            <span v-if="activity.actor && activity.created_at" class="meta-sep"> · </span>
            <span v-if="activity.created_at">{{ timeAgo(activity.created_at) }}</span>
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { timeAgo } from '@/shared/utils';
import { submitOrderComment } from '@/api/orders.api';

type OrderActivity = {
  id?: number | string;
  label?: string;
  description?: string;
  created_at?: string;
  actor?: string;
  note?: string;
};

const props = defineProps<{
  orderActivities?: OrderActivity[];
  orderId?: string | number;
  orderStatus?: string;
}>();

const emit = defineEmits<{
  (e: 'commented', payload: { message: string }): void;
}>();

const activities = computed(() => (Array.isArray(props.orderActivities) ? props.orderActivities : []));

const comment = ref('');
const isSubmitting = ref(false);

const canComment = computed(() => {
  const status = String(props.orderStatus ?? '').trim().toLowerCase();
  if (!status) return true;
  return !['completed', 'canceled', 'cancelled', 'rejected'].includes(status);
});

async function submitComment() {
  if (isSubmitting.value) return;
  const text = comment.value.trim();
  if (!text) return;
  if (props.orderId === undefined || props.orderId === null || props.orderId === '') return;

  isSubmitting.value = true;
  try {
    const result = await submitOrderComment(props.orderId, text);
    if (result?.success) emit('commented', { message: 'Comment added' });
    comment.value = '';
  } finally {
    isSubmitting.value = false;
  }
}
</script>

<style scoped>
.card {
  border: 1px solid rgb(var(--v-theme-outline-variant));
  border-radius: 10px;
  padding: 14px;
  background: rgb(var(--v-theme-surface));
}

.timeline {
  list-style: none;
  padding: 0;
  margin: 0;
}

.timeline-item {
  display: grid;
  grid-template-columns: 18px 1fr;
  gap: 10px;
  position: relative;
  padding-bottom: 14px;
}

.timeline-item:last-child {
  padding-bottom: 0;
}

.timeline-marker {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2px solid rgb(var(--v-theme-primary));
  background: rgb(var(--v-theme-primary));
  position: relative;
  top: 4px;
}

.timeline-marker.latest {
  box-shadow: 0 0 0 4px rgba(var(--v-theme-primary), 0.18);
}

.timeline-item::before {
  content: '';
  position: absolute;
  left: 5px;
  top: 16px;
  bottom: 0;
  width: 2px;
  background: #c5c5c5;
}

.timeline-item:last-child::before {
  display: none;
}

.timeline-title {
  font-weight: 500;
  font-size: 0.8rem;
}

.timeline-desc {
  margin-top: 2px;
  font-size: 0.8rem;
}

.timeline-meta {
  font-size: 0.82rem;
  color: #656565;
  margin-top: 4px;
}

.empty-state {
  font-size: 0.85rem;
}
</style>
