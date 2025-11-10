<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Change application locale.
     */
    public function switchLanguage($locale)
    {
        // List of supported locales
        $availableLocales = ['en', 'de'];

        if (in_array($locale, $availableLocales)) {
            Session::put('locale', $locale);
            App::setLocale($locale);
        }

        // Redirect back to the previous page
        return redirect()->back();
    }
}
