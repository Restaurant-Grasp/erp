<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CrmSetting;
use App\Helpers\SettingsHelper;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    // Settings index - display all settings grouped by category
    public function index()
    {
        $settings = CrmSetting::orderBy('category', 'asc')->orderBy('id', 'asc')->get();
        
        // Group settings by category
        $settingsByCategory = $settings->groupBy('category');
        
        // Get all unique categories for tabs
        $categories = $settingsByCategory->keys()->toArray();
        
        // Get current country information
        $currentCountryInfo = SettingsHelper::getCurrentCountryInfo();
        
        return view('settings.index', compact('settingsByCategory', 'categories', 'currentCountryInfo'));
    }

    // Update settings
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:1000',
            'files.*' => 'nullable|image|mimes:jpg,jpeg,png,svg,gif|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $currencyChanged = false;
            $timezoneChanged = false;
            $newCurrency = null;
            $newTimezone = null;

            foreach ($request->settings as $settingId => $value) {
                $setting = CrmSetting::findOrFail($settingId);
                
                // Track currency and timezone changes
                if ($setting->setting_key === 'currency' && $setting->setting_value !== $value) {
                    $currencyChanged = true;
                    $newCurrency = $value;
                }
                
                if ($setting->setting_key === 'time_zone' && $setting->setting_value !== $value) {
                    $timezoneChanged = true;
                    $newTimezone = $value;
                }
                
                // Handle file uploads
                if ($setting->setting_type === 'file' && $request->hasFile("files.{$settingId}")) {
                    $file = $request->file("files.{$settingId}");
                    $filename = $this->handleFileUpload($file, $setting);
                    $value = $filename;
                }
                
                // Handle different setting types
                switch ($setting->setting_type) {
                    case 'boolean':
                        $value = $value ? '1' : '0';
                        break;
                    case 'number':
                        $value = is_numeric($value) ? $value : '0';
                        break;
                    case 'file':
                        // Keep existing file if no new file uploaded
                        if (!$request->hasFile("files.{$settingId}")) {
                            continue 2; // Skip this iteration
                        }
                        break;
                    default:
                        // text, json - keep as is
                        break;
                }
                
                $setting->update([
                    'setting_value' => $value,
                    'updated_at' => now()
                ]);
            }

            // Auto-update country if currency or timezone changed
            if ($currencyChanged || $timezoneChanged) {
                // Get current values if only one changed
                if (!$newCurrency) {
                    $newCurrency = SettingsHelper::get('general', 'currency', 'MYR');
                }
                if (!$newTimezone) {
                    $newTimezone = SettingsHelper::get('general', 'time_zone', 'Asia/Kuala_Lumpur');
                }
                
                $countryData = SettingsHelper::updateCountryFromCurrencyTimezone($newCurrency, $newTimezone);
                
                // Add success message with country info
                $countryMessage = " Country automatically updated to {$countryData['flag']} {$countryData['country_name']} based on currency and timezone.";
            }

            // Clear settings cache
            SettingsHelper::clearCache();

            DB::commit();
            
            $successMessage = 'Settings updated successfully.';
            if (isset($countryMessage)) {
                $successMessage .= $countryMessage;
            }
            
            return redirect()->route('settings.index')->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating settings: ' . $e->getMessage());
        }
    }

    // Handle file upload for settings
    private function handleFileUpload($file, $setting)
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = $setting->setting_key . '_' . time() . '.' . $extension;
        
        $folderPath = public_path('assets/settings');
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }
        
        // Delete old file if exists
        if ($setting->setting_value && file_exists(public_path('assets/' . $setting->setting_value))) {
            unlink(public_path('assets/' . $setting->setting_value));
        }
        
        $file->move($folderPath, $filename);
        
        return 'settings/' . $filename;
    }

    // Create new setting
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:50',
            'setting_key' => 'required|string|max:100',
            'setting_value' => 'nullable|string|max:1000',
            'setting_type' => 'required|in:text,number,boolean,json,file',
            'description' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Check if setting already exists
            $existingSetting = CrmSetting::where('category', $request->category)
                                       ->where('setting_key', $request->setting_key)
                                       ->first();
            
            if ($existingSetting) {
                return redirect()->back()->with('error', 'Setting already exists for this category and key.');
            }

            CrmSetting::create([
                'category' => $request->category,
                'setting_key' => $request->setting_key,
                'setting_value' => $request->setting_value,
                'setting_type' => $request->setting_type,
                'description' => $request->description,
            ]);

            // Clear cache
            SettingsHelper::clearCache();

            DB::commit();
            return redirect()->route('settings.index')->with('success', 'Setting created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error creating setting: ' . $e->getMessage());
        }
    }

    // Delete setting
    public function destroy(CrmSetting $setting)
    {
        try {
            $setting->delete();
            SettingsHelper::clearCache();
            return redirect()->route('settings.index')->with('success', 'Setting deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting setting: ' . $e->getMessage());
        }
    }

    // Get setting value by category and key (helper method)
    public static function getValue($category, $key, $default = null)
    {
        $setting = CrmSetting::where('category', $category)
                            ->where('setting_key', $key)
                            ->first();
        
        return $setting ? $setting->setting_value : $default;
    }

    // AJAX endpoint to get country information based on currency and timezone
    public function getCountryInfo(Request $request)
    {
        $currency = $request->input('currency');
        $timezone = $request->input('timezone');
        
        if (!$currency && !$timezone) {
            return response()->json(['error' => 'Currency or timezone required'], 400);
        }
        
        $countryData = SettingsHelper::autoDetectCountry($currency, $timezone);
        
        return response()->json($countryData);
    }

    // Get supported currencies for dropdown
    public function getSupportedCurrencies()
    {
        $currencies = SettingsHelper::getSupportedCurrencies();
        return response()->json($currencies);
    }
}