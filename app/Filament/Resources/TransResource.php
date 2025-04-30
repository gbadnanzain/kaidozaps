<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Trans;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Tables\Actions\NewAction;
use Illuminate\Support\Carbon;
use PhpParser\Node\Stmt\Label;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Actions\ReplicateAction;

use App\Filament\Exports\TransExporter;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
//use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Filters\SelectFilter;
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
    // Scope untuk memfilter SO_Status
    public function scopeActiveStatus($query)
    {
        return $query->whereNotIn('SO_Status', ['COMPLETED', 'W/OFF', 'CANCELED']);
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
                        Forms\Components\Select::make('SO_Status')
                            ->label('Status')
                            ->required()
                            ->reactive()
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

                        Forms\Components\TextInput::make('SO_ID')
                            ->label('SO ID')
                            ->required()
                            ->columnSpan(6),



                        Forms\Components\DatePicker::make('SO_Date')
                            ->label('SO Date')
                            ->required()
                            ->placeholder('Select a date')
                            ->displayFormat('d/m/Y')
                            ->columnSpan(4),
                        Forms\Components\TextInput::make('SO_DebtorID')
                            ->label('Debt. ID')
                            ->required()
                            ->columnSpan(4),

                        Forms\Components\DatePicker::make('SO_Target_CompletionDatePerPO')
                            ->label('Target Compl./PO')
                            ->required()
                            ->placeholder('Select a date')
                            ->displayFormat('d/m/Y')
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
                            ->default('-')
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
                            ->default('-')
                            ->columnSpan(5),
                        TextInput::make('MTC_JO')
                            ->label('Job Order')
                            ->columnSpan(5),
                        TextInput::make('MTC_DN_DO')
                            ->label('DN / DO')
                            ->default('-')
                            ->columnSpan(5),
                        TextInput::make('MTC_BA')
                            ->label('BA')
                            ->default('-')
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

                        TextInput::make('updated_by')
                            ->label('Updated by')
                            ->disabled()
                            ->columnSpan(6)

                            ->default(Auth::user()->name),

                        //->default(Auth::check() ? Auth::user()->name : 'Guest')

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
                    //->saveUsing(fn($state) => strtoupper($state)) // <--- ini menggantikan dehydrateStateUsing
                    //->extraAttributes(['style' => 'text-transform: uppercase'])
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
                    ->tooltip('format date DD/MM/YYYY')

                    ->placeholder('Enter SO Date')
                    ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->SO_Date)->format('d/m/Y')),

                TextColumn::make('SO_Status')
                    ->badge()
                    ->label('SO Status')
                    ->sortable()
                    ->columnSpan(10)
                    ->toggleable()
                    ->icon(icon: fn(string $state) => match ($state) {
                        'ALL SENT' => 'heroicon-o-check-circle',
                        'CANCELED' => 'heroicon-o-x-circle',
                        'COMPLETED' => 'heroicon-o-check-circle',
                        'DELIVERED PARTIAL' => 'heroicon-o-truck',
                        'INVOICED' => 'heroicon-o-document',
                        'ITEM INCOMPLETE' => 'heroicon-o-exclamation-circle',
                        'OUTSTANDING' => 'heroicon-o-clock',
                        'PAYMENT' => 'heroicon-o-credit-card',
                        'TAKE ID' => 'heroicon-o-identification', // Changed to valid icon
                        'W/OFF' => 'heroicon-o-x-mark',
                        '#Replicated#' => 'heroicon-o-hand-raised',
                        default => 'heroicon-o-hand-raised',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'ALL SENT' => 'primary',  // bg-blue-500
                        'CANCELED' => 'danger',   // bg-red-500
                        'COMPLETED' => 'success', // bg-green-500
                        'DELIVERED PARTIAL' => 'warning', // bg-yellow-500
                        'INVOICED' => 'primary', // bg-purple-500
                        'ITEM INCOMPLETE' => 'warning', // bg-orange-500
                        'OUTSTANDING' => 'primary', // bg-gray-500
                        'PAYMENT' => 'success',    // bg-teal-500
                        'TAKE ID' => 'warning',  // bg-indigo-500
                        'W/OFF' => 'danger',      // bg-pink-500
                        '#Replicated#' => 'gray', // bg-gray-700
                        default => 'gray', // Default color if no match
                    })
                    ->html(fn(string $state): string => match ($state) {
                        'ALL SENT' =>
                        '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4v16a1 1 0 001 1h16a1 1 0 001-1V4l-8 4-8-4z"/>
        </svg>' . $state, // Paper airplane icon
                        'CANCELED' =>
                        '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>' . $state, // X icon
                        'COMPLETED' =>
                        '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>' . $state, // Checkmark icon
                        'DELIVERED PARTIAL' =>
                        '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 12V8a4 4 0 118 0v4a4 4 0 11-8 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12V8a4 4 0 118 0v4a4 4 0 11-8 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12v8a4 4 0 014 4h8a4 4 0 014-4v-8"/>
        </svg>' . $state, // Truck icon
                        'INVOICED' =>
                        '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 0v4m0-4h4m-4 0h-4"/>
        </svg>' . $state, // Invoice icon
                        'ITEM INCOMPLETE' =>
                        '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6"/>
        </svg>' . $state, // Exclamation icon
                        'OUTSTANDING' =>
                        '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 0v4m0-4h4m-4 0h-4"/>
        </svg>' . $state, // Clock icon
                        'PAYMENT' =>
                        '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v18H3z"/>
        </svg>' . $state, // Credit card icon
                        'TAKE ID' =>
                        '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10h6v4H9z"/>
        </svg>' . $state, // ID card icon
                        'W/OFF' =>
                        '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2 2 2M12 10v4"/>
        </svg>' . $state, // Minus circle icon
                        '#Replicated#' =>
                        '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4v16a1 1 0 001 1h16a1 1 0 001-1V4l-8 4-8-4z"/>
        </svg>' . $state, // Copy icon
                        default =>
                        '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline-block mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v20M2 12h20"/>
        </svg>' . $state, // Default circle icon

                    }),


                TextInputColumn::make('SO_DebtorID')
                    ->sortable()
                    //->searchable(isIndividual: true)
                    ->label('Debtor ID')
                    ->placeholder('Enter Debtor ID')
                    ->default('-'),

                TextInputColumn::make('SO_Target_CompletionDatePerPO')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip('format date DD/MM/YYYY')
                    //->searchable(isIndividual: true)
                    ->label('SO Target Completion Date Per PO')
                    ->placeholder('Enter SO Target Completion Date Per PO')
                    ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->SO_Date)->format('d/m/Y'))
                    ->default(Carbon::now()->format('d/m/Y')),

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
                    ->default('-')
                    ->extraAttributes([
                        'style' => 'width: fit-content; white-space: nowrap;',
                    ]),

                TextInputColumn::make('SO_LiftNo')
                    ->sortable()
                    ->searchable(isIndividual: true)
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



                TextInputColumn::make('PCH_PO_to_TELC_MS')
                    ->label('PO to TELC MS'),

                TextInputColumn::make('PCH_ETA')
                    ->label('ETA')
                    ->sortable(),
                //->searchable(isIndividual: true),
                TextInputColumn::make('PCH_PO_ReceiveDate')
                    ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->PCH_ETA)->format('d/m/Y'))
                    ->label('PO Receive Date')
                    ->tooltip('format date DD/MM/YYYY')
                    ->columnSpan(1),
                TextInputColumn::make('PCH_Transfered_Qty')
                    ->label('Transf. Qty')
                    ->columnSpan(1),
                TextInputColumn::make('PCH_Doc')
                    ->searchable(isIndividual: true)
                    ->label('Purchase Document')
                    ->columnSpan(1),
                TextInputColumn::make('PCH_Date')
                    ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->PCH_Date)->format('d/m/Y'))
                    ->label('Purchase Date')
                    ->tooltip('format date DD/MM/YYYY')
                    ->columnSpan(1),
                /* TextInputColumn::make('PCH_Inform_Finance_on')
                    ->label('Inform Finance on')
                    ->columnSpan(1), */
                TextInputColumn::make('PCH_Remark')
                    ->label('Purchase Remark')->columnSpan(1)
                    ->sortable()
                    //->searchable(isIndividual: true)
                    ->toggleable(),

                TextInputColumn::make('MTC_RQ_No')
                    ->label('MTC Req. No.')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                TextInputColumn::make('MTC_RQ_Date')
                    ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->MTC_RQ_Date)->format('d/m/Y'))
                    ->label('MTC Req. Date')
                    ->tooltip('format date DD/MM/YYYY')
                    ->toggleable(),
                TextInputColumn::make('MTC_Job_Done')
                    ->label('Job Done')
                    ->sortable()
                    //->searchable()
                    ->toggleable(),
                TextInputColumn::make('MTC_Target_Completion')
                    ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->MTC_Target_Completion)->format('d/m/Y'))
                    ->label('Target Compl. Date')
                    ->tooltip('format date DD/MM/YYYY')
                    ->sortable()
                    ->toggleable(),
                /* TextInputColumn::make('MTC_SBK')
                    ->searchable(isIndividual: true)
                    ->label('SBK')
                    ->sortable()
                    //->searchable()
                    ->toggleable(), */
                TextInputColumn::make('MTC_JO')
                    ->label('Job Order')
                    ->toggleable(),
                /* TextInputColumn::make('MTC_DN_DO')
                ->searchable(isIndividual: true)
                    ->label('DN / DO')
                    ->sortable()
                    ->toggleable(), */
                TextInputColumn::make('MTC_BA')
                    ->label('BA')
                    ->sortable()
                    ->searchable(isIndividual: true)
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

                    ->toggleable(isToggledHiddenByDefault: true),
                TextInputColumn::make('ACTG_Currency')
                    ->label('Currency')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextInputColumn::make('ACTG_Currency_Rate')
                    ->label('Currency Rate')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextInputColumn::make('ACTG_Local_Net_Total')
                    ->label('Local Net Total')

                    ->toggleable(isToggledHiddenByDefault: true),
                TextInputColumn::make('ACTG_Invoicing')
                    ->label('Invoicing')
                    ->toggleable(),
                TextInputColumn::make('ACTG_Inv_Date')
                    ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->ACTG_Inv_Date)->format('d/m/Y'))
                    ->label('Invoice Date')
                    ->tooltip('format date DD/MM/YYYY')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextInputColumn::make('ACTG_Payment_Receipt')

                    ->label('Payment Receipt Date')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextInputColumn::make('ACTG_Payment_Rcpt_Date')
                    ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->ACTG_Payment_Rcpt_Date)->format('d/m/Y'))
                    ->label('Payment Receipt Date')
                    ->tooltip('format date d/m/YYYY')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextInputColumn::make('ACTG_Remarks')
                    ->label('Accounting Remarks')
                    ->sortable()
                    //->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),


                TextInputColumn::make('updated_by')
                    ->label('Updated by')
                    ->disabled()
                    ->columnSpan(6)
                    ->default(Auth::check() ? Auth::user()->name : 'Guest'),
                TextInputColumn::make('updated_at')
                    ->label('Updated at')
                    ->sortable()
                    ->disabled()->columnSpan(6),
            ])
            ->striped()
            ->recordClasses(fn($record) => 'hover:bg-yellow-100 focus:bg-yellow-200')
            ->defaultSort('SO_ID', 'desc')
            ->filters([
                // Filter berdasarkan rentang tanggal (current month & year)

                Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('SO Date From')
                            ->default(Carbon::now()->subYear()->startOfYear()),  // Default: awal bulan ini

                        Forms\Components\DatePicker::make('to')
                            ->label('SO Date To')
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

                Filter::make('date_range_mtc_Target_Completion')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Target Completion From')
                            ->default(Carbon::now()->subYear()->startOfYear()),  // Default: awal tahun lalu

                        Forms\Components\DatePicker::make('to')
                            ->label('Target Completion To')
                            ->default(Carbon::now()->endOfMonth()),  // Default: akhir bulan ini
                    ])
                    ->query(function ($query, $data) {
                        if (isset($data['from']) && isset($data['to'])) {
                            $query->whereBetween('MTC_Target_Completion', [
                                Carbon::parse($data['from'])->startOfDay(),
                                Carbon::parse($data['to'])->endOfDay(),
                            ]);
                        }
                    }),




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
                ExportAction::make()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('All Data Export')
                    ->exporter(TransExporter::class)
                // EXPORT
                /* ExportAction::make('exportXls')
                    ->label('Export XLS')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        return Excel::download( \App\Filament\Exports\TransExporter::class, 'trans.xls');
                    }), */

            ])
            ->actions([


                Tables\Actions\ActionGroup::make(
                    [

                        // ->requiresConfirmation()
                        //->requiresConfirmation()
                        //->modalHeading('Create New Record')
                        //->modalSubheading('Are you sure you want to create new record?'),






                        Action::make('replicate')
                            ->label('Duplicate this ...')
                            ->color('danger')
                            ->icon('heroicon-o-document-duplicate')
                            ->action(function ($record) {
                                $newReplicaRecord = $record->replicate();

                                // List of attributes to exclude
                                $excludeAttributes = [
                                    'SO_Item_Description',
                                    'SO_LiftNo',
                                    'SO_Qty',
                                    'SO_UOM',
                                    'SO_OIR_SentTo_Finance',
                                    'SO_RQ_No',
                                    'SO_Remark',
                                    'PCH_PO_to_TELC_MS',
                                    'PCH_ETA',
                                    'PCH_PO_ReceiveDate',
                                    'PCH_Transfered_Qty',
                                    'PCH_Doc',
                                    'PCH_Date',
                                    'PCH_Inform Finance on',
                                    'PCH_Remark',
                                    'MTC_RQ_No',
                                    'MTC_RQ_Date',
                                    'MTC_Job_Done',
                                    'MTC_Target_Completion',
                                    'MTC_SBK',
                                    'MTC_JO',
                                    'MTC_DN_DO',
                                    'MTC_BA',
                                    'MTC_Other',
                                    'MTC_Remarks',
                                    'ACTG_Unit_Price',
                                    'ACTG_Currency',
                                    'ACTG_Currency_Rate',
                                    'ACTG_Local_Net_Total',
                                    'ACTG_Invoicing',
                                    'ACTG_Inv_Date',
                                    'ACTG_Remarks',
                                    'ACTG_Payment_Receipt',
                                    'ACTG_Payment_Rcpt_Date'
                                ];

                                // Loop through attributes to exclude and unset them from the new record
                                foreach ($excludeAttributes as $attribute) {
                                    unset($newReplicaRecord->$attribute);
                                }
                                $newReplicaRecord->SO_ID = $record->SO_ID;
                                $newReplicaRecord->SO_Status =   $record->SO_Status;
                                $newReplicaRecord->updated_by = Auth::user()->name;
                                $newReplicaRecord->updated_at = now();

                                // Simpan record yang baru
                                $newReplicaRecord->save();
                                // $this->notify('success', 'Record successfully replicated/duplicated.');
                            })


                    ]
                ),

                /* CreateAction::make()
                    ->label('New Record [Form]')
                    ->color('warning'),

                Tables\Actions\EditAction::make()
                    ->label('Edit [Form]'), */
                // Tables\Actions\DeleteAction::make(),







            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //  Tables\Actions\DeleteBulkAction::make(),
                ]),

                // Bulk action untuk Set Status 'ALL SENT'
                BulkAction::make('ALL SENT')
                    ->Label('ALL SENT')
                    ->tooltip('Set Status ALL SENT')
                    ->icon('heroicon-o-check-circle')  // Ikon untuk 'ALL SENT'
                    ->color('primary')
                    ->requiresConfirmation()  // Meminta konfirmasi sebelum tindakan
                    ->action(function (Collection $records) {
                        if ($records->count() < 51) {
                            $records->each(function ($record) {
                                $record->update(['SO_Status' => 'ALL SENT', 'updated_by' => Auth::user()->name]);
                            });
                        }
                    })
                    ->deselectRecordsAfterCompletion(),

                // Bulk action untuk Set Status 'CANCELED'
                BulkAction::make('CANCELED')
                    ->Label('CANCELED')
                    ->tooltip('Set Status CANCELED')
                    ->icon('heroicon-o-x-circle')  // Ikon untuk 'CANCELED'
                    ->color('danger') // Menentukan warna tombol
                    ->requiresConfirmation()  // Meminta konfirmasi sebelum tindakan
                    ->action(function (Collection $records) {
                        if ($records->count() < 51) {
                            $records->each(function ($record) {
                                $record->update(['SO_Status' => 'CANCELED', 'updated_by' => Auth::user()->name]);
                            });
                        }
                    })
                    ->deselectRecordsAfterCompletion(),

                // Bulk action untuk Set Status 'COMPLETED'
                BulkAction::make('COMPLETED')
                    ->Label('COMPLETED')
                    ->tooltip('Set Status COMPLETED')
                    ->icon('heroicon-o-check-circle')  // Ikon untuk 'COMPLETED'
                    ->color('success') // Menentukan warna tombol
                    ->requiresConfirmation()  // Meminta konfirmasi sebelum tindakan
                    ->action(function (Collection $records) {
                        if ($records->count() < 51) {
                            $records->each(function ($record) {
                                $record->update(['SO_Status' => 'COMPLETED', 'updated_by' => Auth::user()->name]);
                            });
                        }
                    })
                    ->deselectRecordsAfterCompletion(),

                // Bulk action untuk Set Status 'DELIVERED PARTIAL'
                BulkAction::make('DELIVERED PARTIAL')
                    ->Label('DELIVERED PARTIAL')
                    ->tooltip('Set Status DELIVERED PARTIAL')
                    ->icon('heroicon-o-truck')  // Ikon untuk 'DELIVERED PARTIAL'
                    ->color('warning') // Menentukan warna tombol
                    ->requiresConfirmation()  // Meminta konfirmasi sebelum tindakan
                    ->action(function (Collection $records) {
                        if ($records->count() < 51) {
                            $records->each(function ($record) {
                                $record->update(['SO_Status' => 'DELIVERED PARTIAL', 'updated_by' => Auth::user()->name]);
                            });
                        }
                    })
                    ->deselectRecordsAfterCompletion(),

                // Bulk action untuk Set Status 'INVOICED'
                BulkAction::make('Set Status INVOICED')
                    ->Label('INVOICED')
                    ->tooltip('Set Status INVOICED')
                    ->icon('heroicon-o-document')  // Ikon untuk 'INVOICED'
                    ->color('primary')  // Menentukan warna tombol
                    ->requiresConfirmation()  // Meminta konfirmasi sebelum tindakan
                    ->action(function (Collection $records) {
                        if ($records->count() < 51) {
                            $records->each(function ($record) {
                                $record->update(['SO_Status' => 'INVOICED', 'updated_by' => Auth::user()->name]);
                            });
                        }
                    })
                    ->deselectRecordsAfterCompletion(),

                // Bulk action untuk Set Status 'ITEM INCOMPLETE'
                BulkAction::make('Set Status ITEM INCOMPLETE')
                    ->Label('ITEM INCOMPLETE')
                    ->tooltip('Set Status ITEM INCOMPLETE')
                    ->icon('heroicon-o-exclamation-circle')  // Ikon untuk 'ITEM INCOMPLETE'
                    ->color('warning')  // Menentukan warna tombol
                    ->requiresConfirmation()  // Meminta konfirmasi sebelum tindakan
                    ->action(function (Collection $records) {
                        if ($records->count() < 51) {
                            $records->each(function ($record) {
                                $record->update(['SO_Status' => 'ITEM INCOMPLETE', 'updated_by' => Auth::user()->name]);
                            });
                        }
                    })
                    ->deselectRecordsAfterCompletion(),

                // Bulk action untuk Set Status 'OUTSTANDING'
                BulkAction::make('Set Status OUTSTANDING')
                    ->label('OUTSTANDING')
                    ->tooltip('Set Status OUTSTANDING')
                    ->icon('heroicon-o-clock')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Ubah Status')
                    ->modalSubheading(function (Collection $records) {
                        $firstSoId = $records->first()->SO_ID ?? 'Tidak Diketahui';
                        $allSameSoId = $records->every(fn($record) => $record->SO_ID === $firstSoId);

                        return $allSameSoId
                            ? "Semua record memiliki SO_ID: $firstSoId. Apakah Anda yakin ingin mengubah status menjadi OUTSTANDING?"
                            : "SO_ID dari record tidak sama. Tindakan ini tidak akan diproses.";
                    })
                    ->modalButton('Ya, Ubah Status')
                    ->action(function (Collection $records) {
                        $firstSoId = $records->first()->SO_ID;
                        $allSameSoId = $records->every(fn($record) => $record->SO_ID === $firstSoId);

                        if ($allSameSoId) {
                            $records->each(function ($record) {
                                $record->update([
                                    'SO_Status' => 'OUTSTANDING',
                                    'updated_by' => Auth::user()->name,
                                ]);
                            });
                        }
                    })
                    ->deselectRecordsAfterCompletion(),

                // Bulk action untuk Set Status 'PAYMENT'
                BulkAction::make('Set Status PAYMENT')
                    ->Label('PAYMENT')
                    ->tooltip('Set Status PAYMENT')
                    ->icon('heroicon-o-credit-card')  // Ikon untuk 'PAYMENT'
                    ->color('success')  // Menentukan warna tombol
                    ->requiresConfirmation()  // Meminta konfirmasi sebelum tindakan
                    ->action(function (Collection $records) {
                        if ($records->count() < 51) {
                            $records->each(function ($record) {
                                $record->update(['SO_Status' => 'PAYMENT', 'updated_by' => Auth::user()->name]);
                            });
                        }
                    })
                    ->deselectRecordsAfterCompletion(),

                // Bulk action untuk Set Status 'TAKE ID'
                BulkAction::make('Set Status TAKE ID')
                    ->Label('TAKE ID')
                    ->tooltip('Set Status TAKE ID')
                    ->icon('heroicon-o-identification')  // Ikon untuk 'TAKE ID'
                    ->color('warning')  // Menentukan warna tombol
                    ->requiresConfirmation()  // Meminta konfirmasi sebelum tindakan
                    ->action(function (Collection $records) {
                        if ($records->count() < 51) {
                            $records->each(function ($record) {
                                $record->update(['SO_Status' => 'TAKE ID', 'updated_by' => Auth::user()->name]);
                            });
                        }
                    })
                    ->deselectRecordsAfterCompletion(),

                // Bulk action untuk Set Status 'W/OFF'
                BulkAction::make('Set Status W/OFF')
                    ->Label('W/OFF')
                    ->tooltip('Set Status W/OFF')
                    ->icon('heroicon-o-x-mark')  // Ikon untuk 'W/OFF'
                    ->color('danger')  // Menentukan warna tombol
                    ->requiresConfirmation()  // Meminta konfirmasi sebelum tindakan
                    ->action(function (Collection $records) {
                        if ($records->count() < 51) {
                            $records->each(function ($record) {
                                $record->update(['SO_Status' => 'W/OFF', 'updated_by' => Auth::user()->name]);
                            });
                        }
                    })
                    ->deselectRecordsAfterCompletion(),


                /*  ExportBulkAction::make()
                    ->exporter(TransExporter)
                    ->color('info') // Mengubah warna tombol menjadi 'info'
                    ->label('Export Data') // Menambahkan label pada tombol
                    ->icon('heroicon-o-arrow-down-tray')
                    ->deselectRecordsAfterCompletion() */


            ])
            //->deselectRecordsAfterCompletion()
        ;
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
