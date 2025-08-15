<?php

namespace App\Services;

use App\Models\Entry;
use App\Models\EntryItem;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\Ledger;
use App\Helpers\SettingsHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AccountMigrationService
{
    /**
     * Entry type constants
     */
    const ENTRY_TYPE_RECEIPT = 1;
    const ENTRY_TYPE_PAYMENT = 2;
    const ENTRY_TYPE_JOURNAL = 4;
    
    /**
     * Invoice type constants
     */
    const INV_TYPE_SALES_INVOICE = 1;
    const INV_TYPE_PURCHASE_INVOICE = 2;
    const INV_TYPE_SALES_PAYMENT = 3;
    const INV_TYPE_PURCHASE_PAYMENT = 4;

    /**
     * Migrate sales invoice to accounting system
     */
    public function migrateSalesInvoice(SalesInvoice $invoice)
    {
        if ($invoice->account_migration) {
            Log::info("Sales invoice {$invoice->invoice_no} already migrated");
            return;
        }

        DB::beginTransaction();
        try {
            // Get required ledgers
            $customerLedgerId = $invoice->customer->ledger_id;
            $salesLedgerId = SettingsHelper::getSetting('sales', 'sales_ledger_id');
            $discountLedgerId = SettingsHelper::getSetting('sales', 'discount_ledger_id');
            
            if (!$customerLedgerId || !$salesLedgerId) {
                throw new \Exception("Required ledgers not configured for sales invoice migration");
            }

            // Generate journal entry number
            $entryNumber = $this->generateEntryNumber('JOR');
            
            // Create main journal entry
            $entry = Entry::create([
                'entry_code' => $entryNumber,
                'number' => $entryNumber,
                'entrytype_id' => self::ENTRY_TYPE_JOURNAL,
                'date' => $invoice->invoice_date,
                'dr_total' => $invoice->total_amount,
                'cr_total' => $invoice->total_amount,
                'notes' => "Sales Invoice: {$invoice->invoice_no}",
                'inv_type' => self::INV_TYPE_SALES_INVOICE,
                'inv_id' => $invoice->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $entryItems = [];

            // 1. Sales Revenue - Credit
            $entryItems[] = [
                'entry_id' => $entry->id,
                'ledger_id' => $salesLedgerId,
                'amount' => $invoice->subtotal,
                'dc' => 'C', // Credit
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 2. Discount - Debit (if any)
            if ($invoice->discount_amount > 0 && $discountLedgerId) {
                $entryItems[] = [
                    'entry_id' => $entry->id,
                    'ledger_id' => $discountLedgerId,
                    'amount' => $invoice->discount_amount,
                    'dc' => 'D', // Debit
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // 3. Tax Ledgers - Credit (for each unique tax)
            $taxAmounts = $this->calculateSalesInvoiceTaxAmounts($invoice);
            foreach ($taxAmounts as $taxLedgerId => $taxAmount) {
                if ($taxAmount > 0) {
                    $entryItems[] = [
                        'entry_id' => $entry->id,
                        'ledger_id' => $taxLedgerId,
                        'amount' => $taxAmount,
                        'dc' => 'C', // Credit
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // 4. Customer Ledger - Debit (total amount)
            $entryItems[] = [
                'entry_id' => $entry->id,
                'ledger_id' => $customerLedgerId,
                'amount' => $invoice->total_amount,
                'dc' => 'D', // Debit
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert all entry items
            EntryItem::insert($entryItems);

            // Update invoice with entry reference and migration status
            $invoice->update([
                'entry_id' => $entry->id,
                'account_migration' => 1
            ]);

            DB::commit();
            Log::info("Sales invoice {$invoice->invoice_no} migrated successfully with entry {$entryNumber}");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to migrate sales invoice {$invoice->invoice_no}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Migrate purchase invoice to accounting system  
     */
    public function migratePurchaseInvoice(PurchaseInvoice $invoice)
    {
        if ($invoice->account_migration) {
            Log::info("Purchase invoice {$invoice->invoice_no} already migrated");
            return;
        }

        DB::beginTransaction();
        try {
            // Get required ledgers
            $vendorLedgerId = $invoice->vendor->ledger_id;
            $purchaseLedgerId = SettingsHelper::getSetting('purchase', 'purchase_ledger_id');
            
            if (!$vendorLedgerId || !$purchaseLedgerId) {
                throw new \Exception("Required ledgers not configured for purchase invoice migration");
            }

            // Generate journal entry number
            $entryNumber = $this->generateEntryNumber('JOR');
            
            // Create main journal entry
            $entry = Entry::create([
                'entry_code' => $entryNumber,
                'number' => $entryNumber,
                'entrytype_id' => self::ENTRY_TYPE_JOURNAL,
                'date' => $invoice->invoice_date,
                'dr_total' => $invoice->total_amount,
                'cr_total' => $invoice->total_amount,
                'notes' => "Purchase Invoice: {$invoice->invoice_no}",
                'inv_type' => self::INV_TYPE_PURCHASE_INVOICE,
                'inv_id' => $invoice->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $entryItems = [];

            // 1. Purchase Expense - Debit (total amount including tax)
            $entryItems[] = [
                'entry_id' => $entry->id,
                'ledger_id' => $purchaseLedgerId,
                'amount' => $invoice->total_amount,
                'dc' => 'D', // Debit
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 2. Vendor Ledger - Credit (total amount)
            $entryItems[] = [
                'entry_id' => $entry->id,
                'ledger_id' => $vendorLedgerId,
                'amount' => $invoice->total_amount,
                'dc' => 'C', // Credit
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert all entry items
            EntryItem::insert($entryItems);

            // Update invoice with entry reference and migration status
            $invoice->update([
                'entry_id' => $entry->id,
                'account_migration' => 1
            ]);

            DB::commit();
            Log::info("Purchase invoice {$invoice->invoice_no} migrated successfully with entry {$entryNumber}");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to migrate purchase invoice {$invoice->invoice_no}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Migrate sales invoice payment to accounting system
     */
    public function migrateSalesInvoicePayment($payment)
    {
        if ($payment->account_migration) {
            Log::info("Sales payment {$payment->id} already migrated");
            return;
        }

        DB::beginTransaction();
        try {
            // Get required ledgers
            $customerLedgerId = $payment->invoice->customer->ledger_id;
            $paymentModeLedgerId = $payment->paymentMode->ledger_id;
            
            if (!$customerLedgerId || !$paymentModeLedgerId) {
                throw new \Exception("Required ledgers not configured for sales payment migration");
            }

            // Generate receipt entry number
            $entryNumber = $this->generateEntryNumber('REC');
            
            // Create receipt entry
            $entry = Entry::create([
                'entry_code' => $entryNumber,
                'number' => $entryNumber,
                'entrytype_id' => self::ENTRY_TYPE_RECEIPT,
                'date' => $payment->payment_date,
                'dr_total' => $payment->paid_amount,
                'cr_total' => $payment->paid_amount,
                'notes' => "Sales Payment for Invoice: {$payment->invoice->invoice_no}",
                'inv_type' => self::INV_TYPE_SALES_PAYMENT,
                'inv_id' => $payment->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $entryItems = [];

            // 1. Customer Ledger - Credit (payment received)
            $entryItems[] = [
                'entry_id' => $entry->id,
                'ledger_id' => $customerLedgerId,
                'amount' => $payment->paid_amount,
                'dc' => 'C', // Credit
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 2. Payment Mode Ledger - Debit (cash/bank account)
            $entryItems[] = [
                'entry_id' => $entry->id,
                'ledger_id' => $paymentModeLedgerId,
                'amount' => $payment->paid_amount,
                'dc' => 'D', // Debit
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert all entry items
            EntryItem::insert($entryItems);

            // Update payment with migration status
            $payment->update(['account_migration' => 1]);

            DB::commit();
            Log::info("Sales payment {$payment->id} migrated successfully with entry {$entryNumber}");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to migrate sales payment {$payment->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Migrate purchase invoice payment to accounting system
     */
    public function migratePurchaseInvoicePayment($payment)
    {
        if ($payment->account_migration) {
            Log::info("Purchase payment {$payment->id} already migrated");
            return;
        }

        DB::beginTransaction();
        try {
            // Get required ledgers
            $vendorLedgerId = $payment->invoice->vendor->ledger_id;
            $paymentModeLedgerId = $payment->paymentMode->ledger_id;
            
            if (!$vendorLedgerId || !$paymentModeLedgerId) {
                throw new \Exception("Required ledgers not configured for purchase payment migration");
            }

            // Generate payment entry number
            $entryNumber = $this->generateEntryNumber('PAY');
            
            // Create payment entry
            $entry = Entry::create([
                'entry_code' => $entryNumber,
                'number' => $entryNumber,
                'entrytype_id' => self::ENTRY_TYPE_PAYMENT,
                'date' => $payment->payment_date,
                'dr_total' => $payment->paid_amount,
                'cr_total' => $payment->paid_amount,
                'notes' => "Purchase Payment for Invoice: {$payment->invoice->invoice_no}",
                'inv_type' => self::INV_TYPE_PURCHASE_PAYMENT,
                'inv_id' => $payment->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $entryItems = [];

            // 1. Vendor Ledger - Debit (payment made)
            $entryItems[] = [
                'entry_id' => $entry->id,
                'ledger_id' => $vendorLedgerId,
                'amount' => $payment->paid_amount,
                'dc' => 'D', // Debit
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 2. Payment Mode Ledger - Credit (cash/bank account)
            $entryItems[] = [
                'entry_id' => $entry->id,
                'ledger_id' => $paymentModeLedgerId,
                'amount' => $payment->paid_amount,
                'dc' => 'C', // Credit
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert all entry items
            EntryItem::insert($entryItems);

            // Update payment with migration status
            $payment->update(['account_migration' => 1]);

            DB::commit();
            Log::info("Purchase payment {$payment->id} migrated successfully with entry {$entryNumber}");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to migrate purchase payment {$payment->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate entry number with format: [TYPE][YYMMDD][SEQUENCE]
     */
    private function generateEntryNumber($type)
    {
        $date = Carbon::now();
        $dateString = $date->format('ymd'); // YYMMDD format
        
        // Get the latest entry number for today with this type
        $latestEntry = Entry::where('entry_code', 'like', $type . $dateString . '%')
                           ->orderBy('entry_code', 'desc')
                           ->first();
        
        if ($latestEntry) {
            // Extract sequence number and increment
            $lastSequence = intval(substr($latestEntry->entry_code, -5));
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }
        
        return $type . $dateString . str_pad($newSequence, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate tax amounts grouped by tax ledger for sales invoice
     */
    private function calculateSalesInvoiceTaxAmounts(SalesInvoice $invoice)
    {
        $taxAmounts = [];
        
        foreach ($invoice->items as $item) {
            if ($item->tax_id && $item->tax_amount > 0) {
                $tax = $item->tax;
                if ($tax && $tax->ledger_id) {
                    if (!isset($taxAmounts[$tax->ledger_id])) {
                        $taxAmounts[$tax->ledger_id] = 0;
                    }
                    $taxAmounts[$tax->ledger_id] += $item->tax_amount;
                }
            }
        }
        
        return $taxAmounts;
    }

    /**
     * Validate required ledgers exist
     */
    public function validateLedgersExist()
    {
        $errors = [];
        
        // Check sales ledgers
        $salesLedgerId = SettingsHelper::getSetting('sales', 'sales_ledger_id');
        if (!$salesLedgerId || !Ledger::find($salesLedgerId)) {
            $errors[] = 'Sales ledger not configured or does not exist';
        }
        
        $discountLedgerId = SettingsHelper::getSetting('sales', 'discount_ledger_id');
        if ($discountLedgerId && !Ledger::find($discountLedgerId)) {
            $errors[] = 'Discount ledger configured but does not exist';
        }
        
        // Check purchase ledgers
        $purchaseLedgerId = SettingsHelper::getSetting('purchase', 'purchase_ledger_id');
        if (!$purchaseLedgerId || !Ledger::find($purchaseLedgerId)) {
            $errors[] = 'Purchase ledger not configured or does not exist';
        }
        
        return $errors;
    }
}