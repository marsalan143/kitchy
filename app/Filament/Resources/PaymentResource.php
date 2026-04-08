<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Finance';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options([
                        'customer' => 'Customer',
                        'supplier' => 'Supplier',
                    ])
                    ->required()
                    ->reactive(),
                Forms\Components\Select::make('reference_id')
                    ->label(fn ($get) => $get('type') === 'customer' ? 'Customer' : 'Supplier')
                    ->options(function ($get) {
                        if ($get('type') === 'customer') {
                            return \App\Models\Customer::pluck('name', 'id');
                        }
                        return \App\Models\Supplier::pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('invoice_id')
                    ->relationship('invoice', 'invoice_number')
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get) => $get('type') === 'customer'),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->step(0.01),
                Forms\Components\Select::make('method')
                    ->options([
                        'cash' => 'Cash',
                        'bank' => 'Bank',
                        'online' => 'Online',
                    ])
                    ->required(),
                Forms\Components\Select::make('direction')
                    ->options([
                        'advance' => 'Advance',
                        'partial' => 'Partial',
                        'full' => 'Full',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('payment_date')
                    ->required()
                    ->default(now()),
                Forms\Components\Textarea::make('notes'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('reference_name')
                    ->label('Customer/Supplier')
                    ->getStateUsing(function (Payment $record) {
                        if ($record->type === 'customer') {
                            return $record->customer?->name ?? 'N/A';
                        }
                        return $record->supplier?->name ?? 'N/A';
                    }),
                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('method')
                    ->badge(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type'),
                Tables\Filters\SelectFilter::make('method'),
                Tables\Filters\TrashedFilter::make(),
                ...(Auth::user()?->isAdmin() ? [
                    Tables\Filters\SelectFilter::make('branch_id')
                        ->label('Branch')
                        ->relationship('branch', 'name')
                ] : []),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => Auth::user()?->can('payments.delete')),
                Tables\Actions\RestoreAction::make()
                    ->visible(fn () => Auth::user()?->can('payments.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->can('payments.delete')),
                    Tables\Actions\RestoreBulkAction::make()
                        ->visible(fn () => Auth::user()?->can('payments.delete')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
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
