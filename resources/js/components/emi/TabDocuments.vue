<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12" lg="6" offset-lg="3">
        <div>
          <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Blanditiis, quia.</p>
        </div>
        <!-- <v-card class="pa-4">
          <v-row>
            <v-col cols="12" sm="6" md="3">
              <div class="text-caption text-medium-emphasis mb-1">Citizenship</div>
              <v-img
                v-if="data.documents?.citizenship"
                :src="data.documents?.citizenship"
                height="120"
                cover
              />
              <div v-else class="text-caption text-medium-emphasis">No image</div>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <div class="text-caption text-medium-emphasis mb-1">Salary Certificate</div>
              <v-img
                v-if="data.documents?.salary"
                :src="data.documents?.salary"
                height="120"
                cover
              />
              <div v-else class="text-caption text-medium-emphasis">No image</div>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <div class="text-caption text-medium-emphasis mb-1">Bank Statement</div>
              <v-img v-if="data.documents?.bank" :src="data.documents?.bank" height="120" cover />
              <div v-else class="text-caption text-medium-emphasis">No image</div>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <div class="text-caption text-medium-emphasis mb-1">Applicant Photo</div>
              <v-img v-if="data.documents?.photo" :src="data.documents?.photo" height="120" cover />
              <div v-else class="text-caption text-medium-emphasis">No image</div>
            </v-col>
          </v-row>
        </v-card> -->
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
defineProps<{ data: Record<string, any> }>();
</script>
