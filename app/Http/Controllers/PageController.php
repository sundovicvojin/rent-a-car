<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessage;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PageController extends Controller
{
    public function contact()
    {
        return view('pages.contact');
    }

    public function sendContact(ContactRequest $request)
    {
        $data = $request->safe()->only(['name', 'email', 'phone', 'message']);
        $to = CompanySetting::current()->email ?: config('mail.from.address');

        try {
            Mail::to($to)->send(new ContactMessage($data));
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->withErrors(['message' => 'Poruka trenutno nije poslata. Pokušajte ponovo ili nas pozovite.']);
        }

        return redirect()->route('contact')->with('sent', true);
    }

    public function terms()
    {
        return view('pages.terms');
    }

    public function privacy()
    {
        return view('pages.privacy');
    }
}
