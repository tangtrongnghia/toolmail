<?php

namespace App\Http\Controllers;

use App\Exports\FacebookSampleDataExport;
use App\Imports\FacebookSampleDataImport;
use App\Models\FacebookSampleData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class FacebookUserController extends Controller
{
    public function index(Request $request)
    {
        $statusInput = strtolower($request->input('status', 'all'));
        $status = match ($statusInput) {
             'pending'=> FacebookSampleData::PENDING,
             'inprocess' => FacebookSampleData::PROCESSING,
             'success' => FacebookSampleData::SUCCESS,
             default => null,
        };

        $data = FacebookSampleData::when($status !== null, function ($query) use ($status) {
            if ($status == FacebookSampleData::PENDING) {
                $query->where(function ($q) {
                    $q->where('status', FacebookSampleData::PENDING)
                      ->orWhere(function ($subQuery) {
                          $subQuery->where('status', FacebookSampleData::PROCESSING)
                                   ->where('updated_at', '<', now()->subHours(2));
                      });
                });
            } elseif ($status == FacebookSampleData::PROCESSING) {
                $query->where('status', FacebookSampleData::PROCESSING)
                      ->where('updated_at', '>=', now()->subHours(2));
            } else {
                $query->where('status', $status);
            }
        })
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->appends(request()->query());

        $countPending = FacebookSampleData::where('status', FacebookSampleData::PENDING)
            ->orWhere(function ($q) {
                $q->where('status', FacebookSampleData::PROCESSING)
                  ->where('updated_at', '<', now()->subHours(2));
            })
            ->count();

        $countProcessing = FacebookSampleData::where('status', FacebookSampleData::PROCESSING)
            ->where('updated_at', '>=', now()->subHours(2))
            ->count();

        $countSuccess = FacebookSampleData::where('status', FacebookSampleData::SUCCESS)->count();

        return Inertia::render('ImportFacebookCsv', [
            'facebook_data' => $data,
            'statusFilter' => $statusInput,
            'countPending' => $countPending,
            'countProcessing' => $countProcessing,
            'countSuccess' => $countSuccess,
        ]);
    }

    public function getInfo()
    {
        return Inertia::render('GetFacebookInfo');
    }

    public function fetchInfo()
    {
        $data = FacebookSampleData::where(function ($query) {
                $query->where('status', FacebookSampleData::PENDING)
                    ->orWhere(function ($q) {
                        $q->where('status', FacebookSampleData::PROCESSING)
                            ->where('updated_at', '<', now()->subHours(2));
                    });
            })
            ->inRandomOrder()
            ->first();

        if ($data) {
            $data->forceFill([
                'status' => FacebookSampleData::PROCESSING,
                'updated_at' => now(),
            ])->save();
        }

        return response()->json(['data' => $data]);
    }

    public function checkStatus(Request $request, FacebookSampleData $data)
    {
        if ($data->status == FacebookSampleData::SUCCESS) {
            return response()->json(['error' => 'Data is invalid'], 400);
        }

        $data->forceFill([
            'status' => FacebookSampleData::PROCESSING,
            'updated_at' => now(),
        ])->save();

        return response()->json(['message' => 'Data is valid']);
    }

    public function saveInfo(Request $request)
    {
        $inputs = $request->all();
        $data = FacebookSampleData::findOrFail($inputs['id']);

        if ($inputs['facebook_link']) {
            $requestGetUid = new Request(['input' => [$inputs['facebook_link']]]);
            $uid = app(FacebookUidController::class)->getFacebookUid($requestGetUid);
            $data->facebook_uid = reset($uid);
        }

        $data->fill($inputs);
        $data->status = FacebookSampleData::SUCCESS;
        $data->save();

        return redirect()->back()->with('success', 'Save Info Successfully!');
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
            FacebookSampleData::query()->delete();

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
