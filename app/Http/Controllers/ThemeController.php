<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ThemeController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate(['theme' => ['required', Rule::in(['light', 'dark'])]]);
        $request->user()->update($data);

        return back();
    }
}
