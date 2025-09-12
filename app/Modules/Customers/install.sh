#!/bin/bash

echo "🚀 Instalando módulo de Clientes..."

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Función para imprimir con color
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    print_error "Este script debe ejecutarse desde la raíz del proyecto Laravel"
    exit 1
fi

print_status "Verificando dependencias..."

# Verificar PHP
if ! command -v php &> /dev/null; then
    print_error "PHP no está instalado"
    exit 1
fi

# Verificar Composer
if ! command -v composer &> /dev/null; then
    print_error "Composer no está instalado"
    exit 1
fi

print_status "Ejecutando migraciones del módulo Customers..."

# Ejecutar migraciones específicas del módulo
php artisan migrate --path=app/Modules/Customers/Infrastructure/Database/Migrations

if [ $? -eq 0 ]; then
    print_status "Migraciones ejecutadas correctamente"
else
    print_error "Error al ejecutar las migraciones"
    exit 1
fi

print_warning "¿Deseas ejecutar los seeders de ejemplo? (y/N)"
read -r response

if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
    print_status "Ejecutando seeders..."
    php artisan db:seed --class="App\Modules\Customers\Infrastructure\Database\Seeders\CustomersSeeder"
    
    if [ $? -eq 0 ]; then
        print_status "Seeders ejecutados correctamente"
    else
        print_error "Error al ejecutar los seeders"
    fi
fi

print_status "Limpiando cache..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

print_status "Optimizando aplicación..."
php artisan config:cache
php artisan route:cache

echo ""
echo "🎉 ¡Módulo de Clientes instalado correctamente!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Configurar políticas de acceso en AuthServiceProvider"
echo "2. Personalizar validaciones según tu país/región"
echo "3. Configurar eventos y listeners según necesidades"
echo "4. Ejecutar tests: vendor/bin/phpunit app/Modules/Customers/Tests/"
echo ""
echo "📚 Documentación completa en: app/Modules/Customers/README.md"
echo ""
echo "🌐 Endpoints disponibles:"
echo "  GET    /api/customers           - Listar clientes"
echo "  POST   /api/customers           - Crear cliente"
echo "  GET    /api/customers/{id}      - Ver cliente"
echo "  PUT    /api/customers/{id}      - Actualizar cliente"
echo "  DELETE /api/customers/{id}      - Eliminar cliente"
echo "  GET    /api/customers/search    - Buscar clientes"
echo "  POST   /api/customers/merge     - Combinar clientes"
echo ""
