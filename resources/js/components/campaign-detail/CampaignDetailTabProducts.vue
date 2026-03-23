<template>
  <CampaignProducts :campaign="item" />
</template>

<script setup lang="ts">
import CampaignProducts from './CampaignProducts.vue';
import type { Campaign } from '@/types/models';

defineProps<{
  item: Campaign;
}>();
</script>
