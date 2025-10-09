<?php

namespace App\Exports;

use App\Models\Contact;
use App\Models\ReceiptForm;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class ExportForm implements FromCollection, WithHeadings
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
        // dd($this->export_id);

        // $contacts = Contact::select(
        //     "name",
        //     "nickname",
        //     "school",
        //     "gender",
        //     "birth_date",
        //     "address",
        //     "telephone",
        //     // "birth_date",
        //     // "street_address",
        //     // "postcode",
        //     // "name_parent",
        //     // "mobile",
        //     // "package",
        //     // "contest",
        //     // DB::raw("CONCAT('" . url('/') . "/receipt_form_images/',photo_att) AS photo"),
        //     // DB::raw("CONCAT('" . url('/') . "/receipt_form_images/',file_att) AS slip"),
        //     // "news_form"
        // )
        //     ->whereIn('id', $this->export_id)
        //     // ->where([['sfield', $this->sfield_check]])
        //     ->get();
        $contacts = Contact::whereIn('id', $this->export_id)

            ->get();

        $customResults = $contacts->map(function ($contact) {
            if ($contact->gender == 1) {

                $sex = 'male';
            }else {
                $sex = 'female';
            }
            return [
                "code" => $contact->code,
                "start_date" => $contact->start_date,
                "start_term" => $contact->start_term,
                "name" => $contact->name,
                "nickname" => $contact->nickname,
                "school" => $contact->school,
                "gender" => $sex,
                "birth_date" => $contact->birth_date,
                "telephone" => $contact->telephone,
                "email" => $contact->father_email.', '.$contact->mother_email,

                "level_name" => $contact->level_name,
                "term_name" => $contact->term_name,
                "bookuse_name" => $contact->bookuse_name,

                "level2_name" => $contact->level2_name,
                "term2_name" => $contact->term2_name,
                "bookuse2_name" => $contact->bookuse2_name,
                "father_name" => $contact->father_name,
                "father_mobile" => $contact->father_mobile,
                "mother_name" => $contact->mother_name,
                "mother_mobile" => $contact->mother_mobile,

            ];
        });

        // dd($customResults);

        return $customResults;
    }

    public function headings(): array
    {
        return [

            'code',
            'start_date',
            'start_term',
            'name',
            'Nick Name',
            'School Name',
            'gender',
            'Birth Date',
            'Mobile',
            'email',
            'start level',
            'start term',
            'start bookuse',
            'To level',
            'To term',
            'To bookuse',
            'father name',
            'father mobile',
            'mother name',
            'mother mobile',

            // 'Package',
            // 'Price',
            // 'Contest',
            // 'Photo',
            // 'Slip',
            // 'News From',
        ];
    }
}
