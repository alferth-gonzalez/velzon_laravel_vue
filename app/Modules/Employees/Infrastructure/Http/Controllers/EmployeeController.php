<?php
// EmployeeController.php
namespace App\Modules\Employees\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Employees\Infrastructure\Http\Requests\{
    CreateEmployeeRequest, UpdateEmployeeRequest, FilterEmployeesRequest
};
use App\Modules\Employees\Infrastructure\Http\Resources\{EmployeeResource};
use App\Modules\Employees\Application\Commands\{
    CreateEmployeeCommand, UpdateEmployeeCommand, DeleteEmployeeCommand
};
use App\Modules\Employees\Application\Handlers\{
    CreateEmployeeHandler, UpdateEmployeeHandler, DeleteEmployeeHandler
};
use App\Modules\Employees\Application\Queries\{
    GetEmployeeByIdQuery, ListEmployeesQuery
};
use App\Modules\Employees\Domain\Repositories\EmployeeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class EmployeeController extends Controller
{
    public function index(
        FilterEmployeesRequest $r,
        EmployeeRepository $repo
    ) {
        $filters = $r->validated();
        $page    = (int)($filters['page'] ?? 1);
        $perPage = (int)($filters['per_page'] ?? 15);

        $result = $repo->paginate($filters, $page, $perPage);
        // Transformación rápida a array para no alargar
        return response()->json([
            'data' => array_map(fn($e) => [
                'id' => $e->id(),
                'first_name' => $e->firstName(),
                'last_name' => $e->lastName(),
                'document_type' => $e->document()->type(),
                'document_number' => $e->document()->number(),
                'status' => $e->status()->value,
            ], $result['data']),
            'meta' => ['total' => $result['total']]
        ]);
    }

    public function show(string $id, EmployeeRepository $repo) {
        $e = $repo->findById($id);
        abort_unless($e, 404);
        return response()->json([
            'data' => [
                'id' => $e->id(),
                'tenant_id' => $e->tenantId(),
                'first_name' => $e->firstName(),
                'last_name' => $e->lastName(),
                'document_type' => $e->document()->type(),
                'document_number' => $e->document()->number(),
                'email' => $e->email()?->value(),
                'phone' => $e->phone()?->value(),
                'hire_date' => $e->hireDate()?->format('Y-m-d'),
                'status' => $e->status()->value,
            ]
        ]);
    }

    public function store(
        CreateEmployeeRequest $r,
        CreateEmployeeHandler $handler
    ) {
        $cmd = new CreateEmployeeCommand(
            tenantId: $r->input('tenant_id'),
            firstName: $r->input('first_name'),
            lastName: $r->input('last_name'),
            documentType: $r->input('document_type'),
            documentNumber: $r->input('document_number'),
            email: $r->input('email'),
            phone: $r->input('phone'),
            hireDate: $r->input('hire_date'),
            actorId: Auth::id()?->toString() ?? null
        );
        $dto = $handler->handle($cmd);
        return response()->json(['data' => $dto], 201);
    }

    public function update(
        string $id,
        UpdateEmployeeRequest $r,
        UpdateEmployeeHandler $handler
    ) {
        $cmd = new UpdateEmployeeCommand(
            id: $id,
            firstName: $r->input('first_name'),
            lastName: $r->input('last_name'),
            email: $r->input('email'),
            phone: $r->input('phone'),
            hireDate: $r->input('hire_date'),
            actorId: Auth::id()?->toString() ?? null
        );
        $dto = $handler->handle($cmd);
        return response()->json(['data' => $dto]);
    }

    public function destroy(string $id, DeleteEmployeeHandler $handler) {
        $handler->handle(new DeleteEmployeeCommand($id));
        return response()->noContent();
    }
}