<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { router } from '@inertiajs/vue3';

export default {
  components: {
    Layout,
    PageHeader
  },
  props: {
    customers: {
      type: Object,
      default: () => ({ data: [], meta: {} })
    }
  },
  data() {
    return {
      filters: {
        search: '',
        status: '',
        type: '',
        segment: ''
      },
      selectedCustomer: null,
      loading: false,
      customerForm: {
        type: '',
        document_type: '',
        document_number: '',
        first_name: '',
        last_name: '',
        business_name: '',
        email: '',
        phone: '',
        address: '',
        status: 'active',
        segment: ''
      },
      customerModal: null,
      viewCustomerModal: null,
      deleteConfirmModal: null
    }
  },

  methods: {
    router,
    
    loadCustomers() {
      this.loading = true;
      router.get('/customers', this.filters, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => this.loading = false
      });
    },

    debouncedSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.loadCustomers();
      }, 300);
    },

    getStatusClass(status) {
      const classes = {
        active: 'bg-success',
        inactive: 'bg-secondary',
        prospect: 'bg-primary',
        suspended: 'bg-warning',
        blacklisted: 'bg-danger'
      };
      return classes[status] || 'bg-secondary';
    },

    getStatusText(status) {
      const texts = {
        active: 'Activo',
        inactive: 'Inactivo',
        prospect: 'Prospecto',
        suspended: 'Suspendido',
        blacklisted: 'Lista Negra'
      };
      return texts[status] || 'Desconocido';
    },

    getTypeText(type) {
      const texts = {
        natural: 'Persona Natural',
        juridical: 'Persona Jurídica'
      };
      return texts[type] || 'Desconocido';
    },

    viewCustomer(customer) {
      this.selectedCustomer = customer;
      this.viewCustomerModal.show();
    },

    editCustomer(customer) {
      this.selectedCustomer = customer;
      this.fillForm(customer);
      this.customerModal.show();
    },

    confirmDelete(customer) {
      this.selectedCustomer = customer;
      this.deleteConfirmModal.show();
    },

    createCustomer() {
      this.selectedCustomer = null;
      this.resetForm();
      this.customerModal.show();
    },

    editCustomerFromView() {
      this.viewCustomerModal.hide();
      setTimeout(() => {
        this.editCustomer(this.selectedCustomer);
      }, 300);
    },

    fillForm(customer) {
      this.customerForm = {
        type: customer.type?.value || customer.type || '',
        document_type: customer.document?.type || '',
        document_number: customer.document?.number || '',
        first_name: customer.first_name || '',
        last_name: customer.last_name || '',
        business_name: customer.business_name || '',
        email: customer.email || '',
        phone: customer.phone || '',
        address: customer.address || '',
        status: customer.status?.value || customer.status || 'active',
        segment: customer.segment || ''
      };
    },

    resetForm() {
      this.customerForm = {
        type: '',
        document_type: '',
        document_number: '',
        first_name: '',
        last_name: '',
        business_name: '',
        email: '',
        phone: '',
        address: '',
        status: 'active',
        segment: ''
      };
    },

    saveCustomer() {
      this.loading = true;
      
      const url = this.selectedCustomer 
        ? `/api/customers/${this.selectedCustomer.id}`
        : '/api/customers';
      
      const method = this.selectedCustomer ? 'put' : 'post';
      
      router[method](url, this.customerForm, {
        onSuccess: (page) => {
          this.customerModal.hide();
          this.resetForm();
          this.selectedCustomer = null;
          this.loadCustomers();
          
          // Mostrar notificación de éxito
          this.showSuccessMessage(this.selectedCustomer ? 'Cliente actualizado correctamente' : 'Cliente creado correctamente');
        },
        onError: (errors) => {
          console.error('Errores de validación:', errors);
          
          // Mostrar mensaje de error
          this.showErrorMessage('Por favor revise los datos ingresados');
        },
        onFinish: () => {
          this.loading = false;
        }
      });
    },

    confirmDeleteCustomer() {
      this.loading = true;
      
      // Simular eliminación (aquí conectarías con tu API)
      setTimeout(() => {
        this.loading = false;
        this.deleteConfirmModal.hide();
        
        // Mostrar notificación de éxito
        this.showSuccessMessage('Cliente eliminado correctamente');
        
        // Recargar lista
        this.loadCustomers();
      }, 1000);
    },

    showSuccessMessage(message) {
      // Puedes implementar una notificación toast aquí
      alert(message); // Temporal
    },

    showErrorMessage(message) {
      // Mostrar mensaje de error
      alert('Error: ' + message); // Temporal
    },

    loadPage(page) {
      router.get('/customers', { ...this.filters, page }, {
        preserveState: true,
        preserveScroll: true
      });
    }
  },

  mounted() {
    // Inicializar modales de Bootstrap
    this.customerModal = new window.bootstrap.Modal(this.$refs.customerModal);
    this.viewCustomerModal = new window.bootstrap.Modal(this.$refs.viewCustomerModal);
    this.deleteConfirmModal = new window.bootstrap.Modal(this.$refs.deleteConfirmModal);
  }
};
</script>

