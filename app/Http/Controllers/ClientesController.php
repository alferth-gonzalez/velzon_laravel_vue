<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Cliente;

class ClientesController extends Controller
{
    public function index()
    {   
        $clientes = Cliente::all();
        return Inertia::render('clientes/index', [
            'clientes' => $clientes,
            'title' => 'Clientes',
            'description' => 'Listado de clientes',
            'breadcrumbs' => [
                ['name' => 'Clientes', 'url' => route('clientes.index')],
            ]
        ]);
    }

    public function editar($id)
    {
        $cliente = Cliente::find($id);

        return Inertia::render('clientes/edit', [
            'cliente' => $cliente,
            'title' => 'Editar cliente',
            'description' => 'Editar cliente',
            'breadcrumbs' => [
                ['name' => 'Clientes', 'url' => route('clientes.index')],
                ['name' => 'Editar cliente', 'url' => route('clientes.editar', $id)],
            ]
        ]);
    }

    /**
     * API: Obtener cliente específico
     */
    public function ver($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $cliente,
                'message' => 'Cliente encontrado'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
