<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Http;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class BuyMailController extends Controller
{
    const DONGVANFB = 'dongvanfb';
    const MUAMAIL = 'muamail';
    const UNLIMITMAIL = 'unlimitmail';

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
                    'api_key' => $request->user()->dongvanfb_key ?? ''
                ]);
                break;

            case self::DONGVANFB:
                $render = Inertia::render('Dongvanfb', [
                    'api_key' => $request->user()->dongvanfb_key ?? ''
                ]);
                break;

            case self::MUAMAIL:
                $render = Inertia::render('Muamail', [
                    'api_key' => $request->user()->muamail_key ?? ''
                ]);
                break;

            case self::UNLIMITMAIL:
                $render = Inertia::render('UnlimitMail', [
                    'api_key' => $request->user()->unlimitmail_key ?? ''
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

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
