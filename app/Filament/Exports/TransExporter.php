<?php

namespace App\Filament\Exports;

use App\Models\Trans;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TransExporter extends Exporter
{
    protected static ?string $model = Trans::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('ID')->label('ID'),
            //ExportColumn::make('ID_ORI')->label('ID ORI'),
            ExportColumn::make('SO_ID')->label('SO ID'),
            ExportColumn::make('SO_No')->label('SO No'),
            ExportColumn::make('SO_Date')->label('SO Date'),
            ExportColumn::make('SO_DebtorID')->label('SO Debtor ID'),
            ExportColumn::make('SO_Target_CompletionDatePerPO')->label('SO Target Completion Date Per PO'),
            ExportColumn::make('SO_DebtorName')->label('SO Debtor Name'),
            ExportColumn::make('SO_Agent')->label('SO Agent'),
            ExportColumn::make('SO_CustPONo')->label('SO Cust PO No'),
            ExportColumn::make('SO_Item_Description')->label('SO Item Description'),
            ExportColumn::make('SO_LiftNo')->label('SO Lift No'),
            ExportColumn::make('SO_Qty')->label('SO Qty'),
            ExportColumn::make('SO_UOM')->label('SO UOM'),
            ExportColumn::make('SO_OIR_SentTo_Finance')->label('SO OIR Sent To Finance'),
            ExportColumn::make('SO_RQ_No')->label('SO RQ No'),
            ExportColumn::make('SO_Remark')->label('SO Remark'),
            ExportColumn::make('PCH_PO_to_TELC_MS')->label('PCH PO to TELC MS'),
            ExportColumn::make('PCH_ETA')->label('PCH ETA'),
            ExportColumn::make('PCH_PO_ReceiveDate')->label('PCH PO Receive Date'),
            ExportColumn::make('PCH_Transfered_Qty')->label('PCH Transferred Qty'),
            ExportColumn::make('PCH_Doc')->label('PCH Doc'),
            ExportColumn::make('PCH_Date')->label('PCH Date'),
            ExportColumn::make('PCH_Inform_Finance_on')->label('PCH Inform Finance On'),
            ExportColumn::make('PCH_Remark')->label('PCH Remark'),
            ExportColumn::make('MTC_RQ_No')->label('MTC RQ No'),
            ExportColumn::make('MTC_RQ_Date')->label('MTC RQ Date'),
            ExportColumn::make('MTC_Job_Done')->label('MTC Job Done'),
            ExportColumn::make('MTC_Target_Completion')->label('MTC Target Completion'),
            ExportColumn::make('MTC_SBK')->label('MTC SBK'),
            ExportColumn::make('MTC_JO')->label('MTC JO'),
            ExportColumn::make('MTC_DN_DO')->label('MTC DN/DO'),
            ExportColumn::make('MTC_BA')->label('MTC BA'),
            ExportColumn::make('MTC_Other')->label('MTC Other'),
            ExportColumn::make('MTC_Remarks')->label('MTC Remarks'),
            ExportColumn::make('ACTG_Unit_Price')->label('ACTG Unit Price'),
            ExportColumn::make('ACTG_Currency')->label('ACTG Currency'),
            ExportColumn::make('ACTG_Currency_Rate')->label('ACTG Currency Rate'),
            ExportColumn::make('ACTG_Local_Net_Total')->label('ACTG Local Net Total'),
            ExportColumn::make('ACTG_Invoicing')->label('ACTG Invoicing'),
            ExportColumn::make('ACTG_Inv_Date')->label('ACTG Inv Date'),
            ExportColumn::make('ACTG_Remarks')->label('ACTG Remarks'),
            ExportColumn::make('ACTG_Payment_Receipt')->label('ACTG Payment Receipt'),
            ExportColumn::make('ACTG_Payment_Rcpt_Date')->label('ACTG Payment Receipt Date'),
            ExportColumn::make('SO_Status')->label('SO Status'),
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
