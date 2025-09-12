<template>
  <DialogModal :show="show" @close="$emit('close')">
    <template #title>
      {{ customer ? 'Editar Cliente' : 'Nuevo Cliente' }}
    </template>

    <template #content>
      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Tipo de Cliente -->
          <div class="md:col-span-2">
            <InputLabel for="type" value="Tipo de Cliente *" />
            <select
              id="type"
              v-model="form.type"
              class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
              :class="{ 'border-red-500': form.errors.type }"
              @change="onTypeChange"
            >
              <option value="">Seleccionar tipo</option>
              <option value="natural">Persona Natural</option>
              <option value="juridical">Persona Jurídica</option>
            </select>
            <InputError :message="form.errors.type" class="mt-2" />
          </div>

          <!-- Tipo de Documento -->
          <div>
            <InputLabel for="document_type" value="Tipo de Documento *" />
            <select
              id="document_type"
              v-model="form.document_type"
              class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
              :class="{ 'border-red-500': form.errors.document_type }"
              :disabled="!form.type"
            >
              <option value="">Seleccionar tipo</option>
              <option v-for="docType in availableDocumentTypes" :key="docType.value" :value="docType.value">
                {{ docType.label }}
              </option>
            </select>
            <InputError :message="form.errors.document_type" class="mt-2" />
          </div>

          <!-- Número de Documento -->
          <div>
            <InputLabel for="document_number" value="Número de Documento *" />
            <TextInput
              id="document_number"
              v-model="form.document_number"
              type="text"
              class="w-full"
              :class="{ 'border-red-500': form.errors.document_number }"
              :disabled="!!customer"
              placeholder="Ej: 12345678"
            />
            <InputError :message="form.errors.document_number" class="mt-2" />
          </div>

          <!-- Razón Social -->
          <div class="md:col-span-2">
            <InputLabel for="business_name" value="Razón Social *" />
            <TextInput
              id="business_name"
              v-model="form.business_name"
              type="text"
              class="w-full"
              :class="{ 'border-red-500': form.errors.business_name }"
              placeholder="Nombre de la empresa o razón social"
            />
            <InputError :message="form.errors.business_name" class="mt-2" />
          </div>

          <!-- Nombres (solo para persona natural) -->
          <div v-if="form.type === 'natural'">
            <InputLabel for="first_name" value="Nombres" />
            <TextInput
              id="first_name"
              v-model="form.first_name"
              type="text"
              class="w-full"
              :class="{ 'border-red-500': form.errors.first_name }"
              placeholder="Nombres"
            />
            <InputError :message="form.errors.first_name" class="mt-2" />
          </div>

          <!-- Apellidos (solo para persona natural) -->
          <div v-if="form.type === 'natural'">
            <InputLabel for="last_name" value="Apellidos" />
            <TextInput
              id="last_name"
              v-model="form.last_name"
              type="text"
              class="w-full"
              :class="{ 'border-red-500': form.errors.last_name }"
              placeholder="Apellidos"
            />
            <InputError :message="form.errors.last_name" class="mt-2" />
          </div>

          <!-- Email -->
          <div>
            <InputLabel for="email" value="Email" />
            <TextInput
              id="email"
              v-model="form.email"
              type="email"
              class="w-full"
              :class="{ 'border-red-500': form.errors.email }"
              placeholder="correo@ejemplo.com"
            />
            <InputError :message="form.errors.email" class="mt-2" />
          </div>

          <!-- Teléfono -->
          <div>
            <InputLabel for="phone" value="Teléfono" />
            <TextInput
              id="phone"
              v-model="form.phone"
              type="text"
              class="w-full"
              :class="{ 'border-red-500': form.errors.phone }"
              placeholder="+57 300 123 4567"
            />
            <InputError :message="form.errors.phone" class="mt-2" />
          </div>

          <!-- Estado -->
          <div>
            <InputLabel for="status" value="Estado *" />
            <select
              id="status"
              v-model="form.status"
              class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
              :class="{ 'border-red-500': form.errors.status }"
            >
              <option value="prospect">Prospecto</option>
              <option value="active">Activo</option>
              <option value="inactive">Inactivo</option>
              <option value="suspended">Suspendido</option>
            </select>
            <InputError :message="form.errors.status" class="mt-2" />
          </div>

          <!-- Segmento -->
          <div>
            <InputLabel for="segment" value="Segmento" />
            <TextInput
              id="segment"
              v-model="form.segment"
              type="text"
              class="w-full"
              :class="{ 'border-red-500': form.errors.segment }"
              placeholder="VIP, Premium, Estándar..."
            />
            <InputError :message="form.errors.segment" class="mt-2" />
          </div>

          <!-- Notas -->
          <div class="md:col-span-2">
            <InputLabel for="notes" value="Notas" />
            <textarea
              id="notes"
              v-model="form.notes"
              rows="3"
              class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
              :class="{ 'border-red-500': form.errors.notes }"
              placeholder="Observaciones adicionales..."
            ></textarea>
            <InputError :message="form.errors.notes" class="mt-2" />
          </div>
        </div>
      </form>
    </template>

    <template #footer>
      <SecondaryButton @click="$emit('close')">
        Cancelar
      </SecondaryButton>

      <PrimaryButton
        class="ml-3"
        :class="{ 'opacity-25': form.processing }"
        :disabled="form.processing"
        @click="submit"
      >
        {{ customer ? 'Actualizar' : 'Crear' }} Cliente
      </PrimaryButton>
    </template>
  </DialogModal>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DialogModal from '@/Components/DialogModal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  show: Boolean,
  customer: Object
})

const emit = defineEmits(['close', 'saved'])

const form = useForm({
  type: '',
  document_type: '',
  document_number: '',
  business_name: '',
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  status: 'prospect',
  segment: '',
  notes: ''
})

const documentTypes = {
  natural: [
    { value: 'CC', label: 'Cédula de Ciudadanía' },
    { value: 'CE', label: 'Cédula de Extranjería' },
    { value: 'PA', label: 'Pasaporte' },
    { value: 'TI', label: 'Tarjeta de Identidad' },
    { value: 'RC', label: 'Registro Civil' }
  ],
  juridical: [
    { value: 'NIT', label: 'NIT' }
  ]
}

const availableDocumentTypes = computed(() => {
  return documentTypes[form.type] || []
})

const onTypeChange = () => {
  form.document_type = ''
  if (form.type === 'juridical') {
    form.first_name = ''
    form.last_name = ''
  }
}

const submit = () => {
  const url = props.customer 
    ? `/api/customers/${props.customer.id}`
    : '/api/customers'
    
  const method = props.customer ? 'put' : 'post'

  form[method](url, {
    onSuccess: () => {
      emit('saved')
      form.reset()
    }
  })
}

// Llenar formulario cuando se selecciona un cliente para editar
watch(() => props.customer, (customer) => {
  if (customer) {
    form.type = customer.type.value
    form.document_type = customer.document.type
    form.document_number = customer.document.number
    form.business_name = customer.business_name
    form.first_name = customer.first_name || ''
    form.last_name = customer.last_name || ''
    form.email = customer.email || ''
    form.phone = customer.phone || ''
    form.status = customer.status.value
    form.segment = customer.segment || ''
    form.notes = customer.notes || ''
  } else {
    form.reset()
  }
}, { immediate: true })

// Limpiar formulario cuando se cierra el modal
watch(() => props.show, (show) => {
  if (!show && !props.customer) {
    form.reset()
  }
})
</script>
