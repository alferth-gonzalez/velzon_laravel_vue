<script>
import UserAutocomplete from '@/Components/Shared/UserAutocomplete.vue'

export default {
  name: 'VehicleForm',
  components: { UserAutocomplete },
  props: {
    vehicle: { type: Object, default: null }, // {id, plate, description, driver:{}, maintenance_at, notes}
    action: { type: String, required: true }, // 'store' | 'update'
    submitText: { type: String, default: 'Guardar' }
  },
  data() {
    return {
      form: {
        plate:          this.vehicle?.plate ?? '',
        description:    this.vehicle?.description ?? '',
        driver_id:      this.vehicle?.driver?.id ?? null,
        maintenance_at: this.vehicle?.maintenance_at ?? '',
        notes:          this.vehicle?.notes ?? '',
      },
      driver: this.vehicle?.driver ? { ...this.vehicle.driver } : null,
      processing: false,
    }
  },
  watch: {
    vehicle: {
      immediate: true,
      deep: true,
      handler(v) {
        if (!v) return
        this.form.plate = v.plate ?? ''
        this.form.description = v.description ?? ''
        this.form.driver_id = v.driver?.id ?? null
        this.form.maintenance_at = v.maintenance_at ?? ''
        this.form.notes = v.notes ?? ''
        this.driver = v.driver ? { ...v.driver } : null
      }
    },
    driver(val) {
      this.form.driver_id = val?.id ?? null
    }
  },
  computed: {
    errors() { return this.$page.props.errors ?? {} }
  },
  methods: {
    submit() {
      const routeName = this.action === 'store' ? 'vehicles.store' : 'vehicles.update'
      const url = this.action === 'store' ? this.route(routeName) : this.route(routeName, this.vehicle.id)

      this.processing = true
      const method = this.action === 'store' ? 'post' : 'put'

      this.$inertia[method](url, this.form, {
        onFinish: () => { this.processing = false },
      })
    },
    reset() {
      this.form = { plate:'', description:'', driver_id:null, maintenance_at:'', notes:'' }
      this.driver = null
    }
  }
}
</script>

<template>
  <form class="grid gap-3 max-w-xl" @submit.prevent="submit">
    <label>Placa* <input v-model="form.plate" class="input" /></label>
    <div v-if="errors.plate" class="err">{{ errors.plate }}</div>

    <label>Descripción <input v-model="form.description" class="input" /></label>

    <label>Conductor</label>
    <UserAutocomplete v-model="driver" placeholder="Nombre o email del conductor" />
    <div v-if="errors.driver_id" class="err">{{ errors.driver_id }}</div>

    <label>Fecha de mantenimiento
      <input type="date" v-model="form.maintenance_at" class="input" />
    </label>
    <div v-if="errors.maintenance_at" class="err">{{ errors.maintenance_at }}</div>

    <label>Observaciones
      <textarea v-model="form.notes" rows="4" class="input"></textarea>
    </label>
    <div v-if="errors.notes" class="err">{{ errors.notes }}</div>

    <div class="flex gap-2">
      <button :disabled="processing" class="btn">{{ submitText }}</button>
      <button type="button" @click="reset" :disabled="processing" class="btn-outline">Limpiar</button>
    </div>
  </form>
</template>

<style scoped>
.input{ border:1px solid #ddd; padding:8px; border-radius:6px; width:100% }
.btn{ background:#2563eb;color:#fff;border:none;padding:8px 12px;border-radius:6px }
.btn-outline{ background:#fff; border:1px solid #ccc; padding:8px 12px; border-radius:6px }
.err{ color:#b91c1c; font-size:.9rem }
</style>