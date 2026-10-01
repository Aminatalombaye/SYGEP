<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyContactMessageRequest;
use App\Models\ContactMessage;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class ContactMessageController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('contact_message_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $contactMessages = ContactMessage::latest()->get();

        return view('admin.contactMessages.index', compact('contactMessages'));
    }

    public function show(ContactMessage $contactMessage)
    {
        abort_if(Gate::denies('contact_message_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if (! $contactMessage->isRead()) {
            $contactMessage->update(['read_at' => now()]);
        }

        return view('admin.contactMessages.show', compact('contactMessage'));
    }

    public function destroy(ContactMessage $contactMessage)
    {
        abort_if(Gate::denies('contact_message_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $contactMessage->delete();

        return back();
    }

    public function massDestroy(MassDestroyContactMessageRequest $request)
    {
        ContactMessage::whereIn('id', request('ids'))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
