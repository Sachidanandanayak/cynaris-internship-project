<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Display the authenticated user's account details and verification overview.
     */
    public function index(Request $request): View
    {
        return view('account', [
            'user' => $request->user(),
        ]);
    }
}
