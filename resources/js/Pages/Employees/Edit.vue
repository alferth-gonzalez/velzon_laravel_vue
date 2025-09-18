<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const employee = ref({
  first_name: '',
  last_name: '',
  document_type: 'CC',
  document_number: '',
  email: '',
  phone: '',
})

onMounted(async () => {
  const { data } = await axios.get(`/api/employees/${route.params.id}`)
  employee.value = data.data
})

const submitForm = async () => {
  try {
    await axios.put(`/api/employees/${route.params.id}`, employee.value)
    router.push({ name: 'employees.index' }) // Redirige al listado
  } catch (error) {
    alert('Error al actualizar empleado.')
  }
}
</script>

<template>
  <div class="container py-3">
    <h2>Editar Empleado</h2>

    <form @submit.prevent="submitForm">
      <div class="mb-3">
        <label for="first_name" class="form-label">Nombre</label>
        <input v-model="employee.first_name" type="text" class="form-control" id="first_name" required>
      </div>
      <div class="mb-3">
        <label for="last_name" class="form-label">Apellido</label>
        <input v-model="employee.last_name" type="text" class="form-control" id="last_name" required>
      </div>
      <div class="mb-3">
        <label for="document_type" class="form-label">Tipo de Documento</label>
        <select v-model="employee.document_type" class="form-control" id="document_type" required>
          <option value="CC">Cédula de Ciudadanía</option>
          <option value="NIT">NIT</option>
          <option value="CE">Cédula de Extranjería</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="document_number" class="form-label">Número de Documento</label>
        <input v-model="employee.document_number" type="text" class="form-control" id="document_number" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Correo Electrónico</label>
        <input v-model="employee.email" type="email" class="form-control" id="email">
      </div>
      <div class="mb-3">
        <label for="phone" class="form-label">Teléfono</label>
        <input v-model="employee.phone" type="text" class="form-control" id="phone">
      </div>
      <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
  </div>
</template>