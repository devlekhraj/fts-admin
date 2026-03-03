<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="6" offset-lg="3">
        <div class="text-h6 mb-1">Guarantor Info</div>
        <div class="text-body-2 text-medium-emphasis mb-4">Guarantor details for this EMI request.</div>

        <div>
          <div class="d-flex flex-column ga-4">
            <div>
              <v-avatar size="80" color="grey-lighten-3 text-uppercase">
                <v-icon size="40" color="grey-darken-1">mdi-account-circle</v-icon>
              </v-avatar>
            </div>

            <div>
              <div class="text-caption text-medium-emphasis">Name</div>
              <div class="text-body-2">{{ data.guarantor_name || 'N/A' }}</div>
            </div>
            <div>
              <div class="text-caption text-medium-emphasis">Email</div>
              <div class="text-body-2">{{ data.guarantor_email || 'N/A' }}</div>
            </div>
            <div>
              <div class="text-caption text-medium-emphasis">Phone</div>
              <div class="text-body-2">{{ formatPhoneNumber(data.guarantor_mobile) }}</div>
            </div>
            <div>
              <div class="text-caption text-medium-emphasis">Address</div>
              <div class="text-body-2">{{ data.guarantor_address || 'N/A' }}</div>
            </div>
            <div>
              <div class="text-caption text-medium-emphasis">Father's Name</div>
              <div class="text-body-2">{{ data.guarantor_father || 'N/A' }}</div>
            </div>
            <div>
              <div class="text-caption text-medium-emphasis">Grandfather's Name</div>
              <div class="text-body-2">{{ data.guarantor_grand_father || 'N/A' }}</div>
            </div>
          </div>
        </div>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { formatPhoneNumber } from '@/shared/utils';
defineProps<{ data: Record<string, any> }>();
</script>
