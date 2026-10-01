<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Models\User;
use App\Models\UserAlert;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact', [
            'subjects' => ContactMessage::SUBJECTS,
            'contact'  => config('panel.contact'),
        ]);
    }

    public function store(StoreContactMessageRequest $request)
    {
        $message = ContactMessage::create($request->safe()->except('website') + [
            'ip_address' => $request->ip(),
        ]);

        $this->notifyTeam($message);

        return redirect()
            ->route('contact')
            ->with('contact_success', 'Merci ! Votre message a bien été envoyé. La cellule informatique vous répondra dans les meilleurs délais.');
    }

    private function notifyTeam(ContactMessage $message): void
    {
        $recipients = User::whereHas('roles.permissions', fn ($q) => $q->where('title', 'contact_message_access'))->pluck('id');

        if ($recipients->isEmpty()) {
            return;
        }

        $alert = UserAlert::create([
            'alert_text' => 'Nouveau message de '.$message->name.' : '.$message->subject_label,
            'alert_link' => route('admin.contact-messages.show', $message),
        ]);

        $alert->users()->sync($recipients);
    }
}
