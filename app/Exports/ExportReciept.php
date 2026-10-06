<?php

namespace App\Exports;

use App\Models\Receipt;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

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

        $receipts = Receipt::whereIn('id', $this->export_id)->with('levels:id,name')->get();

        $custom_results = $receipts->map(function ($receipt) {
            // A receipt's issued values remain valid when its student moves or is removed.
            $level = $receipt->levels?->name ?? (string) $receipt->level;


            return [
                "receipt_number" => $receipt->receipt_number,
                "payment_date" => $receipt->payment_date,
                "code" => $receipt->student_no,
                "name" => $receipt->student_name,
                "level name" => $level,
                "total_fee" => $receipt->total_fee,


            ];
        });

        return $custom_results;
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
