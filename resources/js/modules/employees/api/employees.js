import http from "@/shared/http"

export const listEmployees  = (params)           => http.get("/api/employees", { params })
export const getEmployee    = (id)               => http.get(`/api/employees/${id}`)
export const createEmployee = (payload)          => http.post("/api/employees", payload)
export const updateEmployee = (id, payload)      => http.put(`/api/employees/${id}`, payload)
export const deleteEmployee = (id)               => http.delete(`/api/employees/${id}`)