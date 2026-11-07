<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    protected $fillable = ['type', 'payload', 'status', 'ip_address', 'user_agent'];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }
}
