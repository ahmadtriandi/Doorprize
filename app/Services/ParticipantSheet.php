<?php

namespace App\Services;

use App\Models\Participant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Baca/tulis daftar peserta dalam format CSV maupun Excel (.xlsx/.xls).
 *
 * Format kolom yang dikenali (baris pertama = judul kolom, urutan bebas):
 *   nip | nama | unit
 * Sinonim yang juga diterima: name, nama_lengkap, no_induk, nik, departemen, divisi, bagian.
 */
class ParticipantSheet
{
    private const HEADER_MAP = [
        'nip'           => 'nip',
        'no_induk'      => 'nip',
        'nomor_induk'   => 'nip',
        'nik'           => 'nip',
        'id'            => 'nip',
        'nama'          => 'name',
        'name'          => 'name',
        'nama_lengkap'  => 'name',
        'nama_peserta'  => 'name',
        'unit'          => 'department',
        'departemen'    => 'department',
        'department'    => 'department',
        'divisi'        => 'department',
        'bagian'        => 'department',
    ];

    /**
     * @return array{imported:int, updated:int, skipped:int, errors:array<string>}
     */
    public function import(UploadedFile $file, bool $replaceAll = false): array
    {
        $rows = $this->readRows($file);

        if (empty($rows)) {
            throw new RuntimeException('File kosong atau tidak bisa dibaca.');
        }

        $header = array_map(fn ($h) => Str::of((string) $h)->trim()->lower()->replace(' ', '_')->toString(), array_shift($rows));
        $columns = [];

        foreach ($header as $i => $label) {
            if (isset(self::HEADER_MAP[$label])) {
                $columns[self::HEADER_MAP[$label]] = $i;
            }
        }

        if (! isset($columns['name'])) {
            throw new RuntimeException('Kolom "nama" tidak ditemukan pada baris pertama file.');
        }
        if (! isset($columns['nip'])) {
            throw new RuntimeException('Kolom "nip" tidak ditemukan pada baris pertama file.');
        }

        $result = ['imported' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        DB::transaction(function () use ($rows, $columns, $replaceAll, &$result) {
            if ($replaceAll) {
                Participant::query()->delete();
            }

            foreach ($rows as $line => $row) {
                $nip  = trim((string) ($row[$columns['nip']] ?? ''));
                $name = trim((string) ($row[$columns['name']] ?? ''));
                $dept = isset($columns['department']) ? trim((string) ($row[$columns['department']] ?? '')) : null;

                if ($nip === '' && $name === '') {
                    continue; // baris kosong
                }

                if ($name === '') {
                    $result['skipped']++;
                    $result['errors'][] = 'Baris '.($line + 2).': nama kosong.';
                    continue;
                }

                if ($nip === '') {
                    $result['skipped']++;
                    $result['errors'][] = 'Baris '.($line + 2).': NIP kosong.';
                    continue;
                }

                $existing = Participant::where('nip', $nip)->first();

                if ($existing) {
                    $existing->update(['name' => $name, 'department' => $dept ?: $existing->department]);
                    $result['updated']++;
                } else {
                    Participant::create([
                        'nip'        => $nip,
                        'name'       => $name,
                        'department' => $dept ?: null,
                        'is_active'  => true,
                    ]);
                    $result['imported']++;
                }
            }
        });

        $result['errors'] = array_slice($result['errors'], 0, 20);

        return $result;
    }

    /** Unduh daftar peserta sebagai CSV (dibuka langsung oleh Excel). */
    public function exportCsv(string $filename = 'peserta.csv'): StreamedResponse
    {
        $rows = Participant::orderBy('name')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM supaya Excel membaca UTF-8

            fputcsv($out, ['nip', 'nama', 'unit', 'status']);

            foreach ($rows as $p) {
                fputcsv($out, [$p->nip, $p->name, $p->department, $p->is_active ? 'aktif' : 'nonaktif']);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Template kosong untuk diisi panitia. */
    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['nip', 'nama', 'unit']);
            fputcsv($out, ['198701012010011001', 'Ditha Paramitha', 'Produksi']);
            fputcsv($out, ['198903152012012002', 'Yunus Anggoro Muchlis', 'Maintenance']);
            fclose($out);
        }, 'template-peserta.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array<int, array<int, mixed>> */
    private function readRows(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if (in_array($ext, ['xlsx', 'xls'], true)) {
            return $this->readSpreadsheet($file->getRealPath());
        }

        return $this->readCsv($file->getRealPath());
    }

    private function readCsv(string $path): array
    {
        $content = file_get_contents($path);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        $temp = fopen('php://temp', 'r+');
        fwrite($temp, $content);
        rewind($temp);

        // Deteksi pemisah: koma atau titik koma (Excel Indonesia)
        $firstLine = strtok($content, "\n");
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $rows = [];
        while (($row = fgetcsv($temp, 0, $delimiter)) !== false) {
            $rows[] = $row;
        }
        fclose($temp);

        return $rows;
    }

    private function readSpreadsheet(string $path): array
    {
        if (! class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new RuntimeException(
                'Untuk membaca file Excel, jalankan: composer require phpoffice/phpspreadsheet. '.
                'Atau simpan file sebagai CSV lalu unggah ulang.'
            );
        }

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $sheet = $reader->load($path)->getActiveSheet();

        return $sheet->toArray(null, true, false, false);
    }
}
