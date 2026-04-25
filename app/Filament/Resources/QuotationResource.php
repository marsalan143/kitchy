<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuotationResource\Pages;
use App\Models\Quotation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;

class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('event_date')
                            ->required(),
                        Forms\Components\DatePicker::make('expiry_date'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Select::make('menu_item_id')
                                    ->relationship('menuItem', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $menuItem = \App\Models\MenuItem::find($state);
                                            if ($menuItem) {
                                                $set('unit_price', $menuItem->price_per_unit);
                                                $set('unit_type', $menuItem->unit_type);
                                            }
                                        }
                                    }),
                                Forms\Components\TextInput::make('unit_type')
                                    ->label('Unit')
                                    ->disabled()
                                    ->dehydrated(false),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, $get) {
                                        $quantity = $state ?? 0;
                                        $unitPrice = $get('unit_price') ?? 0;
                                        $set('total', $quantity * $unitPrice);
                                    }),
                                Forms\Components\TextInput::make('unit_price')
                                    ->numeric()
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('total')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->columns(5)
                            ->defaultItems(1)
                            ->required()
                            ->hiddenLabel(),
                    ]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Toggle::make('gst_enabled')
                            ->reactive(),
                        Forms\Components\TextInput::make('gst_percentage')
                            ->numeric()
                            ->default(0)
                            ->visible(fn ($get) => $get('gst_enabled'))
                            ->required(fn ($get) => $get('gst_enabled')),
                        Forms\Components\TextInput::make('discount')
                            ->numeric()
                            ->default(0)
                            ->visible(fn () => Auth::user()?->can('discounts.apply')),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Sent',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quotation_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('revision_number')
                    ->badge(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('event_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
                ...(Auth::user()?->isAdmin() ? [
                    Tables\Filters\SelectFilter::make('branch_id')
                        ->label('Branch')
                        ->relationship('branch', 'name')
                ] : []),
            ])
            ->actions([
                Tables\Actions\Action::make('download_pdf')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Quotation $record) {
                        // TODO: Implement PDF download
                        return redirect()->route('quotations.download', $record->id);
                    }),
                Tables\Actions\Action::make('send_whatsapp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->form([
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->default(fn (Quotation $record) => $record->customer->phone),
                    ])
                    ->action(function (Quotation $record, array $data) {
                        // TODO: Implement WhatsApp sending
                    }),
                Tables\Actions\Action::make('send_email')
                    ->icon('heroicon-o-envelope')
                    ->form([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->default(fn (Quotation $record) => $record->customer->email),
                    ])
                    ->action(function (Quotation $record, array $data) {
                        // TODO: Implement Email sending
                    }),
                Tables\Actions\Action::make('convert_to_order')
                    ->icon('heroicon-o-shopping-cart')
                    ->requiresConfirmation()
                    ->action(function (Quotation $record) {
                        // TODO: Implement conversion to order
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'edit' => Pages\EditQuotation::route('/{record}/edit'),
        ];
    }
}
