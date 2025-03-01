<?php

namespace App\Filament\Exports;

use App\Models\Trans;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;
class TransExporter extends Exporter
{
    protected static ?string $model = Trans::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('ID')->label('ID'),
            ExportColumn::make('ID_ORI')->label('Original ID'),
            ExportColumn::make('SO_ID')->label('Sales Order ID'),
            ExportColumn::make('SO_No')->label('Sales Order Number'),
            ExportColumn::make('SO_Date')->label('Sales Order Date'),
            ExportColumn::make('SO_DebtorID')->label('Debtor ID'),
            ExportColumn::make('SO_Target_CompletionDatePerPO')->label('Target Completion Date per PO'),
            ExportColumn::make('SO_DebtorName')->label('Debtor Name'),
            ExportColumn::make('SO_Agent')->label('Sales Agent'),
            ExportColumn::make('SO_CustPONo')->label('Customer PO Number'),
            ExportColumn::make('SO_Item_Description')->label('Item Description'),
            ExportColumn::make('SO_LiftNo')->label('Lift Number'),
            ExportColumn::make('SO_Qty')->label('Quantity'),
            ExportColumn::make('SO_UOM')->label('Unit of Measurement'),
            ExportColumn::make('SO_OIR_SentTo_Finance')->label('OIR Sent to Finance'),
            ExportColumn::make('SO_RQ_No')->label('RQ Number'),
            ExportColumn::make('SO_Remark')->label('Sales Order Remarks'),
            ExportColumn::make('PCH_PO_to_TELC_MS')->label('PO to TELC MS'),
            ExportColumn::make('PCH_ETA')->label('ETA'),
            ExportColumn::make('PCH_PO_ReceiveDate')->label('PO Receive Date'),
            ExportColumn::make('PCH_Transfered_Qty')->label('Transferred Quantity'),
            ExportColumn::make('PCH_Doc')->label('Document'),
            ExportColumn::make('PCH_Date')->label('Purchase Date'),
            ExportColumn::make('PCH_Inform Finance on')->label('Inform Finance On'),
            ExportColumn::make('PCH_Remark')->label('Purchase Remarks'),
            ExportColumn::make('MTC_RQ_No')->label('MTC RQ Number'),
            ExportColumn::make('MTC_RQ_Date')->label('MTC RQ Date'),
            ExportColumn::make('MTC_Job_Done')->label('Job Done'),
            ExportColumn::make('MTC_Target_Completion')->label('MTC Target Completion'),
            ExportColumn::make('MTC_SBK')->label('MTC SBK'),
            ExportColumn::make('MTC_JO')->label('MTC Job Order'),
            ExportColumn::make('MTC_DN_DO')->label('MTC DN/DO'),
            ExportColumn::make('MTC_BA')->label('MTC BA'),
            ExportColumn::make('MTC_Other')->label('MTC Other'),
            ExportColumn::make('MTC_Remarks')->label('MTC Remarks'),
            ExportColumn::make('ACTG_Unit_Price')->label('Unit Price'),
            ExportColumn::make('ACTG_Currency')->label('Currency'),
            ExportColumn::make('ACTG_Currency_Rate')->label('Currency Rate'),
            ExportColumn::make('ACTG_Local_Net_Total')->label('Local Net Total'),
            ExportColumn::make('ACTG_Invoicing')->label('Invoicing'),
            ExportColumn::make('ACTG_Inv_Date')->label('Invoice Date'),
            ExportColumn::make('ACTG_Remarks')->label('Accounting Remarks'),
            ExportColumn::make('ACTG_Payment_Receipt')->label('Payment Receipt'),
            ExportColumn::make('ACTG_Payment_Rcpt_Date')->label('Payment Receipt Date'),
            ExportColumn::make('SO_Status')->label('Sales Order Status'),
            ExportColumn::make('updated_by')->label('Updated By'),
            ExportColumn::make('updated_at')->label('Updated At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your trans export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
