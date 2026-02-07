import { createApp } from 'vue';
import { createPinia, setActivePinia } from 'pinia';
import App from './app/App.vue';
import router, { initAuth } from './app/router';
import vuetify from './app/plugins/vuetify';

const pinia = createPinia();
setActivePinia(pinia);
initAuth();

createApp(App)
  .use(pinia)
  .use(router)
  .use(vuetify)
  .mount('#app');
