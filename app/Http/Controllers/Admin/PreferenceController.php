<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PreferenceController extends Controller
{
    public function theme(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'theme' => ['required', Rule::in(['dark', 'light'])],
        ]);

        $request->user()->forceFill(['theme' => $data['theme']])->save();

        return back(303);
    }
}
