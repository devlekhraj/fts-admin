<template>
  <AppPageHeader title="Edit EMI Bank" :subtitle="bank?.name ?? '-'" />

  <v-form ref="formRef" @submit.prevent="onSubmit">
    <v-card flat class="pa-4 d-flex flex-column gap-4" max-width="520">
      <v-text-field
        v-model="form.name"
        label="Bank Name"
        placeholder="Nabil Bank"
        required />
      <v-text-field
        v-model="form.code"
        label="Code"
        placeholder="NABIL"
        required
        class="text-uppercase"
      />
      <v-card-actions class="justify-start">
        <v-btn color="primary" :loading="submitting" type="submit">Save</v-btn>
        <v-btn variant="text" @click="router.back()">Cancel</v-btn>
      </v-card-actions>
    </v-card>
  </v-form>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { get as getEmiBank, updateEmiBank } from '@/api/emi-banks.api';

const route = useRoute();
const router = useRouter();
const id = route.params.id as string;
const bank = ref<Record<string, any> | null>(null);
const submitting = ref(false);
const formRef = ref();

const form = reactive({
  name: '',
  code: '',
});

async function fetchBank() {
  const response = await getEmiBank(id);
  bank.value = response ?? null;
  form.name = String((response as any)?.name ?? '');
  form.code = String((response as any)?.code ?? '');
}

async function onSubmit() {
  submitting.value = true;
  try {
    await updateEmiBank(id, { name: form.name, code: form.code });
    router.push({ name: 'admin.emi.banks.detail', params: { id } });
  } finally {
    submitting.value = false;
  }
}

onMounted(() => {
  fetchBank();
});
</script>
