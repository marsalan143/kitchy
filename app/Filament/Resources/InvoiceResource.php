<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('order_id')
                    ->relationship('order', 'order_number')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('subtotal')
                    ->numeric()
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('gst_amount')
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('discount')
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('paid_amount')
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('balance_due')
                    ->numeric()
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'paid' => 'Paid',
                        'partial' => 'Partial',
                        'unpaid' => 'Unpaid',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.order_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->state(function (Invoice $record): float {
                        return (float) $record->subtotal + (float) $record->gst_amount - (float) $record->discount;
                    })
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance_due')
                    ->money('PKR')
                    ->sortable()
                    ->color(fn ($record) => $record->balance_due > 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TrashedFilter::make(),
                ...(Auth::user()?->isAdmin() ? [
                    Tables\Filters\SelectFilter::make('branch_id')
                        ->label('Branch')
                        ->relationship('branch', 'name')
                ] : []),
            ])
            ->actions([
                Tables\Actions\Action::make('download_pdf')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Invoice $record) {
                        return redirect()->route('invoices.download', $record->id);
                    }),
                Tables\Actions\Action::make('send_whatsapp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->form([
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->default(fn (Invoice $record) => $record->customer->phone),
                    ])
                    ->action(function (Invoice $record, array $data) {
                        // TODO: Implement WhatsApp sending
                    }),
                Tables\Actions\Action::make('send_email')
                    ->icon('heroicon-o-envelope')
                    ->form([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->default(fn (Invoice $record) => $record->customer->email),
                    ])
                    ->action(function (Invoice $record, array $data) {
                        // TODO: Implement Email sending
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => Auth::user()?->can('invoices.delete')),
                Tables\Actions\RestoreAction::make()
                    ->visible(fn () => Auth::user()?->can('invoices.restore')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->can('invoices.delete')),
                    Tables\Actions\RestoreBulkAction::make()
                        ->visible(fn () => Auth::user()?->can('invoices.restore')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInvoices::route('/'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }
}
