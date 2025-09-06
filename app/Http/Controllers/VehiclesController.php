<?php
namespace App\Http\Controllers;

use App\Http\Requests\Vehicles\VehicleStoreRequest;
use App\Http\Requests\Vehicles\VehicleUpdateRequest;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehiclesController extends Controller
{
  public function index(Request $request)
  {
    $q    = $request->string('q')->toString();
    $sort = $request->string('sort','created_at')->toString();
    $dir  = $request->string('dir','desc')->toString();
    $per  = (int) $request->integer('per_page', 10);
    $withTrashed = $request->boolean('with_trashed', false);

    $query = Vehicle::query()
      ->with('driver:id,name,email')
      ->when($withTrashed, fn($qq) => $qq->withTrashed())
      ->when($q, fn($qq) =>
        $qq->where(fn($w) =>
          $w->where('plate','like',"%$q%")
            ->orWhere('description','like',"%$q%")
        )->orWhereHas('driver', fn($d) =>
          $d->where('name','like',"%$q%")->orWhere('email','like',"%$q%")
        )
      )
      ->orderBy($sort, $dir);

    $page = $query->paginate($per)->appends($request->query());

    $vehicles = collect($page->items())->map(fn($v) => [
      'id'             => $v->id,
      'plate'          => $v->plate,
      'description'    => $v->description,
      'driver'         => $v->driver ? ['id'=>$v->driver->id,'name'=>$v->driver->name,'email'=>$v->driver->email] : null,
      'maintenance_at' => optional($v->maintenance_at)?->toDateString(),
      'deleted_at'     => optional($v->deleted_at)?->toISOString(),
      'created_at'     => optional($v->created_at)?->toISOString(),
    ]);

    return Inertia::render('Vehicles/Index', [
      'vehicles' => [
        'data'  => $vehicles,
        'links' => [
          'current_page' => $page->currentPage(),
          'last_page'    => $page->lastPage(),
          'per_page'     => $page->perPage(),
          'total'        => $page->total(),
        ],
      ],
      'filters' => [
        'q'=>$q, 'sort'=>$sort, 'dir'=>$dir, 'per_page'=>$per, 'with_trashed'=>$withTrashed,
      ],
    ]);
  }

  public function create() {
    return Inertia::render('Vehicles/Create');
  }

  public function store(Request $request) {
    $vehicle = Vehicle::create([
        'plate' => $request->plate,
        'description' => $request->description,
        'driver_id' => $request->driver_id,
        'maintenance_at' => $request->maintenance_at,
        'notes' => $request->notes,
    ]);
    return redirect()->route('vehicles.edit', $vehicle)->with('success','Vehículo creado');
  }

  public function show(Vehicle $vehicle) {
    $vehicle->load('driver:id,name,email');
    return Inertia::render('vehicles/show', [
      'vehicle' => [
        'id'             => $vehicle->id,
        'plate'          => $vehicle->plate,
        'description'    => $vehicle->description,
        'driver'         => $vehicle->driver ? ['id'=>$vehicle->driver->id,'name'=>$vehicle->driver->name,'email'=>$vehicle->driver->email] : null,
        'maintenance_at' => optional($vehicle->maintenance_at)?->toDateString(),
        'notes'          => $vehicle->notes,
      ],
    ]);
  }

  public function edit(Vehicle $vehicle) {
    $vehicle->load('driver:id,name,email');
    return Inertia::render('Vehicles/Edit', [
      'vehicle' => [
        'id'             => $vehicle->id,
        'plate'          => $vehicle->plate,
        'description'    => $vehicle->description,
        'driver'         => $vehicle->driver ? ['id'=>$vehicle->driver->id,'name'=>$vehicle->driver->name,'email'=>$vehicle->driver->email] : null,
        'maintenance_at' => optional($vehicle->maintenance_at)?->toDateString(),
        'notes'          => $vehicle->notes,
      ],
    ]);
  }

  public function update(VehicleUpdateRequest $request, Vehicle $vehicle) {
    $vehicle->update($request->validated());
    return back()->with('success','Vehículo actualizado');
  }

  public function destroy(Vehicle $vehicle) {
    $vehicle->delete();
    return redirect()->route('vehicles.index')->with('success','Vehículo eliminado');
  }

  public function restore(int $id) {
    dd($id);
    $vehicle = Vehicle::withTrashed()->findOrFail($id);
    $vehicle->restore();
    return back()->with('success','Vehículo restaurado');
  }

  // --- Búsqueda de conductores (users) para el autocomplete ---
  public function searchDrivers(Request $request)
  {
    $q = $request->string('q')->toString();
    $users = \App\Models\User::query()
      ->when($q, fn($qq) =>
        $qq->where('name','like',"%$q%")->orWhere('email','like',"%$q%")
      )
      ->orderBy('name')
      ->limit(10)
      ->get(['id','name','email']);

    return response()->json($users);
  }
}