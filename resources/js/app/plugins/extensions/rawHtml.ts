import type { GeneralOptions } from 'vuetify-pro-tiptap';
import { Extension } from '@tiptap/core';
import RawHtmlActionButton from '@/components/tiptap/RawHtmlActionButton.vue';

export type RawHtmlOptions = GeneralOptions<RawHtmlOptions>;

export default Extension.create<RawHtmlOptions>({
	name: 'rawHtml',
	addStorage() {
		return {
			rawHtml: '',
		};
	},
	addOptions() {
		return {
			divider: false,
			spacer: false,
			button: ({ editor }) => ({
				component: RawHtmlActionButton,
				componentProps: {
					editor,
				},
			}),
		};
	},
});