<template>
  <Layout>
    <PageHeader title="Gestión de Clientes" pageTitle="Administración" />
    
    <div class="row">
      <div class="col-lg-12">
        <div class="card" id="customersList">
          <div class="card-header border-0">
            <div class="d-flex align-items-center">
              <h5 class="card-title mb-0 flex-grow-1">Clientes</h5>
              <div class="flex-shrink-0">
                <div class="d-flex flex-wrap gap-2">
                  <button class="btn btn-primary add-btn" @click="createCustomer">
                    <i class="ri-add-line align-bottom me-1"></i> Nuevo Cliente
                  </button>
                  <button class="btn btn-soft-success">
                    <i class="ri-file-download-line align-bottom me-1"></i> Exportar
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Filtros -->
          <div class="card-body border border-dashed border-end-0 border-start-0">
            <form>
              <div class="row g-3">
                <div class="col-xxl-4 col-sm-6">
                  <div class="search-box">
                    <input 
                      type="text" 
                      class="form-control search bg-light border-light"
                      placeholder="Buscar por documento, nombre, email..."
                      v-model="filters.search"
                      @input="debouncedSearch"
                    >
                    <i class="ri-search-line search-icon"></i>
                  </div>
                </div>

                <div class="col-xxl-2 col-sm-6">
                  <div class="input-light">
                    <select 
                      class="form-control bg-light border-light" 
                      v-model="filters.status"
                      @change="loadCustomers"
                    >
                      <option value="">Todos los Estados</option>
                      <option value="active">Activo</option>
                      <option value="inactive">Inactivo</option>
                      <option value="prospect">Prospecto</option>
                      <option value="suspended">Suspendido</option>
                      <option value="blacklisted">Lista Negra</option>
                    </select>
                  </div>
                </div>

                <div class="col-xxl-2 col-sm-6">
                  <div class="input-light">
                    <select 
                      class="form-control bg-light border-light" 
                      v-model="filters.type"
                      @change="loadCustomers"
                    >
                      <option value="">Todos los Tipos</option>
                      <option value="natural">Persona Natural</option>
                      <option value="juridical">Persona Jurídica</option>
                    </select>
                  </div>
                </div>

                <div class="col-xxl-3 col-sm-6">
                  <div class="input-light">
                    <input 
                      type="text" 
                      class="form-control bg-light border-light" 
                      placeholder="Segmento..."
                      v-model="filters.segment"
                      @input="debouncedSearch"
                    >
                  </div>
                </div>

                <div class="col-xxl-1 col-sm-6">
                  <button type="button" class="btn btn-success w-100" @click="loadCustomers">
                    <i class="ri-equalizer-fill me-1 align-bottom"></i>
                    Filtrar
                  </button>
                </div>
              </div>
            </form>
          </div>

          <!-- Tabla -->
          <div class="card-body">
            <div class="table-responsive table-card mb-4">
              <table class="table align-middle table-nowrap mb-0" id="customersTable" v-if="customers.data && customers.data.length > 0">
                <thead class="table-light text-muted">
                  <tr>
                    <th class="sort" data-sort="business_name">Cliente</th>
                    <th class="sort" data-sort="document">Documento</th>
                    <th class="sort" data-sort="email">Contacto</th>
                    <th class="sort" data-sort="type">Tipo</th>
                    <th class="sort" data-sort="status">Estado</th>
                    <th class="sort" data-sort="actions">Acciones</th>
                  </tr>
                </thead>
                <tbody class="list form-check-all">
                  <tr v-for="customer in customers.data" :key="customer.id">
                    <td class="business_name">
                      <div>
                        <h6 class="fs-15 mb-1">{{ customer.business_name }}</h6>
                        <p class="text-muted mb-0" v-if="customer.full_name !== customer.business_name">
                          {{ customer.full_name }}
                        </p>
                      </div>
                    </td>
                    <td class="document">
                      <span class="fw-medium">{{ customer.document?.type }}</span><br>
                      <span class="text-muted">{{ customer.document?.formatted || customer.document?.number }}</span>
                    </td>
                    <td class="contact">
                      <p class="mb-1">{{ customer.email }}</p>
                      <p class="text-muted mb-0">{{ customer.phone }}</p>
                    </td>
                    <td class="type">
                      {{ getTypeText(customer.type?.value || customer.type) }}
                    </td>
                    <td class="status">
                      <span 
                        class="badge fs-12"
                        :class="getStatusClass(customer.status?.value || customer.status)"
                      >
                        {{ getStatusText(customer.status?.value || customer.status) }}
                      </span>
                    </td>
                    <td class="actions">
                      <div class="dropdown">
                        <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="ri-more-fill align-middle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                          <li>
                            <a class="dropdown-item" href="javascript:void(0);" @click="viewCustomer(customer)">
                              <i class="ri-eye-fill align-bottom me-2 text-muted"></i> Ver
                            </a>
                          </li>
                          <li>
                            <a class="dropdown-item" href="javascript:void(0);" @click="editCustomer(customer)">
                              <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Editar
                            </a>
                          </li>
                          <li class="dropdown-divider"></li>
                          <li>
                            <a class="dropdown-item text-danger" href="javascript:void(0);" @click="confirmDelete(customer)">
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
              <div class="noresult" v-if="!customers.data || customers.data.length === 0">
                <div class="text-center">
                  <lord-icon 
                    src="https://cdn.lordicon.com/msoeawqm.json" 
                    trigger="loop"
                    colors="primary:#121331,secondary:#08a88a" 
                    style="width:75px;height:75px">
                  </lord-icon>
                  <h5 class="mt-2">No se encontraron clientes</h5>
                  <p class="text-muted mb-0">No hay clientes que coincidan con los criterios de búsqueda.</p>
                </div>
              </div>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-end" v-if="customers.meta && customers.meta.last_page > 1">
              <div class="pagination-wrap hstack gap-2">
                <a 
                  class="page-item pagination-prev" 
                  :class="{ disabled: customers.meta.current_page === 1 }"
                  href="javascript:void(0);" 
                  @click="loadPage(customers.meta.current_page - 1)"
                >
                  Anterior
                </a>
                <ul class="pagination listjs-pagination mb-0">
                  <li v-for="page in Math.min(customers.meta.last_page, 5)" :key="page" 
                      :class="{ active: page === customers.meta.current_page }">
                    <a class="page" href="javascript:void(0);" @click="loadPage(page)">{{ page }}</a>
                  </li>
                </ul>
                <a 
                  class="page-item pagination-next" 
                  :class="{ disabled: customers.meta.current_page === customers.meta.last_page }"
                  href="javascript:void(0);" 
                  @click="loadPage(customers.meta.current_page + 1)"
                >
                  Siguiente
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Crear/Editar Cliente -->
    <div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true" ref="customerModal" style="display: none !important;">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="customerModalLabel">
              {{ selectedCustomer ? 'Editar Cliente' : 'Nuevo Cliente' }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveCustomer">
              <div class="row g-3">
                <!-- Tipo de Cliente -->
                <div class="col-12">
                  <label for="customerType" class="form-label">Tipo de Cliente *</label>
                  <select class="form-select" id="customerType" v-model="customerForm.type" required>
                    <option value="">Seleccionar tipo</option>
                    <option value="natural">Persona Natural</option>
                    <option value="juridical">Persona Jurídica</option>
                  </select>
                </div>

                <!-- Información del Documento -->
                <div class="col-md-6">
                  <label for="documentType" class="form-label">Tipo de Documento *</label>
                  <select class="form-select" id="documentType" v-model="customerForm.document_type" required>
                    <option value="">Seleccionar</option>
                    <option value="CC">Cédula de Ciudadanía</option>
                    <option value="CE">Cédula de Extranjería</option>
                    <option value="TI">Tarjeta de Identidad</option>
                    <option value="NIT">NIT</option>
                    <option value="RUT">RUT</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label for="documentNumber" class="form-label">Número de Documento *</label>
                  <input type="text" class="form-control" id="documentNumber" v-model="customerForm.document_number" required>
                </div>

                <!-- Nombres -->
                <div class="col-md-6" v-if="customerForm.type === 'natural'">
                  <label for="firstName" class="form-label">Nombres *</label>
                  <input type="text" class="form-control" id="firstName" v-model="customerForm.first_name" required>
                </div>
                <div class="col-md-6" v-if="customerForm.type === 'natural'">
                  <label for="lastName" class="form-label">Apellidos *</label>
                  <input type="text" class="form-control" id="lastName" v-model="customerForm.last_name" required>
                </div>

                <!-- Razón Social (para jurídica) -->
                <div class="col-12" v-if="customerForm.type === 'juridical'">
                  <label for="businessName" class="form-label">Razón Social *</label>
                  <input type="text" class="form-control" id="businessName" v-model="customerForm.business_name" required>
                </div>

                <!-- Contacto -->
                <div class="col-md-6">
                  <label for="email" class="form-label">Email *</label>
                  <input type="email" class="form-control" id="email" v-model="customerForm.email" required>
                </div>
                <div class="col-md-6">
                  <label for="phone" class="form-label">Teléfono</label>
                  <input type="tel" class="form-control" id="phone" v-model="customerForm.phone">
                </div>

                <!-- Dirección -->
                <div class="col-12">
                  <label for="address" class="form-label">Dirección</label>
                  <textarea class="form-control" id="address" rows="2" v-model="customerForm.address"></textarea>
                </div>

                <!-- Estado -->
                <div class="col-md-6">
                  <label for="status" class="form-label">Estado</label>
                  <select class="form-select" id="status" v-model="customerForm.status">
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                    <option value="prospect">Prospecto</option>
                    <option value="suspended">Suspendido</option>
                  </select>
                </div>

                <!-- Segmento -->
                <div class="col-md-6">
                  <label for="segment" class="form-label">Segmento</label>
                  <input type="text" class="form-control" id="segment" v-model="customerForm.segment">
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" @click="saveCustomer" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
              {{ selectedCustomer ? 'Actualizar' : 'Crear' }} Cliente
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Ver Cliente -->
    <div class="modal fade" id="viewCustomerModal" tabindex="-1" aria-labelledby="viewCustomerModalLabel" aria-hidden="true" ref="viewCustomerModal" style="display: none !important;">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="viewCustomerModalLabel">Información del Cliente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" v-if="selectedCustomer">
            <div class="row g-3">
              <div class="col-12">
                <h6 class="text-primary mb-3">{{ selectedCustomer.business_name }}</h6>
              </div>
              
              <div class="col-md-6">
                <strong>Tipo:</strong><br>
                <span class="text-muted">{{ getTypeText(selectedCustomer.type?.value || selectedCustomer.type) }}</span>
              </div>
              
              <div class="col-md-6">
                <strong>Documento:</strong><br>
                <span class="text-muted">{{ selectedCustomer.document?.type }} - {{ selectedCustomer.document?.formatted || selectedCustomer.document?.number }}</span>
              </div>
              
              <div class="col-md-6">
                <strong>Email:</strong><br>
                <span class="text-muted">{{ selectedCustomer.email }}</span>
              </div>
              
              <div class="col-md-6">
                <strong>Teléfono:</strong><br>
                <span class="text-muted">{{ selectedCustomer.phone || 'No registrado' }}</span>
              </div>
              
              <div class="col-md-6">
                <strong>Estado:</strong><br>
                <span class="badge" :class="getStatusClass(selectedCustomer.status?.value || selectedCustomer.status)">
                  {{ getStatusText(selectedCustomer.status?.value || selectedCustomer.status) }}
                </span>
              </div>
              
              <div class="col-md-6">
                <strong>Segmento:</strong><br>
                <span class="text-muted">{{ selectedCustomer.segment || 'No asignado' }}</span>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" @click="editCustomerFromView">
              <i class="ri-pencil-line me-1"></i> Editar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Confirmar Eliminación -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true" ref="deleteConfirmModal" style="display: none !important;">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-danger" id="deleteConfirmModalLabel">Confirmar Eliminación</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" v-if="selectedCustomer">
            <div class="text-center">
              <i class="ri-delete-bin-line text-danger" style="font-size: 48px;"></i>
              <h6 class="mt-3">¿Estás seguro de eliminar este cliente?</h6>
              <p class="text-muted">{{ selectedCustomer.business_name }}</p>
              <p class="text-warning small">Esta acción no se puede deshacer.</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger" @click="confirmDeleteCustomer" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
              <i class="ri-delete-bin-line me-1"></i> Eliminar
            </button>
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

.disabled {
  pointer-events: none;
  opacity: 0.5;
}

.fs-12 {
  font-size: 0.75rem;
}

.fs-15 {
  font-size: 0.9375rem;
}
</style>