<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;

class RedirController extends Controller
{
    
    public function index(Request $request) 
    {
        $params = $request->all();
        $apiKey = "c6393cff85bfb0ab4cdbc843c508a9df";
        $secretKey = "0946cc4e1a9a05e2b0f7a7a28f70bfdc";
        $shop = $params['shop'];
        $hmac = $params['hmac'];
        unset($params['hmac']);
        
        $new_hmac = hash_hmac('sha256', http_build_query($params), $secretKey);
        
        if (hash_equals($hmac, $new_hmac)) {
            $data = [
                'client_id' => $apiKey,
                'client_secret' => $secretKey,
                'code' => $params['code'],
            ];

            $response = Http::withQueryParameters($data)->withOptions(["verify"=>false])->post("https://{$shop}/admin/oauth/access_token");

            $responseData = $response->json();
            $newShop = new Tenant();
            $newShop->domain = $shop;
            $newShop->token = $responseData['access_token'];
            $newShop->save();
            
            return redirect()->to('/');
        }
    }
    
}
