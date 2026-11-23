<template>
  <v-container fluid class="pb-0">
    <v-row dense>
      <v-col v-for="card in cards" :key="card.key" cols="6" class="overview-col" :class="`overview-col--${cards.length}`">
        <v-card
          variant="outlined"
          class="overview-card"
          :class="{ 'overview-card--active': modelValue === card.tab }"
          :aria-label="`Show ${card.label}`"
          @click="emit('update:modelValue', card.tab)"
        >
          <div class="d-flex align-center ga-3 pa-4">
            <v-avatar :color="card.color" variant="tonal" size="48">
              <v-icon :icon="card.icon" size="26" />
            </v-avatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ card.value }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ card.label }}</div>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <v-tabs
      :model-value="modelValue"
      color="primary"
      class="mt-4"
      show-arrows
      @update:model-value="(key) => emit('update:modelValue', key as string)"
    >
      <v-tab v-for="tab in tabs" :key="tab.key" :value="tab.key" class="text-none">
        {{ tab.label }} ({{ tab.count }})
      </v-tab>
    </v-tabs>
    <v-divider />
  </v-container>
</template>

<script setup lang="ts">
// Summary cards and status tabs shared by the Exchange, Orders and EMI request lists.
// A card or a tab sends its `tab` / `key` back; the page decides what filter that means.
export type OverviewCard = { key: string; tab: string; label: string; value: number; icon: string; color: string };
export type OverviewTab = { key: string; label: string; count: number };

defineProps<{
  cards: OverviewCard[];
  tabs: OverviewTab[];
  /** key of the active tab, or null when the filter matches no tab */
  modelValue: string | null;
}>();

const emit = defineEmits<{ (e: 'update:modelValue', key: string): void }>();
</script>

<style scoped>
.overview-card {
  cursor: pointer;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.overview-card:hover {
  border-color: rgb(var(--v-theme-primary));
}
.overview-card--active {
  border-color: rgb(var(--v-theme-primary));
  box-shadow: 0 0 0 1px rgb(var(--v-theme-primary));
}
/* Cards share the row evenly on a wide screen, whatever their number. */
@media (min-width: 960px) {
  .overview-col {
    flex: 1 1 0;
    max-width: 100%;
  }
  .overview-col--4 { max-width: 25%; }
  .overview-col--5 { max-width: 20%; }
  .overview-col--6 { max-width: 16.6667%; }
}
</style>
