<script>
import VehicleForm from '@/Components/Vehicles/VehicleForm.vue'

export default {
  name: 'VehiclesEdit',
  components: {  VehicleForm },
  props: { vehicle: { type: Object, required: true } },
}
</script>

<template>
  <Layout>
    <PageHeader title="Vehiculos" pageTitle="Vehiculos" />
    <h1 class="text-xl font-semibold mb-4">Editar vehículo</h1>
    <VehicleForm :vehicle="vehicle" :action="'update'" :submitText="'Actualizar'" />
  </Layout>
</template>
