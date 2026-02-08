<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12" lg="6" offset-lg="3">
        <div>
          <div class="text-subtitle-2 mb-2">EMI Summary</div>
          <div class="text-body-2">This is a demo modal content.</div>
          <div class="text-caption text-medium-emphasis mt-2">Application ID: {{ id }}</div>
          <div>
            <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Repellat quisquam, rerum dolorem architecto
              reiciendis odio voluptates debitis nisi cum sint ab quo voluptate corrupti voluptatem porro, qui libero
              provident! Vero natus, ipsum a similique ullam sunt necessitatibus rem, provident ipsa rerum nisi expedita
              voluptas deserunt maxime. Repudiandae nam odio itaque omnis. Voluptates veniam expedita explicabo officia ea
              quibusdam eum, libero fugiat laborum accusantium at fugit culpa. Quas totam tenetur asperiores a! Blanditiis
            </p>
          </div>
          <v-btn size="small" variant="text" @click="closeModal">Close</v-btn>
        </div>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { useModalStore } from '@/stores/modal.store';

defineProps<{ id?: string | number }>();
const modal = useModalStore();

function closeModal() {
  modal.close();
}
</script>
