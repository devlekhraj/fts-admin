<template>
  <v-container fluid class="px-0">
    <div class="d-flex align-center justify-space-between mb-4">
      <v-btn variant="text" @click="goBack">
        <v-icon start>mdi-arrow-left</v-icon>
        Back
      </v-btn>
    </div>

    <v-card class="pa-4 mb-4">
      <div class="d-flex align-center gap-4">
        <v-avatar size="64">
          <v-img :src="application.user.avatar" alt="User" />
        </v-avatar>
        <div>
          <div class="text-subtitle-1 font-weight-semibold">{{ application.user.name }}</div>
          <div class="text-caption text-medium-emphasis">{{ application.user.email }}</div>
          <div class="text-caption text-medium-emphasis">{{ application.user.phone }}</div>
        </div>
        <v-spacer />
        <v-chip size="small" variant="tonal">{{ application.status_label }}</v-chip>
      </div>
    </v-card>

    <v-card>
      <v-tabs v-model="tab" density="comfortable" show-arrows color="primary">
        <v-tab v-for="item in tabs" :key="item.value" :value="item.value">
          <v-icon start size="16">{{ item.icon }}</v-icon>
          {{ item.label }}
        </v-tab>
      </v-tabs>
    </v-card>
    <v-divider class="mt-0"></v-divider>

    <v-window v-model="tab">
      <v-window-item :value="tab">
        <component
          :is="activeComponent"
          :data="application"
        />
      </v-window-item>
    </v-window>
  </v-container>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { get as getEmiApplication } from '@/api/emi-applications.api';
import EmiInfoTab from '@/components/emi/EmiInfoTab.vue';
import EmploymentInfoTab from '@/components/emi/EmploymentInfoTab.vue';
import CitizenshipIdentityTab from '@/components/emi/CitizenshipIdentityTab.vue';
import CardInfoTab from '@/components/emi/CardInfoTab.vue';
import GuarantorInfoTab from '@/components/emi/GuarantorInfoTab.vue';
import VehicleInfoTab from '@/components/emi/VehicleInfoTab.vue';
import DocumentsTab from '@/components/emi/DocumentsTab.vue';

const route = useRoute();
const router = useRouter();
const tabComponents = {
  emi: EmiInfoTab,
  employment: EmploymentInfoTab,
  citizenship: CitizenshipIdentityTab,
  card: CardInfoTab,
  guarantor: GuarantorInfoTab,
  vehicle: VehicleInfoTab,
  documents: DocumentsTab,
} as const;

type TabKey = keyof typeof tabComponents;
const tab = ref<TabKey>('emi');
const tabs = [
  { value: 'emi', label: 'EMI Info', icon: 'mdi-cash' },
  { value: 'employment', label: 'Employment Info', icon: 'mdi-briefcase-outline' },
  { value: 'citizenship', label: 'Citizenship & Identity', icon: 'mdi-card-account-details-outline' },
  { value: 'card', label: 'Card Info', icon: 'mdi-credit-card-outline' },
  { value: 'guarantor', label: 'Guarantor Info', icon: 'mdi-account-tie-outline' },
  { value: 'vehicle', label: 'Vehicle Info', icon: 'mdi-motorbike' },
  { value: 'documents', label: 'Documents', icon: 'mdi-file-document-outline' },
];

const activeComponent = computed(() => tabComponents[tab.value] ?? EmiInfoTab);

const application = ref({
  id: route.params.id ?? 'DEMO-001',
  user: {
    name: 'Sita Sharma',
    email: 'sita@example.com',
    phone: '+977 98XXXXXXX',
    avatar: 'https://placehold.co/64',
  },
  product: {
    name: 'Samsung Galaxy S24',
    sku: 'SGS24-128',
    price: 'Rs. 120,000.00',
    thumb: 'https://placehold.co/64',
  },
  emi_type: 'credit_card',
  emi_mode: 'bank',
  emi_per_month: 'Rs. 6,500.00',
  down_payment: 'Rs. 15,000.00',
  status_label: 'Processing',
  occupation: 'Sales Executive',
  length_of_employment: '3 years',
  monthly_income: 'Rs. 45,000.00',
  dob_ad: '1996-02-12',
  dob_bs: '2052-10-30',
  gender: 'Female',
  citizenship: '123-45-6789',
  card_holder_name: 'Sita Sharma',
  card_number: '**** **** **** 1234',
  card_expiry_date: '12/28',
  vehicle: 'Honda Dio',
  documents: {
    citizenship: 'https://placehold.co/240x160',
    salary: 'https://placehold.co/240x160',
    bank: 'https://placehold.co/240x160',
    photo: 'https://placehold.co/240x160',
  },
});

async function fetchDetail() {
  const id = String(route.params.id ?? '');
  if (!id) return;
  try {
    const { data } = await getEmiApplication(id);
    application.value = {
      ...application.value,
      ...data,
    } as any;
  } catch {
    // keep demo data on error
  }
}

function goBack() {
  router.back();
}

onMounted(fetchDetail);
</script>
