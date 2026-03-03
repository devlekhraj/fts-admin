<template>
	<v-card class="pa-4" variant="flat">
		<v-row>
			<v-col cols="12" lg="8" offset-lg="2">
				<div class="pt-4">
					<div class="text-h6 mb-1">Message Templates</div>
					<div class="text-body-2 text-medium-emphasis mb-4">
						Configure order status notification templates using placeholder variables.
					</div>
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Placed</div>
					<v-textarea v-model="form.placed" density="comfortable" variant="outlined" auto-grow rows="3" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Canceled</div>
					<v-textarea v-model="form.canceled" density="comfortable" variant="outlined" auto-grow rows="3" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Completed</div>
					<v-textarea v-model="form.completed" density="comfortable" variant="outlined" auto-grow rows="3" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Confirmed</div>
					<v-textarea v-model="form.confirmed" density="comfortable" variant="outlined" auto-grow rows="3" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Dispatched</div>
					<v-textarea v-model="form.dispatched" density="comfortable" variant="outlined" auto-grow rows="3" />
				</div>
			</v-col>
		</v-row>
	</v-card>
</template>

<script setup lang="ts">
import { reactive } from 'vue';

defineProps<{ data?: unknown }>();

const form = reactive({
	placed: 'Dear {user_name}, Your order #{order_id} has been successfully placed. Thank you for shopping with us.',
	canceled: 'Dear {user_name}, We are sorry to inform you that your order {order_id} has been cancelled.',
	completed: 'Dear {user_name}, Your order #{order_id} has been delivered and completed. Thank you for shopping with us.',
	confirmed: 'Hello {user_name}, Your current order of invoice number #{order_id} has been confirmed. Your total amount is Rs. {order_total}. Thank you for shopping with FatafatSewa.',
	dispatched: 'Dear {user_name}, We are glad to inform you that your order #{order_id} has been dispatched. Your total order amount is Rs. {order_total}. Thank you for shopping with us.',
});
</script>
