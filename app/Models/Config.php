<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'value'
    ];

    /**
     * Get config value by name
     */
    public static function getValue($name, $default = null)
    {
        $config = static::where('name', $name)->first();
        return $config ? $config->value : $default;
    }

    /**
     * Set config value by name
     */
    public static function setValue($name, $value)
    {
        return static::updateOrCreate(
            ['name' => $name],
            ['value' => $value]
        );
    }
}
