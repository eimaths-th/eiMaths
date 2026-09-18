<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UiLocaleController extends Controller
{
    public function Update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(config('ui-localization.supported_locales'))],
            'user_id' => ['prohibited'],
            'id' => ['prohibited'],
        ]);

        $request->user('web')->forceFill([
            'language_code' => $validated['locale'],
        ])->save();

        $return_url = url()->previous(route('home'));
        $origin_prefix = $request->getSchemeAndHttpHost() . '/';

        if (
            ! str_starts_with($return_url, $origin_prefix)
            || $return_url === route('locale.update')
        ) {
            $return_url = route('home');
        }

        return redirect()->to($return_url);
    }
}
