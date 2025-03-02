<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FacebookUidController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('FacebookUid');
    }

    public function getFacebookUid(Request $request)
    {
        $urls = $request->input('input');

        $client = new Client([
            'verify' => false,
            'headers' => ['User-Agent' => 'Mozilla/5.0']
        ]);

        $promises = [];
        $uids = [];

        foreach ($urls as $key => $url) {
            // Nếu URL đã có UID, lấy ngay không cần request
            if (preg_match('/id=(\d+)/', $url, $matches)) {
                $uids[$key] = $matches[1];
            } else {
                $promises[$key] = $client->headAsync($url, ['allow_redirects' => false]);
            }
        }

        // Chỉ gửi request nếu có URL cần kiểm tra
        if (!empty($promises)) {
            $responses = Utils::settle($promises)->wait();

            foreach ($responses as $key => $result) {
                if ($result['state'] !== 'fulfilled') {
                    $uids[$key] = null;
                    continue;
                }

                $redirectUrl = $result['value']->getHeaderLine('Location');

                if (preg_match('/id=(\d+)/', $redirectUrl, $matches)) {
                    $uids[$key] = $matches[1];
                } else {
                    $uids[$key] = null;
                }
            }
        }

        // Sắp xếp lại theo thứ tự index ban đầu
        ksort($uids);

        return array_values($uids);
    }
}
