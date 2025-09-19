<script>
import { router } from '@inertiajs/vue3'
import Layout from "@/Layouts/main.vue"
import PageHeader from "@/Components/page-header.vue"

export default {
  name: 'EmployeeCreate',
  components: {
    Layout,
    PageHeader
  },
  data() {
    return {
      form: {
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
  methods: {
    async submitForm() {
      this.loading = true
      
      try {
        const response = await fetch('/api/employees', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          },
          body: JSON.stringify(this.form)
        })
        
        if (response.ok) {
          alert('Empleado creado correctamente')
          router.visit('/employees')
        } else {
          const errorData = await response.json()
          alert('Error al crear empleado: ' + (errorData.message || 'Error desconocido'))
        }
      } catch (error) {
        console.error('Error:', error)
        alert('Error al crear empleado')
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<template>
  <Layout>
    <PageHeader title="Crear Empleado" pageTitle="Administración" />
    
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">Crear Nuevo Empleado</h5>
          </div>
          
          <div class="card-body">
            <form @submit.prevent="submitForm">
              <div class="row g-3">
                <!-- Nombres -->
                <div class="col-md-6">
                  <label for="first_name" class="form-label">Nombres *</label>
                  <input v-model="form.first_name" type="text" class="form-control" id="first_name" required>
                </div>
                <div class="col-md-6">
                  <label for="last_name" class="form-label">Apellidos</label>
                  <input v-model="form.last_name" type="text" class="form-control" id="last_name">
                </div>

                <!-- Información del Documento -->
                <div class="col-md-6">
                  <label for="document_type" class="form-label">Tipo de Documento *</label>
                  <select v-model="form.document_type" class="form-select" id="document_type" required>
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
                  <input v-model="form.document_number" type="text" class="form-control" id="document_number" required>
                </div>

                <!-- Contacto -->
                <div class="col-md-6">
                  <label for="email" class="form-label">Email</label>
                  <input v-model="form.email" type="email" class="form-control" id="email">
                </div>
                <div class="col-md-6">
                  <label for="phone" class="form-label">Teléfono</label>
                  <input v-model="form.phone" type="tel" class="form-control" id="phone">
                </div>

                <!-- Fecha de contratación -->
                <div class="col-md-6">
                  <label for="hire_date" class="form-label">Fecha de Contratación</label>
                  <input v-model="form.hire_date" type="date" class="form-control" id="hire_date">
                </div>

                <!-- Estado -->
                <div class="col-md-6">
                  <label for="status" class="form-label">Estado</label>
                  <select v-model="form.status" class="form-select" id="status">
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
                  {{ loading ? 'Creando...' : 'Crear Empleado' }}
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