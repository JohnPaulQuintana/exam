<?php

namespace App\Models;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;
    protected $fillable = ['blog_id','action','old_values','new_values'];

    // public function user() :BelongsTo{
    //     return $this->belongsTo(User::class);
    // }

    public function blog() :BelongsTo{
        return $this->belongsTo(Blog::class);
    }
}
