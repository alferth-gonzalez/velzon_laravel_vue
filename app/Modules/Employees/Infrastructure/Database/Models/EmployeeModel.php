<?php

declare(strict_types=1);

namespace App\Modules\Employees\Infrastructure\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeModel extends Model
{
    use SoftDeletes;

    protected $table = 'emp_employees';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id','first_name','last_name','document_type','document_number',
        'email','phone','hire_date','status','created_by','updated_by'
    ];

    protected $casts = [
        'hire_date' => 'date',
    ];
}