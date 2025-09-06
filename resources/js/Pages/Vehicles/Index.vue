<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import CardHeader from "@/common/card-header.vue";
import { Head, Link, usePage, router } from '@inertiajs/vue3'
const page = usePage()

export default {
  name: 'VehiclesIndex',
  components: { Link },
  props: {
    vehicles: { type: Object, required: true }, // { data:[], links:{...} }
    filters:  { type: Object, required: true }
  },
  data() {
    return {
      q: this.filters.q ?? '',
      sort: this.filters.sort ?? 'created_at',
      dir: this.filters.dir ?? 'desc',
      per: this.filters.per_page ?? 10,
      withTrashed: !!this.filters.with_trashed,
    }
  },
  watch: {
    q: 'visit',
    sort: 'visit',
    dir: 'visit',
    per: 'visit',
    withTrashed: 'visit',
  },
  methods: {
    route, // Ziggy route helper (requiere @routes en Blade)
    visit(page = 1) {
      router.get(this.route('vehicles.index'), {
        q: this.q, sort: this.sort, dir: this.dir, per_page: this.per, with_trashed: this.withTrashed, page
      }, { preserveState: true, replace: true })
    },
    toggleSort(col) {
      if (this.sort === col) this.dir = (this.dir === 'asc' ? 'desc' : 'asc')
      else { this.sort = col; this.dir = 'asc' }
    },
    destroyItem(id) {
      if (!confirm('¿Eliminar vehículo?')) return
      router.delete(this.route('vehicles.destroy', id), { preserveScroll: true })
    },
    restoreItem(id) {
      router.post(this.route('vehicles.restore', id), {}, { preserveScroll: true })
    }
  }
}
</script>

<template>
  <Layout>
    <PageHeader title="Vehiculos" pageTitle="Vehiculos" />
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold">Vehículos</h1>
        <Link class="btn" :href="route('vehicles.create')">Nuevo</Link>
      </div>

      <div class="flex gap-2 mb-3">
        <input v-model="q" placeholder="Buscar (placa, descripción, conductor)" class="input" />
        <select v-model="sort" class="input">
          <option value="created_at">Fecha</option>
          <option value="plate">Placa</option>
          <option value="maintenance_at">Mantenimiento</option>
        </select>
        <select v-model="dir" class="input">
          <option value="asc">Asc</option>
          <option value="desc">Desc</option>
        </select>
        <select v-model="per" class="input">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
        </select>
        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="withTrashed" /> Incluir eliminados
        </label>
      </div>

      <table class="table w-full">
        <thead>
          <tr>
            <th @click="toggleSort('plate')" class="cursor-pointer">Placa</th>
            <th>Descripción</th>
            <th>Conductor</th>
            <th @click="toggleSort('maintenance_at')" class="cursor-pointer">Mantenimiento</th>
            <th>Estado</th>
            <th class="text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="v in vehicles.data" :key="v.id">
            <td>{{ v.plate }}</td>
            <td>{{ v.description ?? '-' }}</td>
            <td>{{ v.driver ? v.driver.name : '-' }}</td>
            <td>{{ v.maintenance_at ?? '-' }}</td>
            <td>
              <span v-if="v.deleted_at" class="text-red-600">Eliminado</span>
              <span v-else class="text-green-600">Vigente</span>
            </td>
            <td class="text-right space-x-2">
              <Link :href="route('vehicles.show', v.id)">Ver</Link>
              <Link :href="route('vehicles.edit', v.id)">Editar</Link>
              <button v-if="!v.deleted_at" @click="destroyItem(v.id)">Eliminar</button>
              <button v-else @click="restoreItem(v.id)">Restaurar</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="mt-3 flex gap-2 items-center">
        <button :disabled="vehicles.links.current_page<=1" @click="visit(vehicles.links.current_page-1)">Anterior</button>
        <span>Página {{ vehicles.links.current_page }} / {{ vehicles.links.last_page }}</span>
        <button :disabled="vehicles.links.current_page>=vehicles.links.last_page" @click="visit(vehicles.links.current_page+1)">Siguiente</button>
      </div>
  </Layout>
</template>


<style scoped>
.input{ border:1px solid #ddd; padding:8px; border-radius:6px }
.btn{ background:#2563eb;color:#fff;border:none;padding:8px 12px;border-radius:6px }
.table th,.table td{ padding:8px; border-bottom:1px solid #eee }
</style>