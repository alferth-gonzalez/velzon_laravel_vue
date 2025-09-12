<template>
  <DialogModal :show="show" @close="$emit('close')" max-width="4xl">
    <template #title>
      Detalles del Cliente
    </template>

    <template #content>
      <div v-if="customer" class="space-y-6">
        <!-- Información Principal -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Información Principal</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Tipo de Cliente</label>
              <p class="mt-1 text-sm text-gray-900">{{ customer.type.description }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Estado</label>
              <span
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-1"
                :class="getStatusClass(customer.status.value)"
              >
                {{ customer.status.description }}
              </span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Documento</label>
              <p class="mt-1 text-sm text-gray-900">
                {{ customer.document.type }}: {{ customer.document.formatted }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Segmento</label>
              <p class="mt-1 text-sm text-gray-900">{{ customer.segment || 'Sin segmento' }}</p>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700">Razón Social</label>
              <p class="mt-1 text-sm text-gray-900">{{ customer.business_name }}</p>
            </div>
            <div v-if="customer.first_name || customer.last_name">
              <label class="block text-sm font-medium text-gray-700">Nombres</label>
              <p class="mt-1 text-sm text-gray-900">{{ customer.first_name }}</p>
            </div>
            <div v-if="customer.first_name || customer.last_name">
              <label class="block text-sm font-medium text-gray-700">Apellidos</label>
              <p class="mt-1 text-sm text-gray-900">{{ customer.last_name }}</p>
            </div>
          </div>
        </div>

        <!-- Información de Contacto -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Información de Contacto</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Email</label>
              <p class="mt-1 text-sm text-gray-900">{{ customer.email || 'No registrado' }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Teléfono</label>
              <p class="mt-1 text-sm text-gray-900">{{ customer.phone || 'No registrado' }}</p>
            </div>
          </div>
        </div>

        <!-- Estadísticas -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Estadísticas</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
              <div class="text-2xl font-bold text-blue-600">{{ customer.contacts_count }}</div>
              <div class="text-sm text-gray-500">Contactos</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600">{{ customer.addresses_count }}</div>
              <div class="text-sm text-gray-500">Direcciones</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-purple-600">
                {{ customer.has_tax_profile ? '✓' : '✗' }}
              </div>
              <div class="text-sm text-gray-500">Perfil Tributario</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-gray-600">
                {{ daysSinceCreation }}
              </div>
              <div class="text-sm text-gray-500">Días desde creación</div>
            </div>
          </div>
        </div>

        <!-- Notas -->
        <div v-if="customer.notes" class="bg-gray-50 p-4 rounded-lg">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Notas</h3>
          <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ customer.notes }}</p>
        </div>

        <!-- Información de Lista Negra -->
        <div v-if="customer.status.value === 'blacklisted'" class="bg-red-50 p-4 rounded-lg border border-red-200">
          <h3 class="text-lg font-medium text-red-900 mb-4">Información de Lista Negra</h3>
          <div>
            <label class="block text-sm font-medium text-red-700">Razón</label>
            <p class="mt-1 text-sm text-red-900">{{ customer.blacklist_reason || 'Sin razón especificada' }}</p>
          </div>
        </div>

        <!-- Fechas -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Información de Fechas</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Fecha de Creación</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDate(customer.created_at) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Última Actualización</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDate(customer.updated_at) }}</p>
            </div>
            <div v-if="customer.deleted_at">
              <label class="block text-sm font-medium text-gray-700">Fecha de Eliminación</label>
              <p class="mt-1 text-sm text-red-900">{{ formatDate(customer.deleted_at) }}</p>
            </div>
          </div>
        </div>
      </div>
    </template>

    <template #footer>
      <SecondaryButton @click="$emit('close')">
        Cerrar
      </SecondaryButton>
    </template>
  </DialogModal>
</template>

<script setup>
import { computed } from 'vue'
import DialogModal from '@/Components/DialogModal.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
  show: Boolean,
  customer: Object
})

defineEmits(['close'])

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    prospect: 'bg-blue-100 text-blue-800',
    suspended: 'bg-yellow-100 text-yellow-800',
    blacklisted: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const daysSinceCreation = computed(() => {
  if (!props.customer?.created_at) return 0
  
  const createdAt = new Date(props.customer.created_at)
  const now = new Date()
  const diffTime = Math.abs(now - createdAt)
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
})

const formatDate = (dateString) => {
  if (!dateString) return 'No disponible'
  
  const date = new Date(dateString)
  return date.toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>
