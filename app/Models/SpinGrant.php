<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpinGrant extends Model
{
    protected $fillable = ['user_id', 'spins', 'reason', 'granted_by'];

    protected $casts = ['spins' => 'integer'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}