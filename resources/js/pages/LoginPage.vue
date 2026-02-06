<template>
  <v-container class="fill-height" fluid>
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="4">
        <v-card class="pa-6" elevation="2">
          <v-card-title>Admin Login</v-card-title>
          <v-card-text>
            <v-text-field label="Email" type="email" />
            <v-text-field label="Password" type="password" />
            <v-btn color="primary" block>Login</v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
</script>
