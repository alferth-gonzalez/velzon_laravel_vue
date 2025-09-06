import { router } from '@inertiajs/vue3';
import axios from 'axios';

// Composable para peticiones HTTP
export function useHttp() {
    
    // Configuración base para Axios
    const httpClient = axios.create({
        baseURL: '/',
        timeout: 10000,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    // Agregar CSRF token automáticamente
    httpClient.interceptors.request.use((config) => {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) {
            config.headers['X-CSRF-TOKEN'] = token;
        }
        return config;
    });

    // Función para peticiones GET
    const get = async (url, params = {}) => {
        try {
            const response = await httpClient.get(url, { params });
            return {
                success: true,
                data: response.data,
                status: response.status
            };
        } catch (error) {
            return handleError(error);
        }
    };

    // Función para peticiones POST
    const post = async (url, data = {}) => {
        try {
            const response = await httpClient.post(url, data);
            return {
                success: true,
                data: response.data,
                status: response.status
            };
        } catch (error) {
            return handleError(error);
        }
    };

    // Función para peticiones PUT
    const put = async (url, data = {}) => {
        try {
            const response = await httpClient.put(url, data);
            return {
                success: true,
                data: response.data,
                status: response.status
            };
        } catch (error) {
            return handleError(error);
        }
    };

    // Función para peticiones DELETE
    const del = async (url) => {
        try {
            const response = await httpClient.delete(url);
            return {
                success: true,
                data: response.data,
                status: response.status
            };
        } catch (error) {
            return handleError(error);
        }
    };

    // Función para peticiones PATCH
    const patch = async (url, data = {}) => {
        try {
            const response = await httpClient.patch(url, data);
            return {
                success: true,
                data: response.data,
                status: response.status
            };
        } catch (error) {
            return handleError(error);
        }
    };

    // Manejo de errores consistente
    const handleError = (error) => {
        const errorResponse = {
            success: false,
            message: 'Error en la petición',
            status: error.response?.status || 500,
            data: null,
            errors: {}
        };

        if (error.response) {
            errorResponse.message = error.response.data?.message || 'Error del servidor';
            errorResponse.errors = error.response.data?.errors || {};
            errorResponse.data = error.response.data;
            
            // Manejar errores específicos
            if (error.response.status === 401) {
                router.visit('/login');
            }
        } else if (error.request) {
            errorResponse.message = 'Error de conexión';
        } else {
            errorResponse.message = error.message;
        }

        return errorResponse;
    };

    // Funciones específicas para tu aplicación
    const clientesApi = {
        // Obtener todos los clientes
        index: (filtros = {}) => get('/api/clientes', filtros),
        
        // Obtener cliente específico
        show: (id) => get(`/api/clientes/${id}`),
        
        // Crear cliente
        store: (datos) => post('/api/clientes', datos),
        
        // Actualizar cliente
        update: (id, datos) => put(`/api/clientes/${id}`, datos),
        
        // Eliminar cliente
        destroy: (id) => del(`/api/clientes/${id}`),
        
        // Buscar clientes
        search: (termino) => get('/api/clientes/search', { q: termino })
    };

    const usersApi = {
        // Buscar usuarios para autocomplete
        search: (termino) => get('/api/users/search', { q: termino })
    };

    // Función genérica para formularios con Inertia
    const submitForm = async (method, url, data = {}, options = {}) => {
        const defaultOptions = {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                console.log('Formulario enviado exitosamente');
            },
            onError: (errors) => {
                console.error('Errores de validación:', errors);
            },
            ...options
        };

        router[method](url, data, defaultOptions);
    };

    return {
        // Métodos HTTP básicos
        get,
        post,
        put,
        patch,
        delete: del,
        
        // APIs específicas
        clientesApi,
        usersApi,
        
        // Utilidades
        submitForm,
        handleError
    };
}
