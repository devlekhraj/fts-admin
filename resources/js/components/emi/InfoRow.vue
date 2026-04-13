<template>
\t<div class="row" :class="{ 'row--highlight': highlight }">
\t\t<div class="row__label">{{ label }}</div>
\t\t<div class="row__value">{{ value || 'N/A' }}</div>
\t</div>
</template>

<script setup lang="ts">
defineProps<{ label: string; value?: string | number; highlight?: boolean }>();
</script>

<style scoped>
.row {
\tdisplay: grid;
\tgrid-template-columns: 0.38fr 1fr;
\talign-items: center;
\tgap: 6px;
\tpadding: 10px 4px;
\tborder-radius: 8px;
\tbackground-color: transparent;
}

.row--highlight {
\tbackground-color: #f7f9fb;
}

.row__label {
\tcolor: rgba(0, 0, 0, 0.6);
\tfont-size: 13px;
\tfont-weight: 500;
}

.row__value {
\tfont-weight: 600;
\tfont-size: 14px;
\ttext-align: right;
\tcolor: rgba(0, 0, 0, 0.85);
}

@media (max-width: 600px) {
\t.row {
\t\tgrid-template-columns: 1fr;
\t\tpadding: 10px 6px;
\t}

\t.row__value {
\t\ttext-align: left;
\t}
}
</style>
