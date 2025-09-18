<script>
import { usePage } from '@inertiajs/vue3'
import Layout from "@/Layouts/main.vue"
import PageHeader from "@/Components/page-header.vue"

export default {
  name: 'EmployeesIndex',
  components: {
    Layout,
    PageHeader
  },
  data() {
    return {
      searchQuery: '',
    }
  },
  props: {
    employees: {
      type: Object,
      required: true,
      default: () => ({ data: [], meta: { total: 0 } })
    }
  },
  computed: {
    filteredEmployees() {
      // Filtrar empleados si es necesario (por ejemplo, búsqueda por nombre o documento)
      const employeesList = this.employees.data || [];
      console.log('Employees data:', this.employees);
      console.log('Employees list:', employeesList);
      if (!this.searchQuery) {
        return employeesList;
      }
      return employeesList.filter(employee =>
        employee.first_name.toLowerCase().includes(this.searchQuery.toLowerCase())
      )
    }
  },
  mounted() {
    console.log('Component mounted. Employees:', this.employees);
  },
  methods: {
    getStatusClass(status) {
      const classes = {
        active: 'bg-success',
        inactive: 'bg-secondary',
        suspended: 'bg-warning'
      };
      return classes[status] || 'bg-secondary';
    },

    getStatusText(status) {
      const texts = {
        active: 'Activo',
        inactive: 'Inactivo',
        suspended: 'Suspendido'
      };
      return texts[status] || 'Desconocido';
    },

    deleteEmployee(id) {
      if (confirm('¿Seguro que quieres eliminar este empleado?')) {
        fetch(`/api/employees/${id}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        }).then(response => {
          if (response.ok) {
            // Actualiza la lista de empleados después de eliminar
            window.location.reload()
          } else {
            alert('Error al eliminar empleado');
          }
        }).catch(error => {
          console.error('Error:', error);
          alert('Error al eliminar empleado');
        })
      }
    }
  }
}
</script>

<template>
  <Layout>
    <PageHeader title="Gestión de Empleados" pageTitle="Administración" />
    
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-header">
            <div class="d-flex align-items-center">
              <h5 class="card-title mb-0 flex-grow-1">Empleados ({{ employees.meta.total || 0 }})</h5>
              <div class="flex-shrink-0">
                <a href="/employees/create" class="btn btn-primary">
                  <i class="ri-add-line align-bottom me-1"></i> Nuevo Empleado
                </a>
              </div>
            </div>
          </div>
          
          <div class="card-body">
            <!-- Buscador -->
            <div class="mb-3">
              <div class="search-box">
                <input v-model="searchQuery" type="text" class="form-control search bg-light border-light" placeholder="Buscar empleados...">
                <i class="ri-search-line search-icon"></i>
              </div>
            </div>

            <!-- Tabla -->
            <div class="table-responsive">
              <table class="table align-middle table-nowrap mb-0">
                <thead class="table-light text-muted">
                  <tr>
                    <th>Empleado</th>
                    <th>Documento</th>
                    <th>Email</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="employee in filteredEmployees" :key="employee.id">
                    <td>
                      <div>
                        <h6 class="fs-15 mb-1">{{ employee.first_name }} {{ employee.last_name }}</h6>
                        <p class="text-muted mb-0">ID: {{ employee.id }}</p>
                      </div>
                    </td>
                    <td>
                      <span class="fw-medium">{{ employee.document_type }}</span><br>
                      <span class="text-muted">{{ employee.document_number }}</span>
                    </td>
                    <td>{{ employee.email || 'No registrado' }}</td>
                    <td>
                      <span class="badge" :class="getStatusClass(employee.status)">
                        {{ getStatusText(employee.status) }}
                      </span>
                    </td>
                    <td>
                      <div class="dropdown">
                        <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                          <i class="ri-more-fill"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                          <li>
                            <a class="dropdown-item" :href="`/employees/${employee.id}/edit`">
                              <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Editar
                            </a>
                          </li>
                          <li class="dropdown-divider"></li>
                          <li>
                            <a class="dropdown-item text-danger" href="javascript:void(0);" @click="deleteEmployee(employee.id)">
                              <i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i> Eliminar
                            </a>
                          </li>
                        </ul>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              
              <!-- No hay resultados -->
              <div v-if="filteredEmployees.length === 0" class="text-center py-4">
                <div class="noresult">
                  <div class="text-center">
                    <lord-icon 
                      src="https://cdn.lordicon.com/msoeawqm.json" 
                      trigger="loop"
                      colors="primary:#121331,secondary:#08a88a" 
                      style="width:75px;height:75px">
                    </lord-icon>
                    <h5 class="mt-2">No se encontraron empleados</h5>
                    <p class="text-muted mb-0">No hay empleados que coincidan con los criterios de búsqueda.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<style scoped>
.search-box {
  position: relative;
}

.search-icon {
  position: absolute;
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  color: #74788d;
}

.fs-15 {
  font-size: 0.9375rem;
}
</style>