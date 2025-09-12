import { ref, reactive } from 'vue'
import axios from 'axios'

export function useCustomers() {
  const customers = ref({
    data: [],
    meta: null
  })

  const loading = ref(false)

  const filters = reactive({
    search: '',
    status: '',
    type: '',
    segment: '',
    page: 1,
    per_page: 15
  })

  const loadCustomers = async () => {
    loading.value = true
    try {
      const response = await axios.get('/api/customers', {
        params: { ...filters }
      })
      
      if (response.data.success) {
        customers.value = {
          data: response.data.data,
          meta: response.data.meta
        }
      }
    } catch (error) {
      console.error('Error loading customers:', error)
      // TODO: Mostrar notificación de error
    } finally {
      loading.value = false
    }
  }

  const loadPage = (page) => {
    filters.page = page
    loadCustomers()
  }

  const searchCustomers = async (query) => {
    try {
      const response = await axios.get('/api/customers/search', {
        params: { q: query, limit: 10 }
      })
      
      if (response.data.success) {
        return response.data.data
      }
      return []
    } catch (error) {
      console.error('Error searching customers:', error)
      return []
    }
  }

  const createCustomer = async (customerData) => {
    try {
      const response = await axios.post('/api/customers', customerData)
      
      if (response.data.success) {
        // TODO: Mostrar notificación de éxito
        return response.data.data
      }
    } catch (error) {
      console.error('Error creating customer:', error)
      throw error
    }
  }

  const updateCustomer = async (id, customerData) => {
    try {
      const response = await axios.put(`/api/customers/${id}`, customerData)
      
      if (response.data.success) {
        // TODO: Mostrar notificación de éxito
        return response.data.data
      }
    } catch (error) {
      console.error('Error updating customer:', error)
      throw error
    }
  }

  const deleteCustomer = async (id) => {
    try {
      const response = await axios.delete(`/api/customers/${id}`)
      
      if (response.data.success) {
        // TODO: Mostrar notificación de éxito
        return true
      }
    } catch (error) {
      console.error('Error deleting customer:', error)
      throw error
    }
  }

  const getCustomer = async (id) => {
    try {
      const response = await axios.get(`/api/customers/${id}`)
      
      if (response.data.success) {
        return response.data.data
      }
    } catch (error) {
      console.error('Error getting customer:', error)
      throw error
    }
  }

  const mergeCustomers = async (sourceId, destinationId, reason = '') => {
    try {
      const response = await axios.post('/api/customers/merge', {
        source_id: sourceId,
        destination_id: destinationId,
        reason
      })
      
      if (response.data.success) {
        // TODO: Mostrar notificación de éxito
        return response.data.data
      }
    } catch (error) {
      console.error('Error merging customers:', error)
      throw error
    }
  }

  const blacklistCustomer = async (id, reason) => {
    try {
      const response = await axios.post(`/api/customers/${id}/blacklist`, {
        reason
      })
      
      if (response.data.success) {
        // TODO: Mostrar notificación de éxito
        return response.data.data
      }
    } catch (error) {
      console.error('Error blacklisting customer:', error)
      throw error
    }
  }

  const getCustomerMetrics = async (filters = {}) => {
    try {
      const response = await axios.get('/api/customers/metrics', {
        params: filters
      })
      
      if (response.data.success) {
        return response.data.data
      }
    } catch (error) {
      console.error('Error getting metrics:', error)
      throw error
    }
  }

  return {
    // Estado
    customers,
    loading,
    filters,

    // Métodos
    loadCustomers,
    loadPage,
    searchCustomers,
    createCustomer,
    updateCustomer,
    deleteCustomer,
    getCustomer,
    mergeCustomers,
    blacklistCustomer,
    getCustomerMetrics
  }
}
