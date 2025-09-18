<script setup>
import { ref, onMounted } from "vue"
import { useRouter } from "vue-router"
import EmployeeTable from "@/modules/employees/components/EmployeeTable.vue"
import { state, fetchEmployees, removeEmployee } from "@/modules/employees/useEmployees"

const router = useRouter()
const q = ref("")

onMounted(fetchEmployees)

function goCreate() { router.push({ name: "employees.create" }) }
function onEdit(id) { router.push({ name: "employees.edit", params: { id } }) }
async function onRemove(id) {
  if (confirm("¿Eliminar empleado?")) await removeEmployee(id)
}
async function onSearch() {
  state.search = q.value
  state.page = 1
  await fetchEmployees()
}
</script>

<template>
  <div class="row">
    <div class="col-12">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="mb-0">Empleados</h4>
        <div class="d-flex gap-2">
          <input v-model="q" @keyup.enter="onSearch" class="form-control" placeholder="Buscar nombre o documento" />
          <button class="btn btn-outline-secondary" @click="onSearch">Buscar</button>
          <button class="btn btn-primary" @click="goCreate">Nuevo</button>
        </div>
      </div>

      <EmployeeTable :rows="state.items" :loading="state.loading" @edit="onEdit" @remove="onRemove" />
    </div>
  </div>
</template>