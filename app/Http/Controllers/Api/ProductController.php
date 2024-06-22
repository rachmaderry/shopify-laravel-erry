<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
//return type View
use Illuminate\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * index
     *
     * @return View
     */
    public function index(): View
    {
        echo "product"; exit;

    }
}
