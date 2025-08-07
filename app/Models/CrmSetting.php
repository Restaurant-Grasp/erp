<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmSetting extends Model
{
    use HasFactory;

    protected $table = 'crm_settings';

    protected $fillable = [
        'category',
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Get setting value with type casting
    public function getTypedValueAttribute()
    {
        switch ($this->setting_type) {
            case 'boolean':
                return (bool) $this->setting_value;
            case 'number':
                return is_numeric($this->setting_value) ? (float) $this->setting_value : 0;
            case 'json':
                return json_decode($this->setting_value, true);
            default:
                return $this->setting_value;
        }
    }

    // Format setting key for display
    public function getDisplayKeyAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->setting_key));
    }

    // Get all settings grouped by category
    public static function getByCategory()
    {
        return self::orderBy('category', 'asc')
                  ->orderBy('setting_key', 'asc')
                  ->get()
                  ->groupBy('category');
    }

    // Get specific setting value
    public static function getValue($category, $key, $default = null)
    {
        $setting = self::where('category', $category)
                      ->where('setting_key', $key)
                      ->first();
        
        if (!$setting) {
            return $default;
        }

        return $setting->typed_value;
    }

    // Set specific setting value
    public static function setValue($category, $key, $value, $type = 'text', $description = null)
    {
        return self::updateOrCreate(
            ['category' => $category, 'setting_key' => $key],
            [
                'setting_value' => $value,
                'setting_type' => $type,
                'description' => $description
            ]
        );
    }
}