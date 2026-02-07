<template>
  <v-card class="pa-4">
    <v-row>
      <v-col cols="12" sm="6" md="3">
        <div class="text-caption text-medium-emphasis mb-1">Citizenship</div>
        <v-img :src="data.documents?.citizenship" height="120" cover />
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <div class="text-caption text-medium-emphasis mb-1">Salary Certificate</div>
        <v-img :src="data.documents?.salary" height="120" cover />
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <div class="text-caption text-medium-emphasis mb-1">Bank Statement</div>
        <v-img :src="data.documents?.bank" height="120" cover />
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <div class="text-caption text-medium-emphasis mb-1">Applicant Photo</div>
        <v-img :src="data.documents?.photo" height="120" cover />
      </v-col>
    </v-row>
  </v-card>
</template>

<script setup lang="ts">
defineProps<{ data: Record<string, any> }>();
</script>
