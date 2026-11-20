<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePemesananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // form publik, tanpa login
    }

    /** Samakan format nomor: 0812-xxx / +62 812 xxx -> 62812xxx */
    protected function prepareForValidation(): void
    {
        $no = preg_replace('/\D+/', '', (string) $this->input('no_whatsapp'));
        if (str_starts_with($no, '0')) {
            $no = '62'.substr($no, 1);
        } elseif (str_starts_with($no, '8')) {
            $no = '62'.$no;
        }
        $this->merge(['no_whatsapp' => $no]);
    }

    public function rules(): array
    {
        return [
            'nama'          => ['required', 'string', 'min:3', 'max:150'],
            'no_whatsapp'   => ['required', 'regex:/^628[0-9]{8,12}$/'],
            'layanan_id'    => ['required', Rule::exists('layanan', 'id')->whereNull('deleted_at')],
            'tanggal_acara' => ['required', 'date', 'after_or_equal:today'],
            'jumlah'        => ['nullable', 'integer', 'min:1', 'max:100000'],
            'lokasi_acara'  => ['nullable', 'string', 'max:255'],
            'catatan'       => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required'                 => 'Nama wajib diisi.',
            'nama.min'                      => 'Nama minimal 3 karakter.',
            'no_whatsapp.required'          => 'Nomor WhatsApp wajib diisi.',
            'no_whatsapp.regex'             => 'Nomor WhatsApp tidak valid (contoh: 0812xxxxxxxx).',
            'layanan_id.required'           => 'Silakan pilih layanan.',
            'layanan_id.exists'             => 'Layanan yang dipilih tidak tersedia.',
            'tanggal_acara.required'        => 'Tanggal acara wajib diisi.',
            'tanggal_acara.date'            => 'Format tanggal tidak valid.',
            'tanggal_acara.after_or_equal'  => 'Tanggal acara tidak boleh sebelum hari ini.',
            'jumlah.min'                    => 'Jumlah minimal 1.',
        ];
    }
}
