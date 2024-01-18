<?php

namespace App\Exports;

use App\Models\Contact;
use App\Models\Receipt;
use App\Models\ReceiptForm;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class ExportReciept implements FromCollection, WithHeadings
{

    // var $sfield_check = 0;
    var $export_id = array();

    public function  __construct($export_id)
    {
        // $this->sfield_check = $sfield;
        $wsql = '';
        foreach ($export_id as $arr) {
            $wsql .= "'" . $arr . "',";
        }
        $this->export_id = $export_id;
    }

    // public function delete_all_between($beginning, $end, $string)
    // {
    //     $beginningPos = strpos($string, $beginning);
    //     $endPos = strpos($string, $end);
    //     if ($beginningPos === false || $endPos === false) {
    //         return $string;
    //     }

    //     $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);

    //     return $this->delete_all_between($beginning, $end, str_replace($textToDelete, '', $string));
    // }


    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {

        $receipts = Receipt::whereIn('id', $this->export_id)

            ->get();

        $customResults = $receipts->map(function ($receipt) {
            if ($receipt->gender == 1) {

                $sex = 'male';
            }else {
                $sex = 'female';
            }
            $contacts = Contact::find($receipt->cid);
            // dd($contacts);

            $level = $contacts->level_name . ' - ' . $contacts->level2_name;


            return [
                "receipt_number" => $receipt->receipt_number,
                "payment_date" => $receipt->payment_date,
                "code" => $receipt->student_no,
                "name" => $receipt->student_name,
                "level name" => $level,
                "total_fee" => $receipt->total_fee,


            ];
        });

        return $customResults;
    }

    public function headings(): array
    {
        return [
            'receipt_number',
            'payment_date',
            'code',
            'name',
            'level name',
            'total_fee',

        ];
    }
}
