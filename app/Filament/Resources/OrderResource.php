<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Services\OrderService;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('quotation_id')
                    ->relationship('quotation', 'quotation_number')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\DatePicker::make('event_date')
                    ->required(),
                Forms\Components\TimePicker::make('food_preparation_start_time'),
                Forms\Components\TimePicker::make('food_delivery_time'),
                Forms\Components\TimePicker::make('event_start_time'),
                Forms\Components\Repeater::make('items')
                    ->columnSpanFull()
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
                                    }
                                }
                            }),
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
                    ->columns(4)
                    ->defaultItems(1)
                    ->required(),
                
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
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Select::make('delivery_status')
                    ->options([
                        'not_delivered' => 'Not Delivered',
                        'partially_delivered' => 'Partially Delivered',
                        'fully_delivered' => 'Fully Delivered',
                    ])
                    ->default('not_delivered'),
                Forms\Components\Textarea::make('delivery_notes'),
                ])
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->sortable(),
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
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('delivery_status')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('delivery_status'),
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
                Tables\Actions\Action::make('view_activities')
                    ->icon('heroicon-o-clock')
                    ->modalContent(fn (Order $record) => view('filament.resources.orders.activities', ['order' => $record]))
                    ->modalHeading('Order Activities'),
                Tables\Actions\Action::make('confirm')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === 'pending' && Auth::user()?->can('orders.confirm'))
                    ->action(function (Order $record) {
                        $record->update(['status' => 'confirmed']);
                        app(OrderService::class)->logActivity($record, 'Confirmed', 'Order confirmed');
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => Auth::user()?->can('orders.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->can('orders.delete')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
