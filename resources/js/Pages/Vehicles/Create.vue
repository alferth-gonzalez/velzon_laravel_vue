<script>
import VehicleForm from '@/Components/Vehicles/VehicleForm.vue'

export default {
  name: 'VehiclesCreate',
  components: { VehicleForm },
}
</script>

<template>
  <Layout>
    <PageHeader title="Vehiculos" pageTitle="Vehiculos" />
    <h1 class="text-xl font-semibold mb-4">Nuevo vehículo</h1>
    <VehicleForm :action="'store'" :submitText="'Crear'" />
  </Layout>
</template>


<style scoped>
.input{ border:1px solid #ddd; padding:8px; border-radius:6px }
.btn{ background:#2563eb;color:#fff;border:none;padding:8px 12px;border-radius:6px }
.table th,.table td{ padding:8px; border-bottom:1px solid #eee }
</style>