import { createI18n } from 'vue-i18n';
import enMessages from './i18n/en';
import esMessages from './i18n/es';

export const i18n = createI18n({
    legacy: false,
    locale: 'es', // Idioma por defecto
    messages: {
        en: enMessages,
        es: esMessages,
    },
}); 