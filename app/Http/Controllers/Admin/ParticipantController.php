<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Services\ParticipantSheet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class ParticipantController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $participants = Participant::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(fn ($s) => $s->where('name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%"));
            })
            ->withCount('winners')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.participants.index', compact('participants', 'search'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nip'        => ['required', 'string', 'max:50', 'unique:participants,nip'],
            'name'       => ['required', 'string', 'max:150'],
            'department' => ['nullable', 'string', 'max:100'],
        ], [], ['nip' => 'NIP', 'name' => 'nama']);

        Participant::create($data + ['is_active' => true]);

        return back()->with('status', 'Peserta ditambahkan.');
    }

    public function update(Request $request, Participant $participant)
    {
        $data = $request->validate([
            'nip'        => ['required', 'string', 'max:50', Rule::unique('participants', 'nip')->ignore($participant->id)],
            'name'       => ['required', 'string', 'max:150'],
            'department' => ['nullable', 'string', 'max:100'],
            'is_active'  => ['nullable', 'boolean'],
        ], [], ['nip' => 'NIP', 'name' => 'nama']);

        $participant->update($data + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', 'Data peserta diperbarui.');
    }

    public function destroy(Participant $participant)
    {
        $participant->delete();

        return back()->with('status', 'Peserta dihapus.');
    }

    public function import(Request $request, ParticipantSheet $sheet)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
        ], [], ['file' => 'berkas']);

        try {
            $result = $sheet->import($request->file('file'), $request->boolean('replace_all'));
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $pesan = "{$result['imported']} peserta baru, {$result['updated']} diperbarui, {$result['skipped']} dilewati.";

        return back()
            ->with('status', $pesan)
            ->with('import_errors', $result['errors']);
    }

    public function export(ParticipantSheet $sheet)
    {
        return $sheet->exportCsv('peserta-'.now()->format('Ymd-His').'.csv');
    }

    public function template(ParticipantSheet $sheet)
    {
        return $sheet->template();
    }
}
