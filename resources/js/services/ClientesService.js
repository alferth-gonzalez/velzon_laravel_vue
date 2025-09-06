import { useHttp } from '@/composables/useHttp';

/**
 * Servicio para manejar operaciones de Clientes
 * Este patrón es ideal para módulos complejos
 */
class ClientesService {
    constructor() {
        const { get, post, put, delete: del } = useHttp();
        this.http = { get, post, put, delete: del };
    }

    // ===== OPERACIONES BÁSICAS CRUD =====
    async getAll(filtros = {}) {
        return await this.http.get('/api/clientes', { params: filtros });
    }

    async getById(id) {
        return await this.http.get(`/api/clientes/${id}`);
    }

    async create(cliente) {
        return await this.http.post('/api/clientes', cliente);
    }

    async update(id, cliente) {
        return await this.http.put(`/api/clientes/${id}`, cliente);
    }

    async delete(id) {
        return await this.http.delete(`/api/clientes/${id}`);
    }

    // ===== OPERACIONES COMPLEJAS DE NEGOCIO =====
    async getClienteConHistorial(id) {
        return await this.http.get(`/api/clientes/${id}?include=historial,vehiculos,facturas`);
    }

    async generarReporte(filtros) {
        return await this.http.post('/api/clientes/reporte', filtros);
    }

    async importarDesdeExcel(archivo) {
        const formData = new FormData();
        formData.append('archivo', archivo);
        
        return await this.http.post('/api/clientes/importar', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    }

    async exportarPDF(filtros) {
        return await this.http.get('/api/clientes/exportar-pdf', {
            params: filtros,
            responseType: 'blob'
        });
    }

    // ===== BÚSQUEDAS AVANZADAS =====
    async buscarConFiltros(filtros) {
        return await this.http.post('/api/clientes/buscar', {
            search: filtros.termino,
            departamento_id: filtros.departamento,
            municipio_id: filtros.municipio,
            fecha_desde: filtros.fechaDesde,
            fecha_hasta: filtros.fechaHasta,
            tipo_cliente: filtros.tipo,
            estado: filtros.estado
        });
    }

    async getEstadisticas() {
        return await this.http.get('/api/clientes/estadisticas');
    }

    // ===== OPERACIONES CON RELACIONES =====
    async getVehiculosDelCliente(clienteId) {
        return await this.http.get(`/api/clientes/${clienteId}/vehiculos`);
    }

    async asignarVehiculo(clienteId, vehiculoData) {
        return await this.http.post(`/api/clientes/${clienteId}/vehiculos`, vehiculoData);
    }

    async getFacturasDelCliente(clienteId, paginacion = {}) {
        return await this.http.get(`/api/clientes/${clienteId}/facturas`, {
            params: { page: paginacion.page, per_page: paginacion.perPage }
        });
    }
}

// Singleton pattern para reutilizar la instancia
export default new ClientesService();
