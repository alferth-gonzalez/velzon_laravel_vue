import { reactive } from "vue"
import { listEmployees, getEmployee, createEmployee, updateEmployee, deleteEmployee } from "./api/employees"

export const state = reactive({
  items: [],
  total: 0,
  loading: false,
  current: null,
  page: 1,
  perPage: 10,
  search: "",
  errors: null,
})

export async function fetchEmployees() {
  state.loading = true
  try {
    const { data } = await listEmployees({ page: state.page, per_page: state.perPage, search: state.search })
    state.items = data.data
    state.total = data.meta?.total ?? state.items.length
  } finally { state.loading = false }
}

export async function findEmployee(id) {
  state.loading = true
  try {
    const { data } = await getEmployee(id)
    state.current = data.data
  } finally { state.loading = false }
}

export async function createEmployeeAction(payload) {
  state.errors = null
  try {
    const { data } = await createEmployee(payload)
    await fetchEmployees()
    return data.data
  } catch (e) {
    if (e.response?.status === 422) state.errors = e.response.data.errors || e.response.data
    throw e
  }
}

export async function updateEmployeeAction(id, payload) {
  state.errors = null
  try {
    const { data } = await updateEmployee(id, payload)
    await fetchEmployees()
    return data.data
  } catch (e) {
    if (e.response?.status === 422) state.errors = e.response.data.errors || e.response.data
    throw e
  }
}

export async function removeEmployee(id) {
  await deleteEmployee(id)
  await fetchEmployees()
}