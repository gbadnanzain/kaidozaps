<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Trans;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\ReplicateAction;
use App\Filament\Exports\TransExporter;
use Filament\Tables\Actions\BulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Filters\SelectFilter;
//use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\TransResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransResource\RelationManagers;

class TransResource extends Resource
{
    protected static ?string $model = Trans::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(300) // Mengatur jumlah kolom dalam grid
                    ->schema([
                        Forms\Components\TextInput::make('ID')
                            ->label('ID')
                            ->disabled()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(6),
                        Forms\Components\TextInput::make('SO_No')
                            ->label('SO No.')
                            ->required()
                            ->columnSpan(6)
                        /*->reactive()

                             ->afterStateUpdated(function (callable $set, $state) {
                                // Set nilai SO_ID berdasarkan perubahan di SO_No
                                $set('SO_ID', substr($state, 0, 4) . '/' . substr($state, -3));
                            }) */,

                        Forms\Components\TextInput::make('SO_ID')
                            ->label('SO ID')
                            ->required()
                            ->columnSpan(6),
                        //->extraAttributes(['style' => 'width: 100%;']),
                        Forms\Components\DatePicker::make('SO_Date')
                            ->label('SO Date')
                            ->required()
                            ->placeholder('Select a date')
                            ->displayFormat('Y-m-d')
                            ->columnSpan(4),
                        Forms\Components\TextInput::make('SO_DebtorID')
                            ->label('Debt. ID')
                            ->required()
                            ->columnSpan(4),
                        Forms\Components\DatePicker::make('SO_Target_CompletionDatePerPO')
                            ->label('Target Compl./PO')
                            ->required()
                            ->placeholder('Select a date')
                            ->displayFormat('Y-m-d')
                            ->columnSpan(8),
                        Forms\Components\TextInput::make('SO_DebtorName')
                            ->label('Debtor Name')
                            ->required()
                            ->columnSpan(6),
                        Forms\Components\TextInput::make('SO_Agent')
                            ->label('Agent')
                            ->required()
                            ->columnSpan(5),
                        Forms\Components\TextInput::make('SO_CustPONo')
                            ->label('Cust. PO No')
                            ->columnSpan(10),
                        TextInput::make('SO_Item_Description')
                            ->label('Description')
                            ->required()
                            ->columnSpan(15),
                        TextInput::make('SO_LiftNo')
                            ->label('Lift No')
                            ->columnSpan(5),
                        TextInput::make('SO_Qty')
                            ->label('Qty')
                            ->columnSpan(5),
                        TextInput::make('SO_UOM')
                            ->label('UOM')
                            ->columnSpan(5),
                        TextInput::make('SO_OIR_SentTo_Finance')
                            ->label('OIR Sent to Fin.')
                            ->columnSpan(6),
                        TextInput::make('SO_RQ_No')
                            ->label('RQ No.')
                            ->columnSpan(4),
                        TextInput::make('PCH_PO_to_TELC_MS')
                            ->label('PO to TELC MS')
                            ->columnSpan(6),
                        DatePicker::make('PCH_ETA')
                            ->label('ETA')
                            ->columnSpan(7),
                        DatePicker::make('PCH_PO_ReceiveDate')
                            ->label('PO Receive Date')
                            ->columnSpan(7),
                        TextInput::make('PCH_Transfered_Qty')
                            ->label('Transf. Qty')
                            ->columnSpan(8),
                        TextInput::make('PCH_Doc')
                            ->label('Purchase Doc.')
                            ->columnSpan(6),
                        DatePicker::make('PCH_Date')
                            ->label('Purchase Date')
                            ->columnSpan(7),
                        DatePicker::make('PCH_Inform_Finance_on')
                            ->label('Inform Fin. on')
                            ->columnSpan(7),
                        TextInput::make('PCH_Remark')
                            ->label('Purchase Remark')
                            ->columnSpan(8),
                        TextInput::make('MTC_RQ_No')
                            ->label('MTC Req. No.')
                            ->columnSpan(6),
                        DatePicker::make('MTC_RQ_Date')
                            ->label('MTC Req. Date')
                            ->columnSpan(7),
                        TextInput::make('MTC_Job_Done')
                            ->label('Job Done')
                            ->columnSpan(8),
                        DatePicker::make('MTC_Target_Completion')
                            ->label('Target Compl. Date')
                            ->columnSpan(8),
                        TextInput::make('MTC_SBK')
                            ->label('SBK')
                            ->columnSpan(5),
                        TextInput::make('MTC_JO')
                            ->label('Job Order')
                            ->columnSpan(5),
                        TextInput::make('MTC_DN_DO')
                            ->label('DN / DO')
                            ->columnSpan(5),
                        TextInput::make('MTC_BA')
                            ->label('BA')
                            ->columnSpan(5),
                        TextInput::make('MTC_Other')
                            ->label('Other MTC Info')
                            ->columnSpan(8),
                        TextInput::make('MTC_Remarks')
                            ->label('MTC Remarks')
                            ->columnSpan(8),
                        TextInput::make('ACTG_Unit_Price')
                            ->label('Unit Price')
                            ->columnSpan(5),
                        TextInput::make('ACTG_Currency')
                            ->label('Currency')
                            ->columnSpan(5),
                        TextInput::make('ACTG_Currency_Rate')
                            ->label('Currency Rate')
                            ->columnSpan(7),
                        TextInput::make('ACTG_Local_Net_Total')
                            ->label('Local Net Total')
                            ->columnSpan(8),
                        TextInput::make('ACTG_Invoicing')
                            ->label('Invoicing')
                            ->columnSpan(8),
                        DatePicker::make('ACTG_Inv_Date')
                            ->label('Invoice Date')
                            ->columnSpan(5),
                        DatePicker::make('ACTG_Payment_Receipt')
                            ->label('Payment Recv.')
                            ->columnSpan(8),
                        DatePicker::make('ACTG_Payment_Rcpt_Date')
                            ->label('Payment Rec. Date')
                            ->columnSpan(8),
                        TextInput::make('ACTG_Remarks')
                            ->label('Accounting Remarks')
                            ->columnSpan(9),
                        Forms\Components\Select::make('SO_Status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'SENT' => 'ALL SENT',
                                'CANCELED' => 'CANCELED',
                                'COMPLETED' => 'COMPLETED',
                                'DELIVERED PARTIAL' => 'DELIVERED PARTIAL',
                                'INVOICED' => 'INVOICED',
                                'ITEM INCOMPLETE' => 'ITEM INCOMPLETE',
                                'OUTSTANDING' => 'OUTSTANDING',
                                'PAYMENT' => 'PAYMENT',
                                'TAKE ID' => 'TAKE ID',
                                'W/OFF' => 'W/OFF',
                            ])
                            ->getOptionLabelUsing(fn($value) => match ($value) {
                                'SENT' => '<span class="text-green-600 font-bold">ALL SENT</span>',
                                'CANCELED' => '<span class="text-red-600 font-bold">CANCELED</span>',
                                'COMPLETED' => '<span class="text-green-600 font-bold">COMPLETED</span>',
                                'DELIVERED PARTIAL' => '<span class="text-yellow-600 font-bold">DELIVERED PARTIAL</span>',
                                'INVOICED' => '<span class="text-blue-600 font-bold">INVOICED</span>',
                                'ITEM INCOMPLETE' => '<span class="text-red-600 font-bold">ITEM INCOMPLETE</span>',
                                'OUTSTANDING' => '<span class="text-yellow-600 font-bold">OUTSTANDING</span>',
                                'PAYMENT' => '<span class="text-blue-600 font-bold">PAYMENT</span>',
                                'TAKE ID' => '<span class="text-gray-600 font-bold">TAKE ID</span>',
                                'W/OFF' => '<span class="text-gray-500 font-bold">W/OFF</span>',
                                default => '<span class="text-gray-500">UNKNOWN</span>',
                            })
                            ->columnSpan(10),
                        TextInput::make('updated_by')
                            ->label('Updated by')
                            ->disabled()
                            ->columnSpan(6)

                            ->default(fn() => Auth::user()?->name)
                        //->default(Auth::check() ? Auth::user()->name : 'Guest')
                        ,
                        TextInput::make('updated_at')
                            ->label('Updated at')
                            ->disabled()->columnSpan(6),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextInputColumn::make('ID')
                    ->sortable()
                    //->searchable()
                    ->toggleable()
                    ->disabled()
                    ->label('ID'),
                TextInputColumn::make('SO_ID')
                    ->label('SO ID')
                    ->toggleable()
                    ->placeholder('Generated from SO_No')
                    //->default(fn($record) => substr($record->SO_No, 0, 4) . '/' . substr($record->SO_No, -4))
                    ->sortable()
                    ->searchable(isIndividual: true),
                TextInputColumn::make('SO_No')
                    ->sortable()
                    //->searchable(isIndividual: true)
                    ->label('Sales Order No')
                    ->placeholder('Enter SO No')
                    ->default('')
                    ->toggleable(),

                TextInputColumn::make('SO_Date')


                    ->sortable()
                    //->searchable(isIndividual: true)

                    ->label('Sales Order Date')
                    ->placeholder('Enter SO Date')
                    ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->SO_Date)->format('Y-m-d')),


                /* DateTimeColumn::make('SO_Date')
                ->label('SO Date')
                ->format('d/m/Y') // Format tanggal seperti "DD/MM/YYYY"
                ->sortable()
                ->searchable(),
*/
                TextInputColumn::make('SO_DebtorID')
                    ->sortable()
                    //->searchable(isIndividual: true)
                    ->label('Debtor ID')
                    ->placeholder('Enter Debtor ID')
                    ->default('-'),

                TextInputColumn::make('SO_DebtorName')
                    //->weight(FontWeight::Bold)
                    ->sortable()
                    //->searchable(isIndividual: true)
                    ->label('Debtor Name')
                    ->placeholder('Enter Debtor Name')
                    ->default('-'),

                TextInputColumn::make('SO_Agent')
                    //->weight(FontWeight::Bold)
                    ->sortable()
                    //->searchable(isIndividual: true)
                    ->label('Agent ')
                    ->placeholder('Enter Agent')
                    ->default('-'),

                TextInputColumn::make('SO_CustPONo')
                    ->sortable()
                    //->searchable(isIndividual: true)
                    ->label('Customer PO No')
                    ->placeholder('Enter Customer PO No')
                    ->default('-'),

                TextInputColumn::make('SO_Item_Description')
                    ->sortable()
                    //->searchable(isIndividual: true)
                    ->label('Item Description')
                    ->placeholder('Enter Item Description')
                    ->default('-'),

                TextInputColumn::make('SO_LiftNo')
                    ->sortable()
                    //->searchable(isIndividual: true)
                    ->label('Lift No')
                    ->placeholder('Enter Lift No')
                    ->default('-'),

                TextInputColumn::make('SO_Qty')
                    ->sortable()
                    //->searchable(isIndividual: true)
                    ->label('Quantity')
                    ->placeholder('Enter Quantity')
                    ->default(''),

                TextInputColumn::make('SO_UOM')
                    ->sortable()
                    //->searchable(isIndividual: true)
                    ->label('UOM')
                    ->placeholder('Enter UOM')
                    ->default('-'),

                TextInputColumn::make('SO_OIR_SentTo_Finance')
                    ->sortable()
                    //->searchable(isIndividual: true)
                    ->label('OIR Sent to Finance')
                    ->placeholder('Enter OIR Sent to Finance')
                    ->default(''),

                TextInputColumn::make('SO_RQ_No')
                    ->label('Request No.')
                    ->sortable(),
                    //->searchable(isIndividual: true),

                Tables\Columns\SelectColumn::make('SO_Status')
                    ->label('SO Status')

                    ->options([
                        'SENT' => 'Sent',
                        'CANCELED' => 'Canceled',
                        'COMPLETED' => 'Completed',
                        'DELIVERED PARTIAL' => 'Delivered Partial',
                        'INVOICED' => 'Invoiced',
                        'ITEM INCOMPLETE' => 'Item Incomplete',
                        'OUTSTANDING' => 'Outstanding',
                        'PAYMENT' => 'Payment',
                        'TAKE ID' => 'Take ID',
                        'W/OFF' => 'W/OFF',
                        '#Replicated#' => 'Replicated'
                    ])
                // Menambahkan pencarian jika diperlukan
                ,

                TextInputColumn::make('PCH_PO_to_TELC_MS')
                    ->label('PO to TELC MS'),

                TextInputColumn::make('PCH_ETA')
                    ->label('ETA')
                    ->sortable(),
                    //->searchable(isIndividual: true),
                TextInputColumn::make('PCH_PO_ReceiveDate')
                    ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->PCH_ETA)->format('Y-m-d'))
                    ->label('PO Receive Date')

                    ->columnSpan(1),
                TextInputColumn::make('PCH_Transfered_Qty')
                    ->label('Transf. Qty')
                    ->columnSpan(1),
                TextInputColumn::make('PCH_Doc')
                    ->label('Purchase Document')
                    ->columnSpan(1),
                TextInputColumn::make('PCH_Date')
                    ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->PCH_Date)->format('Y-m-d'))
                    ->label('Purchase Date')

                    ->columnSpan(1),
                TextInputColumn::make('PCH_Inform_Finance_on')
                    ->label('Inform Finance on')
                    ->columnSpan(1),
                TextInputColumn::make('PCH_Remark')
                    ->label('Purchase Remark')->columnSpan(1)
                    ->sortable()
                    //->searchable(isIndividual: true)
                    ->toggleable(),

                TextInputColumn::make('MTC_RQ_No')
                    ->label('MTC Req. No.')
                    //->searchable(isIndividual: true)
                    ->toggleable(),
                TextInputColumn::make('MTC_RQ_Date')
                    ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->MTC_RQ_Date)->format('Y-m-d'))
                    ->label('MTC Req. Date')
                    ->toggleable(),
                TextInputColumn::make('MTC_Job_Done')
                    ->label('Job Done')
                    ->sortable()
                    //->searchable()
                    ->toggleable(),
                TextInputColumn::make('MTC_Target_Completion')
                    ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->MTC_Target_Completion)->format('Y-m-d'))
                    ->label('Target Compl. Date')
                    ->sortable()
                    ->toggleable(),
                TextInputColumn::make('MTC_SBK')
                    ->label('SBK')
                    ->sortable()
                    //->searchable()
                    ->toggleable(),
                TextInputColumn::make('MTC_JO')
                    ->label('Job Order')
                    ->toggleable(),
                TextInputColumn::make('MTC_DN_DO')
                    ->label('DN / DO')
                    ->toggleable(),
                TextInputColumn::make('MTC_BA')
                    ->label('BA')
                    ->sortable()
                    //->searchable()
                    ->toggleable(),
                TextInputColumn::make('MTC_Other')
                    ->label('Other MTC Info')
                    ->toggleable(),
                TextInputColumn::make('MTC_Remarks')
                    ->label('MTC Remarks')
                    ->sortable()
                    //->searchable()
                    ->toggleable(),
                TextInputColumn::make('ACTG_Unit_Price')
                    ->label('Unit Price')

                    ->toggleable(),
                TextInputColumn::make('ACTG_Currency')
                    ->label('Currency')
                    ->toggleable(),
                TextInputColumn::make('ACTG_Currency_Rate')
                    ->label('Currency Rate')
                    ->toggleable(),
                TextInputColumn::make('ACTG_Local_Net_Total')
                    ->label('Local Net Total')

                    ->toggleable(),
                TextInputColumn::make('ACTG_Invoicing')
                    ->label('Invoicing')
                    ->toggleable(),
                TextInputColumn::make('ACTG_Inv_Date')
                    ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->ACTG_Inv_Date)->format('Y-m-d'))
                    ->label('Invoice Date')
                    ->toggleable(),
                TextInputColumn::make('ACTG_Payment_Receipt')

                    ->label('Payment Receipt Date')
                    ->toggleable(),
                TextInputColumn::make('ACTG_Payment_Rcpt_Date')
                    ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->ACTG_Payment_Rcpt_Date)->format('Y-m-d'))
                    ->label('Payment Receipt Date')
                    ->toggleable(),
                TextInputColumn::make('ACTG_Remarks')
                    ->label('Accounting Remarks')
                    ->sortable()
                    //->searchable()
                    ->toggleable(),


                TextInputColumn::make('updated_by')
                    ->label('Updated by')
                    ->disabled()
                    ->columnSpan(6)
                    ->default(Auth::check() ? Auth::user()->name : 'Guest'),
                TextInputColumn::make('updated_at')
                    ->label('Updated at')
                    ->disabled()->columnSpan(6),
            ])
            ->striped()
            ->recordClasses(fn($record) => 'hover:bg-yellow-100 focus:bg-yellow-200')
            ->defaultSort('SO_ID', 'desc')
            ->filters([
                // Filter berdasarkan rentang tanggal (current month & year)
                // Filter berdasarkan rentang tanggal (current month & year)
                Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From')
                            ->default(Carbon::now()->subYear()->startOfYear()),  // Default: awal bulan ini

                        Forms\Components\DatePicker::make('to')
                            ->label('To')
                            ->default(Carbon::now()->endOfMonth()),  // Default: akhir bulan ini
                    ])
                    ->query(function ($query, $data) {
                        if (isset($data['from']) && isset($data['to'])) {
                            $query->whereBetween('SO_Date', [
                                Carbon::parse($data['from'])->startOfDay(),
                                Carbon::parse($data['to'])->endOfDay(),
                            ]);
                        }
                    }),
                SelectFilter::make('ID')
                    ->label('ID')
                    ->options(
                        fn() => Trans::query()
                            ->select('ID')
                            ->distinct()
                            ->orderBy('ID', 'desc')
                            ->pluck('ID', 'ID')
                            ->toArray()
                    )
                    ->multiple()
                    ->searchable(),
                SelectFilter::make('SO_ID')
                    ->label('SO ID')
                    ->options(
                        fn() => Trans::query()
                            ->select('SO_ID')
                            ->distinct()
                            ->orderBy('SO_ID', 'desc')
                            ->pluck('SO_ID', 'SO_ID')
                            ->toArray()
                    )
                    ->multiple()
                    ->searchable(),

                SelectFilter::make('SO_DebtorID')
                    ->label('Debtor ID')
                    ->options(
                        fn() => Trans::query()
                            ->select('SO_DebtorID')
                            ->distinct()
                            ->orderBy('SO_DebtorID', 'asc')
                            ->pluck('SO_DebtorID', 'SO_DebtorID')
                            ->toArray()
                    )
                    ->multiple()
                    ->searchable(),

                SelectFilter::make('SO_DebtorName')
                    ->label('Debtor Name')
                    ->options(
                        fn() => Trans::query()
                            ->select('SO_DebtorName')
                            ->distinct()
                            ->orderBy('SO_DebtorName', 'asc')
                            ->pluck('SO_DebtorName', 'SO_DebtorName')
                            ->toArray()
                    )
                    ->multiple()
                    ->searchable(),
                SelectFilter::make('SO_Agent')
                    ->label('Agent')
                    ->options(
                        fn() => Trans::query()
                            ->select('SO_Agent')
                            ->distinct()
                            ->orderBy('SO_Agent', 'asc')
                            ->pluck('SO_Agent', 'SO_Agent')
                            ->toArray()
                    )
                    ->multiple()
                    ->searchable(),

                SelectFilter::make('SO_Item_Description')
                    ->label('Item Description')
                    ->options(
                        fn() => Trans::query()
                            ->select('SO_Item_Description')
                            ->distinct()
                            ->orderBy('SO_Item_Description', 'asc')
                            ->pluck('SO_Item_Description', 'SO_Item_Description')
                            ->toArray()
                    )
                    ->multiple()
                    ->searchable(),

                    SelectFilter::make('MTC_DN_DO')
                    ->label('MTC_DN_DO')
                    ->options(
                        fn() => Trans::query()
                            ->select('MTC_DN_DO')
                            ->distinct()
                            ->orderBy('MTC_DN_DO', 'desc')
                            ->pluck('MTC_DN_DO', 'MTC_DN_DO')
                            ->toArray()
                    )
                    ->searchable(),

                
                SelectFilter::make('MTC_RQ_No')
                    ->label('MTC_RQ_No')
                    ->options(
                        fn() => Trans::query()
                            ->select('MTC_RQ_No')
                            ->distinct()
                            ->orderBy('MTC_RQ_No', 'desc')
                            ->pluck('MTC_RQ_No', 'MTC_RQ_No')
                            ->toArray()
                    )
                    ->searchable(),

                SelectFilter::make('SO_Status')
                    ->label('SO Status')
                    ->options(
                        fn() => Trans::query()
                            ->select('SO_Status')
                            ->orderBy('SO_Status', 'asc')
                            ->distinct()
                            ->pluck('SO_Status', 'SO_Status')
                            ->toArray()
                    )
                    ->multiple()
                    ->searchable(), 

            ])
            ->headerActions([
                ExportAction::make()->exporter(TransExporter::class),
                //ImportAction::make()->importer(BookImporter::class),
            ])
            ->actions([

                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Action::make('replicate')
                    ->label('Replicate')
                    ->color('danger')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function ($record) {
                        $newRecord = $record->replicate();
                        $newRecord->forceFill([
                            /* 'SO_No' => $record->SO_No,
                            'SO_Date' => $record->SO_Date,
                            'SO_DebtorID' => $record->SO_DebtorID,
                            'SO_Target_CompletionDatePerPO' => $record->SO_Target_CompletionDatePerPO,
                            'SO_DebtorName' => $record->SO_DebtorName,
                            'SO_Agent' => $record->SO_Agent,
                            'SO_CustPONo' => $record->SO_CustPONo,
                            'SO_OIR_SentTo_Finance' => $record->SO_OIR_SentTo_Finance,
                            'SO_RQ_No' => $record->SO_RQ_No, */
                            //'SO_Status' => 'Replicate', // Default value
                        ]);
                        //Log::info('New Record Before Save', $newRecord->toArray()); // Log data baru
                        $newRecord->SO_ID = '#Replicated#' . $record->id;

                        $newRecord->updated_by = Auth::user()->name;
                        $newRecord->updated_at = now();

                        $newRecord->SO_Status = 'Replicate'; // Contoh modifikasi
                        $newRecord->save();
                        //return redirect(TransactionalDataResource::getUrl('edit', ['record' => $newRecord->id]));
                        //Log::info('New Record After Save', $newRecord->toArray()); // Log data setelah simpan

                        // $this->notify('success', 'Record successfully replicated.');
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Replicate Record')
                    ->modalSubheading('Are you sure you want to replicate this record?'),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //  Tables\Actions\DeleteBulkAction::make(),
                ]),



                BulkAction::make('Set Status COMPLETED')

                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each->update(['SO_Status' => 'COMPLETED']);
                    }),
                BulkAction::make('Set Status CANCELED')
                    ->color('warning')
                    ->icon('heroicon-m-x-mark')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each->update(['SO_Status' => 'CANCELED']);
                    }),
                BulkAction::make('Set Status W/OFF')

                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each->update(['SO_Status' => 'W/OFF']);
                    }),
                ExportBulkAction::make()
                    ->exporter(TransExporter::class)
                    ->color('info') // Mengubah warna tombol menjadi 'info'
                    ->label('Export Data') // Menambahkan label pada tombol
                    ->icon('heroicon-o-arrow-down-tray')
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrans::route('/'),
            'create' => Pages\CreateTrans::route('/create'),
            'edit' => Pages\EditTrans::route('/{record}/edit'),
        ];
    }
}
