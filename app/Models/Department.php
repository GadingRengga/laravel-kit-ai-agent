<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['code', 'name', 'parent_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function positions()
    {
        return $this->hasMany(Position::class, 'department_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id');
    }
}
