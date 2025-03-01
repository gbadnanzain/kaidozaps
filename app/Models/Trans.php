<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trans extends Model
{
    use HasFactory;

    protected $table = 'trans'; // Explicitly defining the table name

    protected $primaryKey = 'ID'; // Defining primary key if different from default 'id'

    public $timestamps = false; // Disable automatic timestamps if needed, since you have custom fields

    protected $fillable = [
        'ID_ORI', 'SO_ID', 'SO_No', 'SO_Date', 'SO_DebtorID', 'SO_Target_CompletionDatePerPO', 'SO_DebtorName', 
        'SO_Agent', 'SO_CustPONo', 'SO_Item_Description', 'SO_LiftNo', 'SO_Qty', 'SO_UOM', 'SO_OIR_SentTo_Finance',
        'SO_RQ_No', 'SO_Remark', 'PCH_PO_to_TELC_MS', 'PCH_ETA', 'PCH_PO_ReceiveDate', 'PCH_Transfered_Qty', 
        'PCH_Doc', 'PCH_Date', 'PCH_Inform Finance on', 'PCH_Remark', 'MTC_RQ_No', 'MTC_RQ_Date', 'MTC_Job_Done', 
        'MTC_Target_Completion', 'MTC_SBK', 'MTC_JO', 'MTC_DN_DO', 'MTC_BA', 'MTC_Other', 'MTC_Remarks', 
        'ACTG_Unit_Price', 'ACTG_Currency', 'ACTG_Currency_Rate', 'ACTG_Local_Net_Total', 'ACTG_Invoicing', 
        'ACTG_Inv_Date', 'ACTG_Remarks', 'ACTG_Payment_Receipt', 'ACTG_Payment_Rcpt_Date', 'SO_Status', 
        'updated_by', 'updated_at',
    ];

    protected $dates = [
        'SO_Date', 'SO_Target_CompletionDatePerPO', 'PCH_ETA', 'PCH_PO_ReceiveDate', 'PCH_Date', 
        'PCH_Inform Finance on', 'MTC_RQ_Date', 'MTC_Target_Completion', 'ACTG_Inv_Date', 'ACTG_Payment_Rcpt_Date'
    ];
}
