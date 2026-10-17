<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Support\PerPage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Contact Leads: messages visitors send through the website contact form.
class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $messages = ContactMessage::query()
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($q).'%'])
                ->orWhereRaw('LOWER(email) LIKE ?', ['%'.mb_strtolower($q).'%'])
                ->orWhere('phone', 'like', '%'.$q.'%')
                ->orWhereRaw('LOWER(message) LIKE ?', ['%'.mb_strtolower($q).'%'])))
            ->when($status === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when(isset(ContactMessage::STATUSES[$status]), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(PerPage::from($request))
            ->withQueryString();

        $counts = ContactMessage::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.messages.index', compact('messages', 'counts', 'q', 'status'));
    }

    public function show(ContactMessage $message)
    {
        if (! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

        $message->load('user');

        return view('admin.messages.show', compact('message'));
    }

    public function updateStatus(Request $request, ContactMessage $message)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(ContactMessage::STATUSES))],
        ]);

        $message->update($data);

        return back()->with('status', 'Lead marked as '.$message->statusLabel().'.');
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('status', 'Lead deleted.');
    }
}
