<?php

namespace App\Http\Controllers;

//return type View
use Illuminate\View\View;

use Illuminate\Http\Request;

class InstallController extends Controller
{
    
    public function index(Request $request)
    {
        $_API_KEY = "c6393cff85bfb0ab4cdbc843c508a9df";
        $_NGROK_URL = "https://5163-36-68-11-113.ngrok-free.app";
        $shop = $_GET['shop'];
        $scopes = 'read_products,write_products,read_orders,write_orders';
        $redirect_uri = $_NGROK_URL . '/redir';
        $nonce = bin2hex( random_bytes(12));
        $access_mode = 'per-user';
        $oauth_url = 'https://' . $shop . '/admin/oauth/authorize?client_id=' . $_API_KEY . '&scope=' . $scopes . '&redirect_uri=' . urlencode($redirect_uri) . '&state=' . $nonce . '&grant_options[]=' . $access_mode;
        
        return redirect()->to($oauth_url);
    }
}
