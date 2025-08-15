<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AccountMigrationService;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoicePayment;
use App\Models\PurchaseInvoicePayment;

class TestAccountMigration extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:account-migration 
                            {type : Type of migration to test (sales-invoice|purchase-invoice|sales-payment|purchase-payment)}
                            {id : ID of the record to migrate}
                            {--validate : Only validate ledger configuration}';

    /**
     * The console command description.
     */
    protected $description = 'Test account migration for invoices and payments';

    protected $accountMigrationService;

    /**
     * Create a new command instance.
     */
    public function __construct(AccountMigrationService $accountMigrationService)
    {
        parent::__construct();
        $this->accountMigrationService = $accountMigrationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $id = $this->argument('id');
        $validateOnly = $this->option('validate');

        // Validate ledger configuration first
        $this->info('Validating ledger configuration...');
        $errors = $this->accountMigrationService->validateLedgersExist();
        
        if (!empty($errors)) {
            $this->error('Ledger validation failed:');
            foreach ($errors as $error) {
                $this->error('- ' . $error);
            }
            
            if ($validateOnly) {
                return 1;
            }
            
            $this->warn('Continuing despite validation errors...');
        } else {
            $this->info('✓ All required ledgers are configured');
        }

        if ($validateOnly) {
            return 0;
        }

        try {
            switch ($type) {
                case 'sales-invoice':
                    $this->migrateSalesInvoice($id);
                    break;
                case 'purchase-invoice':
                    $this->migratePurchaseInvoice($id);
                    break;
                case 'sales-payment':
                    $this->migrateSalesPayment($id);
                    break;
                case 'purchase-payment':
                    $this->migratePurchasePayment($id);
                    break;
                default:
                    $this->error('Invalid type. Use: sales-invoice, purchase-invoice, sales-payment, or purchase-payment');
                    return 1;
            }

            $this->info('Migration completed successfully!');
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Migrate sales invoice
     */
    private function migrateSalesInvoice($id)
    {
        $invoice = SalesInvoice::with(['customer', 'items.tax'])->find($id);
        
        if (!$invoice) {
            throw new \Exception("Sales invoice with ID {$id} not found");
        }

        $this->info("Migrating Sales Invoice: {$invoice->invoice_no}");
        $this->info("Customer: {$invoice->customer->company_name}");
        $this->info("Total Amount: {$invoice->total_amount}");
        $this->info("Subtotal: {$invoice->subtotal}");
        $this->info("Discount: {$invoice->discount_amount}");
        $this->info("Tax: {$invoice->tax_amount}");

        if ($invoice->account_migration) {
            $this->warn('Invoice already migrated to accounting');
            return;
        }

        $this->accountMigrationService->migrateSalesInvoice($invoice);
        $this->info('✓ Sales invoice migrated successfully');
    }

    /**
     * Migrate purchase invoice
     */
    private function migratePurchaseInvoice($id)
    {
        $invoice = PurchaseInvoice::with(['vendor'])->find($id);
        
        if (!$invoice) {
            throw new \Exception("Purchase invoice with ID {$id} not found");
        }

        $this->info("Migrating Purchase Invoice: {$invoice->invoice_no}");
        $this->info("Vendor: {$invoice->vendor->company_name}");
        $this->info("Total Amount: {$invoice->total_amount}");

        if ($invoice->account_migration) {
            $this->warn('Invoice already migrated to accounting');
            return;
        }

        $this->accountMigrationService->migratePurchaseInvoice($invoice);
        $this->info('✓ Purchase invoice migrated successfully');
    }

    /**
     * Migrate sales payment
     */
    private function migrateSalesPayment($id)
    {
        $payment = SalesInvoicePayment::with(['invoice.customer', 'paymentMode'])->find($id);
        
        if (!$payment) {
            throw new \Exception("Sales payment with ID {$id} not found");
        }

        $this->info("Migrating Sales Payment: ID {$payment->id}");
        $this->info("Invoice: {$payment->invoice->invoice_no}");
        $this->info("Customer: {$payment->invoice->customer->company_name}");
        $this->info("Amount: {$payment->paid_amount}");
        $this->info("Payment Mode: {$payment->paymentMode->name}");

        if ($payment->account_migration) {
            $this->warn('Payment already migrated to accounting');
            return;
        }

        $this->accountMigrationService->migrateSalesInvoicePayment($payment);
        $this->info('✓ Sales payment migrated successfully');
    }

    /**
     * Migrate purchase payment
     */
    private function migratePurchasePayment($id)
    {
        $payment = PurchaseInvoicePayment::with(['invoice.vendor', 'paymentMode'])->find($id);
        
        if (!$payment) {
            throw new \Exception("Purchase payment with ID {$id} not found");
        }

        $this->info("Migrating Purchase Payment: ID {$payment->id}");
        $this->info("Invoice: {$payment->invoice->invoice_no}");
        $this->info("Vendor: {$payment->invoice->vendor->company_name}");
        $this->info("Amount: {$payment->paid_amount}");
        $this->info("Payment Mode: {$payment->paymentMode->name}");

        if ($payment->account_migration) {
            $this->warn('Payment already migrated to accounting');
            return;
        }

        $this->accountMigrationService->migratePurchaseInvoicePayment($payment);
        $this->info('✓ Purchase payment migrated successfully');
    }
}