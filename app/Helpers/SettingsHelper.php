<?php

namespace App\Helpers;

use App\Models\CrmSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
class SettingsHelper
{
    /**
     * Get setting value by category and key
     */
    public static function getSetting($category, $key, $default = null)
    {
        $cacheKey = "setting_{$category}_{$key}";      
            $setting = DB::table('crm_settings')
                ->where('category', $category)
                ->where('setting_key', $key)
                ->value('setting_value');
        
            return $setting ?? $default;
     
    }

    /**
     * Get all settings for a category
     */
    public static function getCategorySettings($category)
    {
        $cacheKey = "settings_{$category}";
        
        return Cache::remember($cacheKey, 3600, function () use ($category) {
            return DB::table('crm_settings')
                ->where('category', $category)
                ->pluck('setting_value', 'setting_key')
                ->toArray();
        });
    }

    /**
     * Get company settings (alias for general category)
     */
    public static function getCompanySettings()
    {
        return self::getCategorySettings('general');
    }

    /**
     * Get specific company setting
     */
    public static function getCompanySetting($key, $default = null)
    {
        return self::getSetting('general', $key, $default);
    }

    /**
     * Get sales settings
     */
    public static function getSalesSettings()
    {
        return self::getCategorySettings('sales');
    }

    /**
     * Get terms and conditions
     */
    public static function getTermsAndConditions()
    {
        return self::getSetting('sales', 'terms_and_conditions', 'Default terms and conditions not set.');
    }

    /**
     * Get company logo URL
     */


    /**
     * Get company sub logo URL
     */
    public static function getCompanySubLogo()
    {
        $logo = self::getSetting('general', 'sub_logo');
        return $logo ? asset('assets/' . $logo) : null;
    }

    /**
     * Get complete company information with proper fallbacks
     */
    public static function getCompanyInfo()
    {
        $settings = self::getCompanySettings();

        return [
            'name' => $settings['name'] ?? 'Company Name Not Set',
            'address' => $settings['address'] ?? 'Address Not Set',
            'pincode' => $settings['pincode'] ?? 'Pincode Not Set',
            'state' => $settings['state'] ?? 'State Not Set',
            'country' => $settings['country'] ?? 'MY',
            'registration_number' => $settings['registration_number'] ?? 'Registration Number Not Set',
            'phone' => $settings['phone'] ?? 'Phone Not Set',
            'email' => $settings['email'] ?? 'Email Not Set',
            'website' => $settings['website'] ?? 'Website Not Set',
            'logo' => self::getCompanyLogo(),
            'sub_logo' => self::getCompanySubLogo(),
            'currency' => $settings['currency'] ?? 'MYR',
        ];
    }

    /**
     * Get complete company information for reports (with better formatting)
     */
    public static function getCompanyInfoForReports()
    {
        $info = self::getCompanyInfo();
        
        // Format registration number display
        if ($info['registration_number'] && $info['registration_number'] !== 'Registration Number Not Set') {
            $info['registration_display'] = '(' . $info['registration_number'] . ')';
        } else {
            $info['registration_display'] = '';
        }
        
        // Format address display
        $addressParts = [];
        if ($info['address'] && $info['address'] !== 'Address Not Set') {
            $addressParts[] = $info['address'];
        }
        if ($info['pincode'] && $info['pincode'] !== 'Pincode Not Set' && 
            $info['state'] && $info['state'] !== 'State Not Set') {
            $addressParts[] = $info['pincode'] . ', ' . $info['state'];
        }
        $info['address_formatted'] = implode('<br>', $addressParts);
        
        // Format contact display
        $contactParts = [];
        if ($info['phone'] && $info['phone'] !== 'Phone Not Set') {
            $contactParts[] = 'Tel: ' . $info['phone'];
        }
        if ($info['email'] && $info['email'] !== 'Email Not Set') {
            $contactParts[] = 'E-mail: ' . $info['email'];
        }
        if ($info['website'] && $info['website'] !== 'Website Not Set') {
            $contactParts[] = 'Visit: ' . $info['website'];
        }
        $info['contact_formatted'] = implode('<br>', $contactParts);
        
        return $info;
    }

    /**
     * Get setting with currency formatting
     */
    public static function getSettingCurrency($key)
    {
        $currency = self::getSetting('general', 'currency', 'MYR');
        $country = self::getSetting('general', 'country', 'MY');
        
        if ($key === 'currency') {
            return $currency;
        }
        
        if ($key === 'country') {
            return $country;
        }
        
        // Return currency symbol based on currency code
        $symbols = [
            'MYR' => 'RM',
            'RM' => 'RM', // For backward compatibility
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'INR' => '₹',
            'SGD' => 'S$',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'JPY' => '¥',
            'CNY' => '¥',
            'THB' => '฿',
            'PHP' => '₱',
            'IDR' => 'Rp',
            'AED' => 'د.إ'
        ];
        
        return $symbols[$currency] ?? $currency;
    }

