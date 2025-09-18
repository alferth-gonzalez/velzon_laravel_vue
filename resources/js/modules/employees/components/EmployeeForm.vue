<script setup>
import { reactive, watchEffect } from "vue"

const props = defineProps({
  modelValue: { type: Object, default: null },
  submitLabel: { type: String, default: "Guardar" },
  errors: { type: Object, default: null },
})
const emit = defineEmits(["submit", "update:modelValue"])

const form = reactive({
  tenant_id: null,
  first_name: "",
  last_name: "",
  document_type: "CC",
  document_number: "",
  email: "",
  phone: "",
  hire_date: ""
})

watchEffect(() => {
  if (props.modelValue) Object.assign(form, props.modelValue)
})

function onSubmit() {
  emit("submit", {
    tenant_id: form.tenant_id || null,
    first_name: form.first_name,
    last_name: form.last_name || null,
    document_type: form.document_type,
    document_number: form.document_number,
    email: form.email || null,
    phone: form.phone || null,
    hire_date: form.hire_date || null
  })
}
</script>

<template>
  <form class="row g-3" @submit.prevent="onSubmit">
    <div class="col-md-6">
      <label class="form-label">Nombres *</label>
      <input v-model="form.first_name" required class="form-control" />
      <div v-if="errors?.first_name" class="text-danger small">{{ errors.first_name[0] }}</div>
    </div>
    <div class="col-md-6">
      <label class="form-label">Apellidos</label>
      <input v-model="form.last_name" class="form-control" />
      <div v-if="errors?.last_name" class="text-danger small">{{ errors.last_name[0] }}</div>
    </div>

    <div class="col-md-4">
      <label class="form-label">Tipo Doc *</label>
      <select v-model="form.document_type" class="form-select">
        <option value="CC">CC</option>
        <option value="NIT">NIT</option>
        <option value="CE">CE</option>
        <option value="PA">PA</option>
        <option value="TI">TI</option>
        <option value="RC">RC</option>
      </select>
      <div v-if="errors?.document_type" class="text-danger small">{{ errors.document_type[0] }}</div>
    </div>
    <div class="col-md-8">
      <label class="form-label">Número *</label>
      <input v-model="form.document_number" required class="form-control" />
      <div v-if="errors?.document_number" class="text-danger small">{{ errors.document_number[0] }}</div>
    </div>

    <div class="col-md-6">
      <label class="form-label">Email</label>
      <input v-model="form.email" type="email" class="form-control" />
      <div v-if="errors?.email" class="text-danger small">{{ errors.email[0] }}</div>
    </div>
    <div class="col-md-6">
      <label class="form-label">Teléfono</label>
      <input v-model="form.phone" class="form-control" />
      <div v-if="errors?.phone" class="text-danger small">{{ errors.phone[0] }}</div>
    </div>

    <div class="col-md-6">
      <label class="form-label">Fecha Ingreso</label>
      <input v-model="form.hire_date" type="date" class="form-control" />
      <div v-if="errors?.hire_date" class="text-danger small">{{ errors.hire_date[0] }}</div>
    </div>

    <div class="col-12">
      <button class="btn btn-primary" type="submit">{{ submitLabel }}</button>
    </div>
  </form>
</template>