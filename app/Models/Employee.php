<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_code', 'user_id', 'department_id', 'position_id',
        'name', 'nik', 'gender', 'place_of_birth', 'date_of_birth',
        'address', 'phone', 'email', 'join_date', 'resign_date',
        'employment_status', 'employment_type', 'photo', 'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'join_date' => 'date',
        'resign_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }
}
