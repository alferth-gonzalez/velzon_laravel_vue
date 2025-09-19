<script setup>
const props = defineProps({ rows: { type: Array, default: () => [] }, loading: Boolean })
const emit = defineEmits(["edit", "remove"])
</script>

<template>
  <div class="table-responsive">
    <table class="table align-middle table-nowrap mb-0">
      <thead class="table-light">
        <tr>
          <th>Nombre</th>
          <th>Documento</th>
          <th>Email</th>
          <th>Teléfono</th>
          <th>Estado</th>
          <th style="width:180px">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading"><td :colspan="6">Cargando…</td></tr>
        <tr v-for="r in props.rows" :key="r.id">
          <td class="fw-semibold">{{ r.first_name }} {{ r.last_name }}</td>
          <td>{{ r.document_type }} {{ r.document_number }}</td>
          <td>{{ r.email || '—' }}</td>
          <td>{{ r.phone || '—' }}</td>
          <td>
            <span :class="['badge', r.status === 'active' ? 'bg-success' : 'bg-secondary']">{{ r.status }}</span>
          </td>
          <td>
            <div class="hstack gap-2">
              <button class="btn btn-sm btn-outline-primary" @click="emit('edit', r.id)">Editar</button>
              <button class="btn btn-sm btn-outline-danger" @click="emit('remove', r.id)">Eliminar</button>
            </div>
          </td>
        </tr>
        <tr v-if="!loading && props.rows.length === 0">
          <td :colspan="6" class="text-center text-muted">Sin resultados</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>