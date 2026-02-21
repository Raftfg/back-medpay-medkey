<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\StoreContactRequest;
use App\Mail\Contact\ContactMessageSubmittedMail;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('contact');
    }

    public function store(StoreContactRequest $request): JsonResponse|RedirectResponse
    {
        $payload = $request->validated();

        Mail::to(config('custom.contact_to_address'))
            ->send(new ContactMessageSubmittedMail($payload));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Votre message a bien été envoyé.',
            ]);
        }

        return redirect()
            ->route('contact')
            ->with('status', 'Votre message a bien été envoyé.');
    }
}
