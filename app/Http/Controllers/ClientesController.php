<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientesController extends Controller
{
    public function index()
    {   
        
        return Inertia::render('clientes/index');
    }
}
