<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use App\Models\Department;

class ContactsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $contacts;

    public function __construct($contacts)
    {
        $this->contacts = $contacts;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Flatten the grouped collection while preserving centre grouping
        $flattenedContacts = collect();

        foreach ($this->contacts as $centre => $centreContacts) {
            foreach ($centreContacts as $contact) {
                $flattenedContacts->push($contact);
            }
        }

        return $flattenedContacts;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Ref',
            'CType',
            'Order',
            'Centre',
            'Centre Name',
            'Code',
            'Free Course',
            'Type',
            'Start Date',
            'Start Term',
            'Name',
            'Nickname',
            'School',
            'Gender',
            'Birth Date',
            'Password',
            'Address',
            'Postcode',
            'Telephone',
            'Father Name',
            'Father Email',
            'Father Mobile',
            'Mother Name',
            'Mother Email',
            'Mother Mobile',
            'Level',
            'Term',
            'Bookuse',
            'Level Name',
            'Term Name',
            'Bookuse Name',
            'Level2',
            'Term2',
            'Bookuse2',
            'Level2 Name',
            'Term2 Name',
            'Bookuse2 Name',
            'Discontinued',
            'Discontinued Date',
            'Days Since Discontinued',
            'Discontinued Reason',
            'Created By',
            'Updated By',
            'Created At',
            'Updated At'
        ];
    }

    /**
     * @param mixed $contact
     * @return array
     */
    public function map($contact): array
    {
        // Get department name from centre id
        $centreName = '';
        if ($contact->centre) {
            $department = Department::find($contact->centre);
            $centreName = $department ? $department->name : '';
        }

        // Calculate days since discontinued for discontinued = 1
        $daysSinceDiscontinued = '';
        if ($contact->discontinued == 1 && $contact->discontinued_date) {
            try {
                $discontinuedDate = Carbon::parse($contact->discontinued_date);
                $daysSinceDiscontinued = $discontinuedDate->diffInDays(Carbon::now());
            } catch (\Exception $e) {
                $daysSinceDiscontinued = '';
            }
        }

        return [
            $contact->id,
            $contact->ref,
            $contact->ctype,
            $contact->order,
            $contact->centre,
            $centreName,
            $contact->code,
            $contact->free_course,
            $contact->type,
            $contact->start_date,
            $contact->start_term,
            $contact->name,
            $contact->nickname,
            $contact->school,
            $contact->gender,
            $contact->birth_date,
            $contact->password,
            $contact->address,
            $contact->postcode,
            $contact->telephone,
            $contact->father_name,
            $contact->father_email,
            $contact->father_mobile,
            $contact->mother_name,
            $contact->mother_email,
            $contact->mother_mobile,
            $contact->level,
            $contact->term,
            $contact->bookuse,
            $contact->level_name,
            $contact->term_name,
            $contact->bookuse_name,
            $contact->level2,
            $contact->term2,
            $contact->bookuse2,
            $contact->level2_name,
            $contact->term2_name,
            $contact->bookuse2_name,
            $contact->discontinued,
            $contact->discontinued_date,
            $daysSinceDiscontinued,
            $contact->discontinued_reason,
            $contact->created_by,
            $contact->updated_by,
            $contact->created_at,
            $contact->updated_at
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
}
