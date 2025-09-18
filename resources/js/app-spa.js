import './bootstrap'
import '../scss/config/corporate/app.scss'    // mismo estilo si quieres
import '@vueform/slider/themes/default.css'
import '../scss/mermaid.min.css'

import { createApp } from 'vue'
import router from '@/router'                  // tu Vue Router para la SPA
import App from '@/App.vue'                    // un App.vue simple con <router-view/>

// plugins opcionales que también uses en la SPA:
// import i18n from './i18n'
// import HttpPlugin from './plugins/http'

const app = createApp(App)
app.use(router)
// app.use(i18n)
// app.use(HttpPlugin)
app.mount('#app')