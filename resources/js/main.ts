import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './app/App.vue';
import router from './app/router';
import vuetify from './app/plugins/vuetify';

createApp(App)
  .use(createPinia())
  .use(router)
  .use(vuetify)
  .mount('#app');
