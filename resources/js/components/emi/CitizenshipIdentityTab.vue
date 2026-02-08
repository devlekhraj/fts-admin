<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12" lg="6" offset-lg="3">
        <v-card class="pa-4">
          <v-row>
            <v-col cols="12" sm="6" md="3">
              <div class="text-caption text-medium-emphasis">DOB (AD)</div>
              <div class="text-body-2">{{ data.dob_ad }}</div>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <div class="text-caption text-medium-emphasis">DOB (BS)</div>
              <div class="text-body-2">{{ data.dob_bs }}</div>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <div class="text-caption text-medium-emphasis">Gender</div>
              <div class="text-body-2">{{ data.gender }}</div>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <div class="text-caption text-medium-emphasis">Citizenship</div>
              <div class="text-body-2">{{ data.citizenship }}</div>
            </v-col>
          </v-row>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
defineProps<{ data: Record<string, any> }>();
</script>
