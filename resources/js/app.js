import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import { Ziggy } from './ziggy';
import VueApexCharts from "vue3-apexcharts";
// Vuetify
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import { components as labsComponents } from 'vuetify/dist/vuetify-labs.js'
// import { VDataTable } from 'vuetify/labs/VDataTable'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import LaravelPermissionToVueJS from 'laravel-permission-to-vuejs';
// import Vue3EasyDataTable from 'vue3-easy-data-table';
// import 'vue3-easy-data-table/dist/style.css';

const vuetify = createVuetify({
  components: {
    labsComponents,
  },
  components,
  directives,
})


const appName =
  window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
  title: title => `${title} - ${appName}`,
  resolve: name =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob('./Pages/**/*.vue')
    ),
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue, Ziggy)
      .use(vuetify)
      .use(LaravelPermissionToVueJS)
      .use(VueApexCharts)
      .mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});
