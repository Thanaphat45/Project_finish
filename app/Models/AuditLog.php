<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_type',
        'model_id',
        'action',
        'user_id',
        'old_values',
        'new_values',
        'ip_address'
    ];

    public function user()
    {
        // users' primary key is ID_User, not id; without it every log showed "System/Guest".
        return $this->belongsTo(User::class, 'user_id', 'ID_User');
    }
}
