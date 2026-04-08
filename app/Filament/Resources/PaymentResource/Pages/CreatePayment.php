<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\InvoiceService;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function afterCreate(): void
    {
        if ($this->record->invoice_id) {
            app(InvoiceService::class)->updateInvoiceBalance($this->record->invoice);
        }
    }
}
