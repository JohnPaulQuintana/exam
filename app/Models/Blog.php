<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'image','title','content'];

    // this blogs belong to user
    public function user() :BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function auditLogs() :HasMany{
        return $this->hasMany(AuditLog::class);
    }
}
