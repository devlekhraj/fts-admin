// @ts-ignore: no type declarations for this CSS side-effect import
import '@mdi/font/css/materialdesignicons.css';
import { createVuetify } from 'vuetify';
import { VDateInput } from 'vuetify/labs/VDateInput'
import 'vuetify/styles';
import { aliases, mdi } from 'vuetify/iconsets/mdi';

export default createVuetify({
  defaults: {
    global: {
      style: {
        fontFamily: 'Poppins, sans-serif',
      },
    },
  },
  icons: {
    defaultSet: 'mdi',
    aliases,
    sets: { mdi },
  },
  theme: {
    defaultTheme: 'light',
  },
  components: {
    VDateInput,
  },
});
