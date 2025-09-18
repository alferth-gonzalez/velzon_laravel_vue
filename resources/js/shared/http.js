import axios from "axios"

const http = axios.create({
  baseURL: "/", // ajusta si usas prefijo
  withCredentials: true,
  headers: { "X-Requested-With": "XMLHttpRequest" },
})

http.interceptors.response.use(
  (r) => r,
  (err) => {
    console.error("[HTTP]", err.response?.status, err.response?.data)
    return Promise.reject(err)
  }
)

export default http