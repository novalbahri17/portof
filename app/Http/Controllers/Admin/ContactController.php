<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/Contacts', [
            'contacts' => Contact::latest()->get(),
        ]);
    }

    public function markAsRead(Contact $contact)
    {
        $contact->update(['read' => true]);
        return back();
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return back()->with('success', 'Pesan berhasil dihapus.');
    }
}
