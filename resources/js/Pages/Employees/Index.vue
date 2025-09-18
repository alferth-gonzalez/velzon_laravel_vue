<script>
import { usePage } from '@inertiajs/vue3'

export default {
  name: 'EmployeesIndex',
  data() {
    return {
      searchQuery: '',
    }
  },
  props: {
    employees: {
      type: Array,
      required: true
    }
  },
  computed: {
    filteredEmployees() {
      // Filtrar empleados si es necesario (por ejemplo, búsqueda por nombre o documento)
      if (!this.searchQuery) {
        return this.employees
      }
      return this.employees.filter(employee =>
        employee.first_name.toLowerCase().includes(this.searchQuery.toLowerCase())
      )
    }
  },
  methods: {
    deleteEmployee(id) {
      if (confirm('¿Seguro que quieres eliminar este empleado?')) {
        axios.delete(`/api/employees/${id}`).then(() => {
          // Actualiza la lista de empleados después de eliminar
          window.location.reload()
        })
      }
    }
  }
}
</script>

<template>
  <div class="container py-3">
    <h2>Listado de Empleados</h2>
    <div class="mb-3">
      <input v-model="searchQuery" type="text" class="form-control" placeholder="Buscar empleados...">
    </div>

    <table class="table table-striped">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Documento</th>
          <th>Email</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="employee in filteredEmployees" :key="employee.id">
          <td>{{ employee.first_name }} {{ employee.last_name }}</td>
          <td>{{ employee.document_type }} - {{ employee.document_number }}</td>
          <td>{{ employee.email || 'No disponible' }}</td>
          <td>
            <a :href="`/employees/${employee.id}/edit`" class="btn btn-sm btn-primary">Editar</a>
            <button @click="deleteEmployee(employee.id)" class="btn btn-sm btn-danger">Eliminar</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>