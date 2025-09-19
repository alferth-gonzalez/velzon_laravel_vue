<template>
  <!-- Ajusta estas clases si tu versión de Velzon usa otras -->
  <div class="page-content">
    <div class="container-fluid">
      <slot />
    </div>
  </div>
</template>

<script>
export default { name: "PageWrapper" }
</script>