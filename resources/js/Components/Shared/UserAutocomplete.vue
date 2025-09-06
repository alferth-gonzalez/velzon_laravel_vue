<script>
export default {
  name: 'UserAutocomplete',
  props: {
    modelValue: { type: Object, default: null }, // { id, name, email }
    placeholder: { type: String, default: 'Buscar conductor...' }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      q: this.modelValue?.name ?? '',
      options: [],
      open: false,
      timer: null,
    }
  },
  watch: {
    q(val) {
      clearTimeout(this.timer)
      if (!val || val.length < 2) {
        this.options = []
        this.open = false
        return
      }
      this.timer = setTimeout(async () => {
        const res = await fetch(`/users/search?q=${encodeURIComponent(val)}`, { credentials: 'same-origin' })
        this.options = await res.json()
        this.open = true
      }, 250)
    },
    modelValue: {
      immediate: true,
      handler(v) {
        if (v?.name && this.q !== v.name) this.q = v.name
      }
    }
  },
  methods: {
    pick(opt) {
      this.$emit('update:modelValue', opt)
      this.q = opt.name
      this.open = false
    }
  }
}
</script>

<template>
  <div class="relative">
    <input
      class="input w-full"
      :placeholder="placeholder"
      v-model="q"
      @focus="open = !!options.length"
    />
    <ul
      v-if="open"
      class="absolute z-10 bg-white border rounded w-full max-h-56 overflow-auto"
    >
      <li
        v-for="o in options"
        :key="o.id"
        class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
        @click="pick(o)"
      >
        {{ o.name }} <span class="text-gray-500 text-sm">({{ o.email }})</span>
      </li>
      <li v-if="!options.length" class="px-3 py-2 text-gray-500">Sin resultados</li>
    </ul>
  </div>
</template>

<style scoped>
.input{ border:1px solid #ddd; padding:8px; border-radius:6px }
</style>