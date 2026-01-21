<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AiApiConfig extends Model
{
    protected $fillable = [
        'provider',
        'api_key',
        'version',
        'is_active',
    ];

    /**
     * Encrypt API key before saving
     */
    public function setApiKeyAttribute($value)
    {
        $this->attributes['api_key'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt API key when accessing
     */
    public function getApiKeyAttribute($value)
    {
        return Crypt::decryptString($value);
    }
}
