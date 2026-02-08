<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12" lg="6" offset-lg="3">
        <v-card elevation="0" class="pa-4">
          <div class="info-card">
            <div class="d-flex align-center justify-space-between mb-2">
              <div>
                <h4>Generated Applications</h4>
              </div>
              <v-btn color="primary" @click="openForm()">
                <v-icon left small>mdi-file-plus</v-icon>
                Generate Now
              </v-btn>
            </div>
            <v-divider class="my-2" />
            <v-table>
              <thead>
                <tr>
                  <th>Bank</th>
                  <th class="text-right">Date</th>
                  <th class="text-right">Email Sent (Count)</th>
                  <th class="text-right">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Nabil Bank</td>
                  <td class="text-right">--</td>
                  <td class="text-right">
                    <v-chip color="grey" text-color="white" small>No (0)</v-chip>
                  </td>
                  <td class="text-right">
                    <v-btn variant="tonal" size="small">View</v-btn>
                  </td>
                </tr>
                <tr>
                  <td>Siddhartha Bank</td>
                  <td class="text-right">--</td>
                  <td class="text-right">
                    <v-chip color="green" text-color="white" small>Yes (1)</v-chip>
                  </td>
                  <td class="text-right">
                    <v-btn variant="tonal" size="small">View</v-btn>
                  </td>
                </tr>
              </tbody>
            </v-table>
          </div>
          <modal-template ref="globalModal"></modal-template>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, shallowRef } from 'vue';
import BankApplicationForm from './BankApplicationForm.vue';

const globalModal = ref(null);
const props = defineProps({
  application: {
    type: Object,
    required: true,
  },
})
const openForm = () => {
  if (!globalModal.value) return;
  globalModal.value.open({
    component: shallowRef(BankApplicationForm),
    size: 'lg',
    props: {
      application: props.application,
    },
  });
};
</script>
<style scoped>
.info-card {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 20px;
}
</style>
