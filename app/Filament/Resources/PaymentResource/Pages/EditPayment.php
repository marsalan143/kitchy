<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Services\InvoiceService;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()?->can('payments.delete')),
            Actions\RestoreAction::make()
                ->visible(fn () => auth()->user()?->can('payments.delete')),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->invoice_id) {
            app(InvoiceService::class)->updateInvoiceBalance($this->record->invoice);
        }
    }
}