    /**
     * Set setting by category and key
     */
    public static function set($category, $key, $value, $type = 'text', $description = null)
    {
        self::clearCache($category, $key);
        
        return DB::table('crm_settings')->updateOrInsert(
            ['category' => $category, 'setting_key' => $key],
            [
                'setting_value' => $value,
                'setting_type' => $type,
                'description' => $description,
                'updated_at' => now()
            ]
        );
    }

    /**
     * Get currency mapping based on currency and timezone
     */
    public static function getCurrencyTimezoneMapping()
    {
        return [
            // Malaysia
            'MYR' => [
                'country' => 'MY',
                'country_name' => 'Malaysia',
                'timezones' => ['Asia/Kuala_Lumpur'],
                'flag' => '🇲🇾',
                'symbol' => 'RM'
            ],
            'RM' => [ // For backward compatibility with existing data
                'country' => 'MY',
                'country_name' => 'Malaysia',
                'timezones' => ['Asia/Kuala_Lumpur'],
                'flag' => '🇲🇾',
                'symbol' => 'RM'
            ],
            // Singapore
            'SGD' => [
                'country' => 'SG',
                'country_name' => 'Singapore',
                'timezones' => ['Asia/Singapore'],
                'flag' => '🇸🇬',
                'symbol' => 'S$'
            ],
            // Indonesia
            'IDR' => [
                'country' => 'ID',
                'country_name' => 'Indonesia',
                'timezones' => ['Asia/Jakarta', 'Asia/Pontianak', 'Asia/Makassar', 'Asia/Jayapura'],
                'flag' => '🇮🇩',
                'symbol' => 'Rp'
            ],
            // Thailand
            'THB' => [
                'country' => 'TH',
                'country_name' => 'Thailand',
                'timezones' => ['Asia/Bangkok'],
                'flag' => '🇹🇭',
                'symbol' => '฿'
            ],
            // Philippines
            'PHP' => [
                'country' => 'PH',
                'country_name' => 'Philippines',
                'timezones' => ['Asia/Manila'],
                'flag' => '🇵🇭',
                'symbol' => '₱'
            ],
            // India
            'INR' => [
                'country' => 'IN',
                'country_name' => 'India',
                'timezones' => ['Asia/Kolkata'],
                'flag' => '🇮🇳',
                'symbol' => '₹'
            ],
            // United Arab Emirates
            'AED' => [
                'country' => 'AE',
                'country_name' => 'United Arab Emirates',
                'timezones' => ['Asia/Dubai'],
                'flag' => '🇦🇪',
                'symbol' => 'د.إ'
            ],
            // United States
            'USD' => [
                'country' => 'US',
                'country_name' => 'United States',
                'timezones' => ['America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles'],
                'flag' => '🇺🇸',
                'symbol' => '$'
            ],
            // United Kingdom
            'GBP' => [
                'country' => 'GB',
                'country_name' => 'United Kingdom',
                'timezones' => ['Europe/London'],
                'flag' => '🇬🇧',
                'symbol' => '£'
            ],
            // European Union
            'EUR' => [
                'country' => 'EU',
                'country_name' => 'European Union',
                'timezones' => ['Europe/Paris', 'Europe/Berlin', 'Europe/Madrid', 'Europe/Amsterdam'],
                'flag' => '🇪🇺',
                'symbol' => '€'
            ],
            // Japan
            'JPY' => [
                'country' => 'JP',
                'country_name' => 'Japan',
                'timezones' => ['Asia/Tokyo'],
                'flag' => '🇯🇵',
                'symbol' => '¥'
            ],
            // Australia
            'AUD' => [
                'country' => 'AU',
                'country_name' => 'Australia',
                'timezones' => ['Australia/Sydney', 'Australia/Melbourne', 'Australia/Brisbane', 'Australia/Perth'],
                'flag' => '🇦🇺',
                'symbol' => 'A$'
            ],
            // Canada
            'CAD' => [
                'country' => 'CA',
                'country_name' => 'Canada',
                'timezones' => ['America/Toronto', 'America/Vancouver', 'America/Edmonton'],
                'flag' => '🇨🇦',
                'symbol' => 'C$'
            ],
            // China
            'CNY' => [
                'country' => 'CN',
                'country_name' => 'China',
                'timezones' => ['Asia/Shanghai'],
                'flag' => '🇨🇳',
                'symbol' => '¥'
            ],
        ];
    }

