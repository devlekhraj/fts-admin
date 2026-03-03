<template>
  <div class="pa-6">
    <div class="tab-wrap">
      <div class="text-h6 mb-1">Settings</div>
      <div class="text-body-2 text-medium-emphasis mb-4">Update customer account password.</div>

      <v-form ref="passwordFormRef">
        <v-text-field
          v-model="form.new_password"
          label="New Password"
          type="password"
          variant="outlined"
          density="comfortable"
          :rules="[requiredRule]"
          class="mb-3" />
        <v-text-field
          v-model="form.confirm_password"
          label="Confirm Password"
          type="password"
          variant="outlined"
          density="comfortable"
          :rules="[requiredRule, matchPasswordRule]" />
      </v-form>

      <div class="d-flex justify-end mt-4">
        <v-btn color="primary" variant="flat" @click="onUpdatePassword">
          <v-icon start size="16">mdi-lock-reset</v-icon>
          Update Password
        </v-btn>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';

const passwordFormRef = ref();
const form = reactive({
  new_password: '',
  confirm_password: '',
});

const requiredRule = (value: unknown) => (String(value ?? '').trim().length > 0 ? true : 'This field is required.');
const matchPasswordRule = (value: unknown) =>
  String(value ?? '') === String(form.new_password ?? '') ? true : 'Passwords do not match.';

async function onUpdatePassword() {
  const validationResult = await passwordFormRef.value?.validate?.();
  if (validationResult && validationResult.valid === false) return;

  // TODO: replace with update-password API call.
  console.log('Update customer password', {
    new_password: form.new_password,
  });
}
</script>

<style scoped>
.tab-wrap {
  max-width: 580px;
  margin: 0 auto;
}
</style>
