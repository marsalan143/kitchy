<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuItemResource\Pages;
use App\Models\MenuItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Menu';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('category')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('unit_type')
                    ->options([
                        'plate' => 'Plate',
                        'kg' => 'Kilogram',
                        'person' => 'Person',
                        'tray' => 'Tray',
                        'piece' => 'Piece',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('price_per_unit')
                    ->numeric()
                    ->required()
                    ->prefix('Rs')
                    ->step(0.01),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->directory('menu-items'),
                Forms\Components\Toggle::make('status')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('price_per_unit')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(fn () => MenuItem::distinct()->pluck('category', 'category')->toArray()),
                Tables\Filters\TernaryFilter::make('status'),
                ...(Auth::user()?->isAdmin() ? [
                    Tables\Filters\SelectFilter::make('branch_id')
                        ->label('Branch')
                        ->relationship('branch', 'name')
                ] : []),
            ])
            ->actions([
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
            'index' => Pages\ManageMenuItems::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Admin can filter by branch
        if (Auth::user()?->isAdmin()) {
            return $query;
        }
        
        return $query;
    }
}