    /**
     * Auto-detect country based on currency and timezone
     */
    public static function autoDetectCountry($currency = null, $timezone = null)
    {
        $mapping = self::getCurrencyTimezoneMapping();

        // If no parameters provided, get from settings
        if (!$currency) {
            $currency = self::getSetting('general', 'currency', 'MYR');
        }
        if (!$timezone) {
            $timezone = self::getSetting('general', 'time_zone', 'Asia/Kuala_Lumpur');
        }

        // First, try to match by currency
        if (isset($mapping[$currency])) {
            $countryData = $mapping[$currency];

            // Check if timezone also matches for this currency
            if (in_array($timezone, $countryData['timezones'])) {
                return $countryData;
            }

            // If timezone doesn't match but currency does, still return currency match
            return $countryData;
        }

        // If currency not found, try to match by timezone
        foreach ($mapping as $currencyCode => $data) {
            if (in_array($timezone, $data['timezones'])) {
                return $data;
            }
        }

        // Default fallback to Malaysia
        return [
            'country' => 'MY',
            'country_name' => 'Malaysia',
            'timezones' => ['Asia/Kuala_Lumpur'],
            'flag' => '🇲🇾',
            'symbol' => 'RM'
        ];
    }

    /**
     * Update country setting based on currency and timezone
     */
    public static function updateCountryFromCurrencyTimezone($currency, $timezone)
    {
        $countryData = self::autoDetectCountry($currency, $timezone);

        // Update the country setting
        self::set('general', 'country', $countryData['country'], 'text', 'Auto-detected country based on currency and timezone');

        return $countryData;
    }

    /**
     * Get current country information
     */
    public static function getCurrentCountryInfo()
    {
        $currency = self::getSetting('general', 'currency', 'MYR');
        $timezone = self::getSetting('general', 'time_zone', 'Asia/Kuala_Lumpur');
        $storedCountry = self::getSetting('general', 'country', 'MY');

        $detectedCountry = self::autoDetectCountry($currency, $timezone);

        // If stored country doesn't match detected, update it
        if ($storedCountry !== $detectedCountry['country']) {
            self::updateCountryFromCurrencyTimezone($currency, $timezone);
        }

        return $detectedCountry;
    }

    /**
     * Get supported currencies list
     */
    public static function getSupportedCurrencies()
    {
        $mapping = self::getCurrencyTimezoneMapping();
        $currencies = [];

        foreach ($mapping as $code => $data) {
            // Skip duplicate entries (like RM which is same as MYR)
            if ($code === 'RM') continue;
            
            $currencies[$code] = $data['flag'] . ' ' . $code . ' (' . $data['symbol'] . ') - ' . $data['country_name'];
        }

        return $currencies;
    }

    /**
     * Clear settings cache
     */
    public static function clearCache($category = null, $key = null)
    {
        if ($category && $key) {
            Cache::forget("setting_{$category}_{$key}");
        } elseif ($category) {
            Cache::forget("settings_{$category}");
        } else {
            // Clear all settings cache
            $categories = ['general', 'sales', 'purchase', 'service', 'subscription', 'hr', 'email', 'company'];
            foreach ($categories as $cat) {
                Cache::forget("settings_{$cat}");
                // Also clear individual setting caches for this category
                Cache::flush(); // Alternative: more targeted cache clearing if needed
            }
        }
    }

    /**
     * Alias for getSetting (backward compatibility)
     */
    public static function get($category, $key, $default = null)
    {
        return self::getSetting($category, $key, $default);
    }
  public static function getCompanyLogo()
    {
        $logo = self::getSetting('general', 'logo');
        return $logo ? asset('assets/' . $logo) : null;
    }

public static function getCloudServerFeatures()
{
    $features = self::getSetting('sales', 'cloud_server_hosting', '[]');
    return json_decode($features, true) ?: [];
}

/**
 * Set cloud server hosting features
 */
public static function setCloudServerFeatures($features)
{
    $jsonData = json_encode($features);
    return self::set('sales', 'cloud_server_hosting', $jsonData, 'json', 'Cloud server hosting features and descriptions');
}

/**
 * Add a single cloud server feature
 */
public static function addCloudServerFeature($feature, $description)
{
    $currentFeatures = self::getCloudServerFeatures();
    $currentFeatures[] = [
        'feature' => $feature,
        'description' => $description,
        'id' => uniqid(), // Unique identifier for frontend manipulation
        'created_at' => now()->toISOString()
    ];
    
    return self::setCloudServerFeatures($currentFeatures);
}

/**
 * Remove a cloud server feature by ID
 */
public static function removeCloudServerFeature($featureId)
{
    $currentFeatures = self::getCloudServerFeatures();
    $filteredFeatures = array_filter($currentFeatures, function($item) use ($featureId) {
        return $item['id'] !== $featureId;
    });
    
    return self::setCloudServerFeatures(array_values($filteredFeatures));
}
}