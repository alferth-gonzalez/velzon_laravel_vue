<script>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from "@/Layouts/main.vue"
import PageHeader from "@/Components/page-header.vue"

export default {
  name: 'EmployeeEdit',
  components: {
    Layout,
    PageHeader
  },
  props: {
    id: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      employee: {
        first_name: '',
        last_name: '',
        document_type: 'CC',
        document_number: '',
        email: '',
        phone: '',
        hire_date: '',
        status: 'active'
      },
      loading: false
    }
  },
  mounted() {
    this.loadEmployee()
  },
  methods: {
    async loadEmployee() {
      try {
        const response = await fetch(`/api/employees/${this.id}`, {
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        })
        
        if (response.ok) {
          const data = await response.json()
          this.employee = data.data
        } else {
          alert('Error al cargar empleado')
        }
      } catch (error) {
        console.error('Error:', error)
        alert('Error al cargar empleado')
      }
    },

    async submitForm() {
      this.loading = true
      
      try {
        const response = await fetch(`/api/employees/${this.id}`, {
          method: 'PUT',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          },
          body: JSON.stringify(this.employee)
        })
        
        if (response.ok) {
          alert('Empleado actualizado correctamente')
          router.visit('/employees')
        } else {
          const errorData = await response.json()
          alert('Error al actualizar empleado: ' + (errorData.message || 'Error desconocido'))
        }
      } catch (error) {
        console.error('Error:', error)
        alert('Error al actualizar empleado')
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<template>
  <Layout>
    <PageHeader title="Editar Empleado" pageTitle="Administración" />
    
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">Editar Empleado</h5>
          </div>
          
          <div class="card-body">
            <form @submit.prevent="submitForm">
              <div class="row g-3">
                <!-- Nombres -->
                <div class="col-md-6">
                  <label for="first_name" class="form-label">Nombres *</label>
                  <input v-model="employee.first_name" type="text" class="form-control" id="first_name" required>
                </div>
                <div class="col-md-6">
                  <label for="last_name" class="form-label">Apellidos</label>
                  <input v-model="employee.last_name" type="text" class="form-control" id="last_name">
                </div>

                <!-- Información del Documento -->
                <div class="col-md-6">
                  <label for="document_type" class="form-label">Tipo de Documento *</label>
                  <select v-model="employee.document_type" class="form-select" id="document_type" required>
                    <option value="">Seleccionar</option>
                    <option value="CC">Cédula de Ciudadanía</option>
                    <option value="CE">Cédula de Extranjería</option>
                    <option value="TI">Tarjeta de Identidad</option>
                    <option value="PA">Pasaporte</option>
                    <option value="NIT">NIT</option>
                    <option value="RC">Registro Civil</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label for="document_number" class="form-label">Número de Documento *</label>
                  <input v-model="employee.document_number" type="text" class="form-control" id="document_number" required>
                </div>

                <!-- Contacto -->
                <div class="col-md-6">
                  <label for="email" class="form-label">Email</label>
                  <input v-model="employee.email" type="email" class="form-control" id="email">
                </div>
                <div class="col-md-6">
                  <label for="phone" class="form-label">Teléfono</label>
                  <input v-model="employee.phone" type="tel" class="form-control" id="phone">
                </div>

                <!-- Fecha de contratación -->
                <div class="col-md-6">
                  <label for="hire_date" class="form-label">Fecha de Contratación</label>
                  <input v-model="employee.hire_date" type="date" class="form-control" id="hire_date">
                </div>

                <!-- Estado -->
                <div class="col-md-6">
                  <label for="status" class="form-label">Estado</label>
                  <select v-model="employee.status" class="form-select" id="status">
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                    <option value="suspended">Suspendido</option>
                  </select>
                </div>
              </div>

              <!-- Botones -->
              <div class="mt-4">
                <button type="button" class="btn btn-secondary me-2" @click="router.visit('/employees')">
                  Cancelar
                </button>
                <button type="submit" class="btn btn-primary" :disabled="loading">
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                  {{ loading ? 'Actualizando...' : 'Actualizar Empleado' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<style scoped>
.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}
</style>