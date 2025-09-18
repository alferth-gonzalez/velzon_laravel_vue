<script setup>
import { onMounted, ref } from "vue"
import { useRoute, useRouter } from "vue-router"
import EmployeeForm from "@/modules/employees/components/EmployeeForm.vue"
import { state, findEmployee, updateEmployeeAction } from "@/modules/employees/useEmployees"

const route = useRoute()
const router = useRouter()
const model = ref(null)

onMounted(async () => {
  await findEmployee(route.params.id)
  model.value = {
    id: state.current?.id,
    tenant_id: state.current?.tenant_id ?? null,
    first_name: state.current?.first_name ?? "",
    last_name: state.current?.last_name ?? "",
    document_type: state.current?.document_type ?? "CC",
    document_number: state.current?.document_number ?? "",
    email: state.current?.email ?? "",
    phone: state.current?.phone ?? "",
    hire_date: state.current?.hire_date ?? ""
  }
})

async function onSubmit(payload) {
  try {
    await updateEmployeeAction(route.params.id, payload)
    router.push({ name: "employees.index" })
  } catch (e) {}
}
</script>

<template>
  <div class="row">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Editar Empleado</h5></div>
        <div class="card-body">
          <EmployeeForm v-if="model" v-model="model" :errors="state.errors" submit-label="Actualizar" @submit="onSubmit" />
          <div v-else>Cargando…</div>
        </div>
      </div>
    </div>
  </div>
</template>