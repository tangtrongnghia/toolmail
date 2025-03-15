<?php

namespace App\Imports;

use App\Models\FacebookSampleData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FacebookSampleDataImport implements ToCollection, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
        foreach ($rows->chunk(1000) as $chunk) {
            $insertData = [];
            $now = Carbon::now();

            foreach ($chunk as $row) {
                $insertData[] = [
                    'user_id' => auth()->user()->id,
                    'last_name' => $row['last_name'],
                    'first_name' => $row['first_name'],
                    'phone' => $row['phone'],
                    'password' => $row['password'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }

            FacebookSampleData::insertOrIgnore($insertData);
        }
    }
}
