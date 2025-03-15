<?php

namespace App\Http\Controllers;

use App\Exports\FacebookSampleDataExport;
use App\Imports\FacebookSampleDataImport;
use App\Models\FacebookSampleData;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class FacebookUserController extends Controller
{
    public function index(Request $request)
    {
        $data = FacebookSampleData::paginate(10);

        return Inertia::render('ImportFacebookCsv', ['facebook_data' => $data]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx|max:2048',
        ]);

        try {
            Excel::import(new FacebookSampleDataImport, $request->file('file'));

            return redirect()->back()->with('success', 'CSV Imported Successfully!');
        } catch (\Throwable $th) {
            throw ValidationException::withMessages([
                'file' => 'Import thất bại, vui lòng kiểm tra lại file.'
            ]);
        }

    }

    public function export()
    {
        return Excel::download(new FacebookSampleDataExport, 'users.csv');
    }

    public function delete(Request $request)
    {
        if ($request->input('all')) {
            FacebookSampleData::truncate();

            return redirect()->back()->with('success', 'Delete All Successfully!');
        }

        $ids = $request->input('ids');

        if (empty($ids)) {
            throw ValidationException::withMessages([
                'ids' => 'id không hợp lệ'
            ]);
        }

        FacebookSampleData::whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', 'Delete All Successfully!');
    }
}
