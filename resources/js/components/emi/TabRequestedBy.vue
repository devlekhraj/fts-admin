<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12" lg="10" offset-lg="1">
        <div class="text-h6 mb-1">Customer Detail</div>
        <div class="text-body-2 text-medium-emphasis mb-4">Requester information for this EMI request.</div>

        <div>
          <div class="d-flex flex-column ga-4">
            <div>
               <v-avatar size="80" color="grey-lighten-3 text-uppercase">
                  <v-img v-if="data.user?.avatar" :src="data.user.avatar" cover />
                  <v-icon v-else size="40" color="grey-darken-1">mdi-account-circle</v-icon>
                </v-avatar>
            </div>
            
            <div>
              <div class="text-caption text-medium-emphasis">Name</div>
              <div class="text-body-2">{{ data.user?.name ?? data.name ?? '-' }}</div>
            </div>
            <div>
              <div class="text-caption text-medium-emphasis">Email</div>
              <div class="text-body-2">{{ data.user?.email ?? data.email ?? '-' }}</div>
            </div>
            <div>
              <div class="text-caption text-medium-emphasis">Mobile</div>
              <div class="text-body-2">{{ formatPhoneNumber(data.user?.mobile ?? data.contact_number) }}</div>
            </div>
            <div>
              <div class="text-caption text-medium-emphasis">Address</div>
              <div class="text-body-2">{{ data.address ?? '-' }}</div>
            </div>
          </div>
        </div>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { formatPhoneNumber } from '@/shared/utils';
defineProps<{ data: Record<string, any> }>();
</script>
