<template>
    <v-container fluid>
        <v-row>
            <v-col cols="12" lg="6" offset-lg="3">
                <v-card class="pa-4">
                    <v-table>
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th class="text-right">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Name</td>
                                <td class="text-right">{{ data.name }}</td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td class="text-right"> {{ formatNPR(data.email) }}</td>
                            </tr>
                            <tr>
                                <td>Phone</td>
                                <td class="text-right"> {{ formatNPR(data.phone) }}</td>
                            </tr>
                            <tr>
                                <td>Stamp</td>
                                <td class="text-right">{{ data.stamp }}</td>
                            </tr>
                            <tr>
                                <td>Logo</td>
                                <td class="text-right">{{ data.logo }}</td>
                            </tr>
                            <tr>
                                <td>Signature</td>
                                <td class="text-right">{{ data.signature }}</td>
                            </tr>
                        </tbody>
                    </v-table>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup lang="ts">
import { formatNPR } from '@/shared/formatters';

defineProps<{ data: Record<string, any> }>();
</script>
