<template>
  <v-card>
    <v-card-title class="d-flex align-center justify-space-between">
      <div class="text-h6">Recent Activity</div>
      <!-- <v-btn variant="text" size="small">
        View All
        <v-icon end>mdi-chevron-right</v-icon>
      </v-btn> -->
    </v-card-title>
    <v-divider />
    <v-card-text class="pa-4">
      <v-list density="compact" class="text-caption">
        <v-list-item
          class="py-2"
          v-for="(activity, index) in items"
          :key="activity.id"
          :class="{ 'border-b': index < items.length - 1 }"
          v-bind="itemProps(activity)"
          @click="onItemClick(activity)"
        >
          <template #prepend>
            <v-avatar size="32" color="primary" variant="tonal">
              <v-icon size="16">{{ activity.icon ?? 'mdi-clock-outline' }}</v-icon>
            </v-avatar>
          </template>
          <v-list-item-title class="text-caption text-primary">
            <span>{{ activity.title }}</span>
          </v-list-item-title>
          <v-list-item-subtitle class="mt-1">
            <div>
              <span style="color: #000; font-size: small;" v-if="activity.description">{{ activity.description }}</span>
            </div>
            <div class="d-flex align-center mt-1">
              <span v-if="activity.actor_name" class="text-caption">By: {{ activity.actor_name }}</span>
              <span v-if="activity.actor_name" class="text-caption mx-1">·</span>
              <span class="text-caption">{{ activity.time }}</span>
            </div>
          </v-list-item-subtitle>
        </v-list-item>
      </v-list>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import router from '@/app/router';

export type RecentActivityItem = {
  id: string | number;
  title: string;
  description: string;
  time: string;
  icon?: string;
  color?: string;
  actor_name?: string | null;
  entity_type?: string | null;
  entity_id?: string | number | null;
};

defineProps<{
  items: RecentActivityItem[];
}>();

function itemProps(item: RecentActivityItem) {
  return {};
}

function onItemClick(activity: RecentActivityItem) {
  if(activity.entity_type == 'orders'){
    router.push({ name: 'admin.orders.detail', params: { id: activity.entity_id } });
  }else if(activity.entity_type == 'emi_requests'){
    router.push({ name: 'admin.emi.requests.detail', params: { id: activity.entity_id } });
  }
}
</script>
