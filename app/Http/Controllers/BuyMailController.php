<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class BuyMailController extends Controller
{
    const DONGVANFB = 'dongvanfb';
    const MUAMAIL = 'muamail';
    const UNLIMITMAIL = 'unlimitmail';
    const SPTMAIL = 'sptmail';

    /**
     * Display the user's profile form.
     */
    public function index(Request $request, $page = null): Response
    {
        $render = null;
        $user = $request->user();

        switch ($page) {
            case null:
                $render = Inertia::render('Dashboard', [
                    'dongvanfb_key' => $user->dongvanfb_key ?? '',
                    'muamail_key' => $user->muamail_key ?? '',
                    'unlimitmail_key' => $user->unlimitmail_key ?? '',
                    'sptmail_key' => $user->sptmail_key ?? '',
                ]);
                break;

            case self::DONGVANFB:
                $render = Inertia::render('Dongvanfb', [
                    'api_key' => $user->dongvanfb_key ?? ''
                ]);
                break;

            case self::MUAMAIL:
                $render = Inertia::render('Muamail', [
                    'api_key' => $user->muamail_key ?? ''
                ]);
                break;

            case self::UNLIMITMAIL:
                $render = Inertia::render('UnlimitMail', [
                    'api_key' => $user->unlimitmail_key ?? ''
                ]);
                break;
            case self::SPTMAIL:
                $render = Inertia::render('SptMail', [
                    'api_key' => $user->sptmail_key ?? ''
                ]);
                break;

            default:
                abort(404);
                break;
        }

        return $render;
    }

    /**
     * Update the user's profile information.
     */
    public function applyKey(Request $request, $page): RedirectResponse
    {
        $fieldName = match ($page) {
            self::DONGVANFB => 'dongvanfb_key',
            self::MUAMAIL => 'muamail_key',
            self::UNLIMITMAIL => 'unlimitmail_key',
            self::SPTMAIL => 'sptmail_key',
            default => abort(404),
        };

        $request->user()->fill([$fieldName => $request->api_key]);
        $request->user()->save();

        return Redirect::back();
    }

    public function buyMailUnlimit(Request $request)
    {
        $user = $request->user();

        $response = Http::withOptions(['verify' => false])
            ->post("https://unlimitmail.com/api/buyHotMailUd?token={$user->unlimitmail_key}&product_id={$request->product_id}&quantity=1&type=email_pass_refresh_client");

        if ($response->failed()) {
            abort(400);
        }

        $data = $response->collect();

        if (!$data->get('status')) {
            abort(400);
        }

        return response()->json($data);
    }
}
