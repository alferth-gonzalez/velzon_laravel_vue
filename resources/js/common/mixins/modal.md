# Modal Mixin - Documentación de Uso

Este mixin global proporciona funcionalidades reutilizables para manejar modales de Bootstrap en toda la aplicación.

## Métodos Disponibles

### `showModal(modalId, options, onHidden)`
Muestra un modal específico.

```javascript
// Ejemplo básico
this.showModal('miModal');

// Con opciones personalizadas
this.showModal('miModal', {
  backdrop: 'static',
  keyboard: false
}, () => {
  console.log('Modal cerrado');
});
```

### `hideModal(modalId)`
Cierra un modal específico.

```javascript
this.hideModal('miModal');
```

### `initModal(modalId, options, onHidden)`
Inicializa un modal manualmente (generalmente se hace automáticamente).

```javascript
this.initModal('miModal', {
  backdrop: true,
  keyboard: true
});
```

### `toggleModal(modalId)`
Alterna la visibilidad del modal.

```javascript
this.toggleModal('miModal');
```

### `isModalVisible(modalId)`
Verifica si un modal está visible.

```javascript
if (this.isModalVisible('miModal')) {
  console.log('El modal está abierto');
}
```

### `getModalInstance(modalId)`
Obtiene la instancia de Bootstrap del modal.

```javascript
const modalInstance = this.getModalInstance('miModal');
```

## Ejemplo de Uso en Componente

```vue
<template>
  <div>
    <button @click="abrirMiModal" class="btn btn-primary">
      Abrir Modal
    </button>

    <!-- Modal HTML -->
    <div id="miModal" class="modal fade" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Mi Modal</h5>
            <button type="button" class="btn-close" @click="cerrarMiModal"></button>
          </div>
          <div class="modal-body">
            <p>Contenido del modal</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="cerrarMiModal">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    abrirMiModal() {
      // El mixin ya está disponible globalmente
      this.showModal('miModal', {
        backdrop: 'static',
        keyboard: false
      });
    },

    cerrarMiModal() {
      this.hideModal('miModal');
    },

    // Callback opcional cuando el modal se cierra
    onModalHidden(modalId) {
      if (modalId === 'miModal') {
        console.log('Mi modal se cerró');
        // Lógica adicional aquí
      }
    }
  }
};
</script>
```

## Múltiples Modales

Puedes manejar múltiples modales en el mismo componente:

```javascript
methods: {
  abrirModalCrear() {
    this.showModal('modalCrear');
  },

  abrirModalEditar(id) {
    this.itemId = id;
    this.showModal('modalEditar');
  },

  abrirModalEliminar(id) {
    this.itemId = id;
    this.showModal('modalEliminar');
  },

  onModalHidden(modalId) {
    switch(modalId) {
      case 'modalCrear':
        this.limpiarFormulario();
        break;
      case 'modalEditar':
        this.resetearDatos();
        break;
      case 'modalEliminar':
        this.itemId = null;
        break;
    }
  }
}
```

## Características

- ✅ **Gestión automática de instancias**: No necesitas manejar manualmente las instancias de Bootstrap
- ✅ **Limpieza automática**: Se limpian automáticamente al destruir el componente
- ✅ **Múltiples modales**: Soporte para múltiples modales por componente
- ✅ **Callbacks personalizados**: Callbacks para eventos de mostrar/ocultar
- ✅ **Configuración flexible**: Opciones personalizables para cada modal
- ✅ **Logging**: Logs informativos en la consola para debugging
- ✅ **Manejo de errores**: Validaciones y mensajes de error útiles

## Opciones de Bootstrap Soportadas

```javascript
const opciones = {
  backdrop: true|false|'static',  // true (default)
  keyboard: true|false,           // true (default)  
  focus: true|false               // true (default)
};
```

## Eventos del Mixin

El mixin proporciona callbacks opcionales que puedes implementar en tu componente:

- `onModalShown(modalId, event)`: Cuando el modal se muestra
- `onModalHidden(modalId, event)`: Cuando el modal se oculta

```javascript
methods: {
  onModalShown(modalId, event) {
    console.log(`Modal ${modalId} mostrado`);
  },

  onModalHidden(modalId, event) {
    console.log(`Modal ${modalId} ocultado`);
  }
}
```
