import axios from 'axios';

// Configuración base de Axios
const httpClient = axios.create({
    baseURL: '/',  // ← Cambiar a raíz para usar rutas web
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
});

// Interceptor para requests (agregar token automáticamente)
httpClient.interceptors.request.use(
    (config) => {
        // Obtener token del meta tag o localStorage
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) {
            config.headers['X-CSRF-TOKEN'] = token;
        }
        
        // Loading state (opcional)
        // store.commit('setLoading', true);
        
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Interceptor para responses (manejar errores globalmente)
httpClient.interceptors.response.use(
    (response) => {
        // Loading state (opcional)
        // store.commit('setLoading', false);
        return response;
    },
    (error) => {
        // store.commit('setLoading', false);
        
        // Manejar errores comunes
        if (error.response?.status === 401) {
            // Redirigir al login
            window.location.href = '/login';
        } else if (error.response?.status === 403) {
            // Mostrar mensaje de acceso denegado
            console.warn('Acceso denegado');
        } else if (error.response?.status >= 500) {
            // Error del servidor
            console.error('Error del servidor');
        }
        
        return Promise.reject(error);
    }
);

// API Service Class con métodos reutilizables
class ApiService {
    // GET request
    async get(url, params = {}) {
        try {
            const response = await httpClient.get(url, { params });
            return {
                success: true,
                data: response.data,
                status: response.status
            };
        } catch (error) {
            return this.handleError(error);
        }
    }

    // POST request
    async post(url, data = {}) {
        try {
            const response = await httpClient.post(url, data);
            return {
                success: true,
                data: response.data,
                status: response.status
            };
        } catch (error) {
            return this.handleError(error);
        }
    }

    // PUT request
    async put(url, data = {}) {
        try {
            const response = await httpClient.put(url, data);
            return {
                success: true,
                data: response.data,
                status: response.status
            };
        } catch (error) {
            return this.handleError(error);
        }
    }

    // DELETE request
    async delete(url) {
        try {
            const response = await httpClient.delete(url);
            return {
                success: true,
                data: response.data,
                status: response.status
            };
        } catch (error) {
            return this.handleError(error);
        }
    }

    // PATCH request
    async patch(url, data = {}) {
        try {
            const response = await httpClient.patch(url, data);
            return {
                success: true,
                data: response.data,
                status: response.status
            };
        } catch (error) {
            return this.handleError(error);
        }
    }

    // Manejar errores de forma consistente
    handleError(error) {
        const errorResponse = {
            success: false,
            message: 'Error en la petición',
            status: error.response?.status || 500,
            data: null,
            errors: {}
        };

        if (error.response) {
            // Error de respuesta del servidor
            errorResponse.message = error.response.data?.message || 'Error del servidor';
            errorResponse.errors = error.response.data?.errors || {};
            errorResponse.data = error.response.data;
        } else if (error.request) {
            // Error de red
            errorResponse.message = 'Error de conexión';
        } else {
            // Error en la configuración
            errorResponse.message = error.message;
        }

        return errorResponse;
    }

    // Métodos específicos para tu aplicación
    async getClientes(filtros = {}) {
        return await this.get('/clientes', filtros);
    }

    async getCliente(id) {
        return await this.get(`/clientes/${id}`);
    }

    async crearCliente(datos) {
        return await this.post('/clientes', datos);
    }

    async actualizarCliente(id, datos) {
        return await this.put(`/clientes/${id}`, datos);
    }

    async eliminarCliente(id) {
        return await this.delete(`/clientes/${id}`);
    }

    async buscarUsuarios(termino) {
        return await this.get('/users/search', { q: termino });
    }
}

// Plugin para Vue
export default {
    install(app) {
        const apiService = new ApiService();
        
        // Hacer disponible globalmente
        app.config.globalProperties.$api = apiService;
        app.provide('$api', apiService);
    }
};

// Exportar también la instancia para uso directo
export const $api = new ApiService();
