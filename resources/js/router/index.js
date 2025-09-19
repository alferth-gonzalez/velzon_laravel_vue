import { createRouter, createWebHistory } from "vue-router"
import store from "@/state/store" // Vuex de Velzon
import BaseLayout from "@/Layouts/BaseLayout.vue"
import PageWrapper from "@/Layouts/PageWrapper.vue"

// Páginas (lazy)
const EmployeesList   = () => import("@/modules/employees/pages/EmployeesList.vue")
const EmployeeCreate  = () => import("@/modules/employees/pages/EmployeeCreate.vue")
const EmployeeEdit    = () => import("@/modules/employees/pages/EmployeeEdit.vue")

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: "/",
      component: BaseLayout,
      children: [
        {
          path: "",
          redirect: { name: "employees.index" }
        },
        {
          path: "employees",
          name: "employees.index",
          meta: { layout: "vertical", title: "Empleados" },
          component: PageWrapper,
          children: [
            { path: "", component: EmployeesList }
          ]
        },
        {
          path: "employees/create",
          name: "employees.create",
          meta: { layout: "vertical", title: "Nuevo empleado" },
          component: PageWrapper,
          children: [
            { path: "", component: EmployeeCreate }
          ]
        },
        {
          path: "employees/:id/edit",
          name: "employees.edit",
          meta: { layout: "vertical", title: "Editar empleado" },
          component: PageWrapper,
          children: [
            { path: "", component: EmployeeEdit }
          ]
        },
      ]
    },
  ]
})

// Cambia el layoutType desde meta.layout en cada navegación
router.beforeEach((to, _from, next) => {
  const layout = to.meta?.layout || "vertical"
  // Velzon suele exponer una acción/mutación para esto:
  // - Si tienes helpers: store.dispatch("layout/changeLayoutType", layout)
  // - O mutación directa: store.commit("layout/CHANGE_LAYOUT_TYPE", layout)
  try {
    if (store.dispatch) {
      store.dispatch("layout/changeLayoutType", layout)
    } else {
      store.commit("layout/CHANGE_LAYOUT_TYPE", layout)
    }
  } catch (e) {
    // en caso de que los nombres difieran en tu versión
    // console.warn("No pude cambiar layoutType, revisa nombres de acciones/mutaciones.")
  }

  // (Opcional) título de página
  if (to.meta?.title) document.title = to.meta.title + " | Admin"

  next()
})

export default router