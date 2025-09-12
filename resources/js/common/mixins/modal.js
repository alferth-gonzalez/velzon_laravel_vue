/**
 * Mixin global para manejo de modales Bootstrap
 * Proporciona métodos reutilizables para inicializar, mostrar y cerrar modales
 */
export const modalMixin = {
  data() {
    return {
      modalInstances: {} // Objeto para almacenar múltiples instancias de modales
    }
  },

  methods: {
    /**
     * Inicializar un modal de Bootstrap
     * @param {string} modalId - ID del elemento modal en el DOM
     * @param {Object} options - Opciones del modal de Bootstrap
     * @param {Function} onHidden - Callback cuando el modal se cierra
     * @returns {Object} Instancia del modal
     */
    initModal(modalId, options = {}, onHidden = null) {
      return this.$nextTick(() => {
        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
          console.error(`Modal con ID '${modalId}' no encontrado en el DOM`);
          return null;
        }

        // Verificar si ya existe una instancia
        let modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
          console.log(`Modal '${modalId}' ya tiene una instancia existente`);
          return modal;
        }

        // Configuración por defecto
        const defaultOptions = {
          backdrop: 'static',
          keyboard: false,
          focus: true
        };

        // Combinar opciones
        const modalOptions = { ...defaultOptions, ...options };

        // Crear nueva instancia
        modal = new bootstrap.Modal(modalElement, modalOptions);

        // Almacenar la instancia
        this.modalInstances[modalId] = modal;

        // Agregar event listener para cuando se cierra
        if (onHidden || this.onModalHidden) {
          modalElement.addEventListener('hidden.bs.modal', (event) => {
            if (onHidden) {
              onHidden(event);
            } else if (this.onModalHidden) {
              this.onModalHidden(modalId, event);
            }
          });
        }

        // Event listener para cuando se muestra
        modalElement.addEventListener('shown.bs.modal', (event) => {
          console.log(`Modal '${modalId}' mostrado`);
          if (this.onModalShown) {
            this.onModalShown(modalId, event);
          }
        });

        console.log(`Modal '${modalId}' inicializado correctamente:`, modal);
        return modal;
      });
    },

    /**
     * Mostrar un modal
     * @param {string} modalId - ID del modal
     * @param {Object} options - Opciones para inicializar si no existe
     * @param {Function} onHidden - Callback cuando se cierra
     */
    async globalShowModal(modalId, options = {}, onHidden = null) {
      let modal = this.modalInstances[modalId];
      
      if (!modal) {
        // Si no existe la instancia, crearla
        await this.initModal(modalId, options, onHidden);
        modal = this.modalInstances[modalId];
      }

      if (modal) {
        console.log(`Mostrando modal '${modalId}'`);
        modal.show();
      } else {
        console.error(`No se pudo mostrar el modal '${modalId}'`);
      }
    },

    /**
     * Cerrar un modal específico
     * @param {string} modalId - ID del modal
     */
    globalHideModal(modalId) {
      console.log("en globalHideModal - método llamado correctamente");
      
      const modal = this.modalInstances[modalId];
      if (modal) {
        console.log(`Cerrando modal '${modalId}'`);
        modal.hide();
      } else {
        console.warn(`Modal '${modalId}' no tiene instancia activa para cerrar`);
        // Fallback: intentar cerrar directamente
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
          const bootstrapModal = bootstrap.Modal.getInstance(modalElement);
          if (bootstrapModal) {
            bootstrapModal.hide();
          }
        }
      }
    },

    /**
     * Alternar visibilidad de un modal
     * @param {string} modalId - ID del modal
     */
    globalToggleModal(modalId) {
      const modal = this.modalInstances[modalId];
      if (modal) {
        modal.toggle();
      } else {
        console.warn(`Modal '${modalId}' no tiene instancia activa para alternar`);
      }
    },

    /**
     * Obtener instancia de un modal específico
     * @param {string} modalId - ID del modal
     * @returns {Object|null} Instancia del modal o null
     */
    globalGetModalInstance(modalId) {
      return this.modalInstances[modalId] || null;
    },

    /**
     * Verificar si un modal está visible
     * @param {string} modalId - ID del modal
     * @returns {boolean}
     */
    globalIsModalVisible(modalId) {
      const modalElement = document.getElementById(modalId);
      return modalElement ? modalElement.classList.contains('show') : false;
    },

    /**
     * Limpiar instancia de un modal específico
     * @param {string} modalId - ID del modal
     */
    globalDisposeModal(modalId) {
      const modal = this.modalInstances[modalId];
      if (modal) {
        modal.dispose();
        delete this.modalInstances[modalId];
        console.log(`Modal '${modalId}' eliminado correctamente`);
      }
    },

    /**
     * Limpiar todas las instancias de modales
     */
    globalDisposeAllModals() {
      Object.keys(this.modalInstances).forEach(modalId => {
        this.globalDisposeModal(modalId);
      });
      console.log('Todas las instancias de modales han sido eliminadas');
    }
  },

  beforeUnmount() {
    // Limpiar todas las instancias de modales cuando el componente se destruye
    this.globalDisposeAllModals();
  }
};

export default modalMixin;
