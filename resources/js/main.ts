import { createApp } from 'vue';
import { createPinia, setActivePinia } from 'pinia';
import App from './app/App.vue';
import router, { initAuth } from './app/router';
import vuetify from './app/plugins/vuetify';
import { vMaska } from 'maska/vue';
import { vuetifyProTipTap } from './app/plugins/tiptap'
import RichText from './components/RichText.vue';

const pinia = createPinia();
setActivePinia(pinia);
initAuth();

const app = createApp(App);
app
  .use(pinia)
  .use(router)
  .use(vuetify)
  .use(vuetifyProTipTap)
  .component('RichText', RichText)
  .directive('maska', vMaska)
  .mount('#app');
