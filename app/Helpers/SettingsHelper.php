<?php

namespace App\Helpers;

use App\Models\CrmSetting;
use Illuminate\Support\Facades\Cache;

class SettingsHelper
{
    /**
     * Get all company settings
     */
    public static function getCompanySettings()
    {
        return Cache::remember('company_settings', 3600, function () {
            return CrmSetting::where('category', 'company')->pluck('setting_value', 'setting_key')->toArray();
        });
    }

    /**
     * Get specific company setting
     */
    public static function getCompanySetting($key, $default = null)
    {
        $settings = self::getCompanySettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Get company logo URL
     */
    public static function getCompanyLogo()
    {
        $logo = self::getCompanySetting('company_logo');
        return $logo ? asset('assets/' . $logo) : null;
    }

    /**
     * Get company sub logo URL
     */
    public static function getCompanySubLogo()
    {
        $logo = self::getCompanySetting('company_sub_logo');
        return $logo ? asset('assets/' . $logo) : null;
    }

    /**
     * Get complete company information
     */
    public static function getCompanyInfo()
    {
        $settings = self::getCompanySettings();

        return [
            'name' => $settings['company_name'] ?? '',
            'address' => $settings['company_address'] ?? '',
            'pincode' => $settings['company_pincode'] ?? '',
            'state' => $settings['company_state'] ?? '',
            'country' => $settings['company_country'] ?? '',
            'registration_number' => $settings['company_registration_number'] ?? '',
            'phone' => $settings['company_phone'] ?? '',
            'email' => $settings['company_email'] ?? '',
            'website' => $settings['company_website'] ?? '',
            'logo' => self::getCompanyLogo(),
            'sub_logo' => self::getCompanySubLogo(),
        ];
    }

    /**
     * Clear settings cache
     */
    public static function clearCache()
    {
        Cache::forget('company_settings');
        Cache::forget('general_settings');
        Cache::forget('all_settings');
    }

    /**
     * Get setting by category and key
     */
    public static function get($category, $key, $default = null)
    {
        return CrmSetting::getValue($category, $key, $default);
    }

    /**
     * Set setting by category and key
     */
    public static function set($category, $key, $value, $type = 'text', $description = null)
    {
        self::clearCache();
        return CrmSetting::setValue($category, $key, $value, $type, $description);
    }

    /**
     * Get currency mapping based on currency and timezone
     */
    public static function getCurrencyTimezoneMapping()
    {
        return [
            // Malaysia - FIXED: Added both MYR and RM for backward compatibility
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
            $currency = self::get('general', 'currency', 'MYR');
        }
        if (!$timezone) {
            $timezone = self::get('general', 'time_zone', 'Asia/Kuala_Lumpur');
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
        $currency = self::get('general', 'currency', 'MYR');
        $timezone = self::get('general', 'time_zone', 'Asia/Kuala_Lumpur');
        $storedCountry = self::get('general', 'country', 'MY');

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

    public function getSettingCurrency($settingKey)
    {
        $setting = CrmSetting::where('setting_key', $settingKey)->first();
        return $setting ? $setting->setting_value : null;
    }
}