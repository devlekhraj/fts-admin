<template>
  <AppPageHeader title="EMI Bank Detail" :subtitle="bank?.name ?? '-'" />

  <v-card flat>
    <v-list density="comfortable">
      <v-list-item title="Name" :subtitle="bank?.name ?? '-'" />
      <v-list-item title="Code" :subtitle="bank?.code ?? '-'" />
      <v-list-item v-if="bank?.created_at" title="Created At" :subtitle="String(bank.created_at)" />
    </v-list>
    <v-card-actions>
      <v-btn color="primary" variant="tonal" @click="router.push({ name: 'admin.emi.banks.edit', params: { id } })">
        Edit
      </v-btn>
      <v-btn variant="text" @click="router.push({ name: 'admin.emi.banks' })">
        Back to list
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { get as getEmiBank } from '@/api/emi-banks.api';

const route = useRoute();
const router = useRouter();
const id = route.params.id as string;
const bank = ref<Record<string, any> | null>(null);

async function fetchBank() {
  const response = await getEmiBank(id);
  bank.value = response ?? null;
}

onMounted(() => {
  fetchBank();
});
</script>
