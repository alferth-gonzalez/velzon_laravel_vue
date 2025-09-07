<script>
export default {
  emits: ['cerrarModal'],
  data() {
    return {
      cliente: {},
      loading: false,
      error: null
    };
  },
  props: {
    idCliente: Number,
    accion: String // crear, editar, ver
  },
  methods: {
    async obtenerCliente() {
      // Validar que tenemos un ID
      if (!this.idCliente) {
        this.error = 'ID de cliente no válido';
        return;
      }

      this.loading = true;
      this.error = null;

      try {
        // Petición HTTP genérica - TU ENFOQUE PREFERIDO
        const response = await this.$api.get(`/clientes/show/${this.idCliente}`);
        
        if (response.success) {
          this.cliente = response.data;
        } else {
          this.error = response.message || 'Error al cargar cliente';
          console.error('Error API:', response);
        }
      } catch (error) {
        this.error = 'Error de conexión al cargar cliente';
        console.error('Error de red:', error);
      } finally {
        this.loading = false;
      }
    },

    abrirModal() {
      
      // Limpiar datos antes de mostrar
      this.cliente = {};
      this.error = null;
      this.loading = false;
      
      // Usar método del mixin global
      this.globalShowModal('myModal', {
        backdrop: 'static',
        keyboard: false
      }, () => {
        // Callback cuando se cierra el modal
        this.$emit('cerrarModal');
      });
    },

    cerrarModal() {
      this.globalHideModal('myModal');
    },

    limpiarFormulario() {
      this.cliente = {};
      this.error = null;
    },

    async guardarCliente() {
      // Implementar lógica de guardado aquí
      console.log('Guardando cliente:', this.cliente);
      // Después de guardar exitosamente, cerrar el modal
      // this.cerrarModal();
    },
  },
  mounted() {
    // Mostrar el modal cuando el componente se monta
    this.$nextTick(() => {
      this.abrirModal();
    });
    
    // Cargar datos si es editar o ver
    if(this.accion === 'editar' || this.accion === 'ver') {
      this.obtenerCliente();
    }
  }
  
  // El mixin se encarga automáticamente de limpiar las instancias en beforeUnmount
};
</script>

<template>
  <!-- Botón para abrir modal (solo ejemplo) -->
  <button type="button" class="btn btn-primary"  @click="abrirModal">
    {{ accion === 'crear' ? 'Nuevo Cliente' : accion === 'editar' ? 'Editar Cliente' : 'Ver Cliente' }}
  </button>

  <!-- Modal -->
  <div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="myModalLabel">
            {{ accion === 'crear' ? 'Nuevo Cliente' : accion === 'editar' ? 'Editar Cliente' : 'Información Cliente' }}
          </h5>
          <button type="button" class="btn-close" @click="cerrarModal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body">
          <!-- Loading State -->
          <div v-if="loading" class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">{{ accion === 'crear' ? 'Preparando formulario...' : 'Cargando datos del cliente...' }}</p>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ error }}
            <button v-if="accion !== 'crear'" @click="obtenerCliente" class="btn btn-sm btn-outline-danger ms-2">
              Reintentar
            </button>
          </div>

          <!-- Formulario -->
          <div v-else>
            <form @submit.prevent="guardarCliente">
              <div class="row">
                <!-- Nombres -->
                <div class="col-md-6 mb-3">
                  <label for="nombres" class="form-label">Nombres *</label>
                  <input 
                    v-model="cliente.nombres"
                    type="text" 
                    id="nombres"
                    class="form-control" 
                    :readonly="accion === 'ver'"
                    required
                  >
                </div>

                <!-- Apellidos -->
                <div class="col-md-6 mb-3">
                  <label for="apellidos" class="form-label">Apellidos *</label>
                  <input 
                    v-model="cliente.apellidos"
                    type="text" 
                    id="apellidos"
                    class="form-control" 
                    :readonly="accion === 'ver'"
                    required
                  >
                </div>

                <!-- Email -->
                <div class="col-md-6 mb-3">
                  <label for="email" class="form-label">Email *</label>
                  <input 
                    v-model="cliente.email"
                    type="email" 
                    id="email"
                    class="form-control" 
                    :readonly="accion === 'ver'"
                    required
                  >
                </div>

                <!-- Teléfono -->
                <div class="col-md-6 mb-3">
                  <label for="telefono" class="form-label">Teléfono *</label>
                  <input 
                    v-model="cliente.telefono"
                    type="text" 
                    id="telefono"
                    class="form-control" 
                    :readonly="accion === 'ver'"
                    required
                  >
                </div>

                <!-- Dirección -->
                <div class="col-12 mb-3">
                  <label for="direccion" class="form-label">Dirección</label>
                  <textarea 
                    v-model="cliente.direccion"
                    id="direccion"
                    class="form-control" 
                    rows="3"
                    :readonly="accion === 'ver'"
                  ></textarea>
                </div>
              </div>
            </form>

            <!-- Información adicional para modo ver -->
            <div v-if="accion === 'ver' && cliente.id">
              <hr>
              <div class="row">
                <div class="col-md-6">
                  <small class="text-muted">ID: {{ cliente.id }}</small>
                </div>
                <div class="col-md-6">
                  <small class="text-muted">Creado: {{ cliente.created_at ? new Date(cliente.created_at).toLocaleDateString() : 'N/A' }}</small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="cerrarModal">
            {{ accion === 'ver' ? 'Cerrar' : 'Cancelar' }}
          </button>
          
          <!-- Botones de acción según el modo -->
          <div v-if="accion !== 'ver'">
            <button 
              v-if="accion === 'crear'"
              @click="limpiarFormulario" 
              type="button" 
              class="btn btn-outline-secondary me-2"
            >
              Limpiar
            </button>
            
            <button 
              @click="guardarCliente" 
              type="button" 
              class="btn btn-primary"
              :disabled="loading"
            >
              <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
              {{ accion === 'editar' ? 'Actualizar' : 'Crear' }} Cliente
            </button>
          </div>
        </div>

      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
</template>