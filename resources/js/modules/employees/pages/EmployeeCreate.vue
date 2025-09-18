<script setup>
import { useRouter } from "vue-router"
import EmployeeForm from "@/modules/employees/components/EmployeeForm.vue"
import { state, createEmployeeAction } from "@/modules/employees/useEmployees"

const router = useRouter()

async function onSubmit(payload) {
  try {
    await createEmployeeAction(payload)
    router.push({ name: "employees.index" })
  } catch (e) {}
}
</script>

<template>
  <div class="row">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Nuevo Empleado</h5></div>
        <div class="card-body">
          <EmployeeForm :errors="state.errors" submit-label="Crear" @submit="onSubmit" />
        </div>
      </div>
    </div>
  </div>
</template>