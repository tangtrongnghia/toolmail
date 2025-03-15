<?php

namespace App\Exports;

use App\Models\FacebookSampleData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FacebookSampleDataExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return FacebookSampleData::where('status', FacebookSampleData::SUCCESS)->get([
            'last_name',
            'first_name',
            'phone',
            'email',
            'password',
            'facebook_link',
            'facebook_uid',
            'two_fa_secret',
        ]);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Last Name',
            'First Name',
            'Phone',
            'Email',
            'Password',
            'Link',
            'UID',
            '2FA',
        ];
    }
}
