<?php

namespace App\Exports;

use App\Models\FacebookSampleData;
use Maatwebsite\Excel\Concerns\FromCollection;

class FacebookSampleDataExport implements FromCollection
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
            'two_fa_secret',
        ]);
    }
}
