<template>
  <v-container fluid class="py-0">
    <div class="d-flex align-center justify-space-between">
      <div class="d-flex align-center ga-2">
        <v-btn
          v-if="showBack"
          icon
          variant="text"
          density="comfortable"
          aria-label="Back"
          @click="onBack"
        >
          <v-icon>mdi-arrow-left</v-icon>
        </v-btn>

        <div>
          <div class="text-h5 font-weight-bold">{{ title }}</div>
          <div v-if="subtitle" class="text-body-2 text-medium-emphasis">{{ subtitle }}</div>
        </div>
      </div>
      <div class="d-flex align-center ga-2">
        <slot name="actions" />
      </div>
    </div>
  </v-container>
</template>

<script setup lang="ts">
import { useAttrs } from 'vue';
import { useRouter } from 'vue-router';

withDefaults(
  defineProps<{
    title: string;
    subtitle?: string;
    showBack?: boolean;
  }>(),
  {
    showBack: true,
  },
);

const emit = defineEmits<{
  (e: 'back'): void;
}>();

const attrs = useAttrs();
const router = useRouter();

function onBack() {
  // If the parent listens to @back, let it decide what "back" means.
  // Otherwise default to router.back().
  const hasListener = Boolean((attrs as any)?.onBack);
  if (hasListener) {
    emit('back');
    return;
  }
  router.back();
}
</script>
