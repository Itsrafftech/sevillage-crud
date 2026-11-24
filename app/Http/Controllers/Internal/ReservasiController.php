<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Services\IdService;
use App\Services\PesanErrorService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservasiController extends Controller
{
    protected array $statusList = ['Dipesan', 'Dibayar', 'Check-in', 'Selesai', 'Dibatalkan'];

    protected array $kanalList = ['Loket', 'WhatsApp', 'Tiket.com'];

    public function index()
    {
        $daftar = DB::table('v_reservasi as r')
            ->join('pelanggan as p', 'p.id_pelanggan', '=', 'r.id_pelanggan')
            ->join('layanan as l', 'l.id_layanan', '=', 'r.id_layanan')
            ->orderByDesc('r.id_reservasi')
            ->select(
                'r.id_reservasi', 'p.nama as nama_pelanggan', 'l.nama_layanan', 'r.no_unit',
                'r.tgl_check_in', 'r.tgl_check_out', 'r.lama_menginap', 'r.jumlah_tamu', 'r.status_reservasi'
            )
            ->get();

        return view('internal.reservasi.index', compact('daftar'));
    }

    public function create()
    {
        return view('internal.reservasi.form', [
            'reservasi' => null,
            'idBaru' => IdService::next('reservasi_penginapan', 'id_reservasi', 'R'),
            'pelangganList' => $this->daftarPelanggan(),
            'unitList' => $this->daftarUnit(),
            'statusList' => $this->statusList,
            'kanalList' => $this->kanalList,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }

        try {
            DB::transaction(function () use ($data) {
                DB::table('reservasi_penginapan')->insert([
                    'id_reservasi' => IdService::next('reservasi_penginapan', 'id_reservasi', 'R'),
                    'id_pelanggan' => $data['id_pelanggan'],
                    'id_layanan' => $data['id_layanan'],
                    'no_unit' => $data['no_unit'],
                    'id_karyawan' => session('id_karyawan'),
                    'tgl_check_in' => $data['tgl_check_in'],
                    'tgl_check_out' => $data['tgl_check_out'],
                    'jumlah_tamu' => $data['jumlah_tamu'],
                    'jenis_kendaraan' => $data['jenis_kendaraan'] ?: null,
                    'kanal_pemesanan' => $data['kanal_pemesanan'],
                    'status_reservasi' => $data['status_reservasi'],
                ]);
            });
        } catch (QueryException $e) {
            return back()->withInput()->with('pesan_error', PesanErrorService::dariException($e));
        }

        return redirect()->route('internal.reservasi.index')->with('pesan_sukses', 'Reservasi berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $reservasi = DB::table('reservasi_penginapan')->where('id_reservasi', $id)->first();

        if (! $reservasi) {
            abort(404);
        }

        return view('internal.reservasi.form', [
            'reservasi' => $reservasi,
            'idBaru' => null,
            'pelangganList' => $this->daftarPelanggan(),
            'unitList' => $this->daftarUnit(),
            'statusList' => $this->statusList,
            'kanalList' => $this->kanalList,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $reservasiLama = DB::table('reservasi_penginapan')->where('id_reservasi', $id)->first();

        if (! $reservasiLama) {
            abort(404);
        }

        $data = $this->validasi($request, $id);

        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }

        try {
            DB::transaction(function () use ($data, $id) {
                DB::table('reservasi_penginapan')->where('id_reservasi', $id)->update([
                    'id_pelanggan' => $data['id_pelanggan'],
                    'id_layanan' => $data['id_layanan'],
                    'no_unit' => $data['no_unit'],
                    'id_karyawan' => session('id_karyawan'),
                    'tgl_check_in' => $data['tgl_check_in'],
                    'tgl_check_out' => $data['tgl_check_out'],
                    'jumlah_tamu' => $data['jumlah_tamu'],
                    'jenis_kendaraan' => $data['jenis_kendaraan'] ?: null,
                    'kanal_pemesanan' => $data['kanal_pemesanan'],
                    'status_reservasi' => $data['status_reservasi'],
                ]);
            });
        } catch (QueryException $e) {
            return back()->withInput()->with('pesan_error', PesanErrorService::dariException($e));
        }

        return redirect()->route('internal.reservasi.index')->with('pesan_sukses', 'Reservasi berhasil diperbarui.');
    }

    public function konfirmasiHapus(string $id)
    {
        $reservasi = DB::table('v_reservasi as r')
            ->join('pelanggan as p', 'p.id_pelanggan', '=', 'r.id_pelanggan')
            ->join('layanan as l', 'l.id_layanan', '=', 'r.id_layanan')
            ->where('r.id_reservasi', $id)
            ->select('r.*', 'p.nama as nama_pelanggan', 'l.nama_layanan')
            ->first();

        if (! $reservasi) {
            abort(404);
        }

        return view('internal.reservasi.hapus', compact('reservasi'));
    }

    public function destroy(string $id)
    {
        $reservasi = DB::table('reservasi_penginapan')->where('id_reservasi', $id)->first();

        if (! $reservasi) {
            abort(404);
        }

        try {
            DB::table('reservasi_penginapan')->where('id_reservasi', $id)->delete();
        } catch (QueryException $e) {
            return back()->with('pesan_error', PesanErrorService::dariException($e));
        }

        return redirect()->route('internal.reservasi.index')->with('pesan_sukses', 'Reservasi berhasil dihapus.');
    }

    public function tandaiDibayar(string $id)
    {
        $reservasi = DB::table('reservasi_penginapan')->where('id_reservasi', $id)->first();

        if (! $reservasi) {
            abort(404);
        }

        if ($reservasi->status_reservasi !== 'Dipesan') {
            return back()->with('pesan_error', 'Hanya reservasi berstatus Dipesan yang dapat ditandai Dibayar.');
        }

        try {
            DB::transaction(function () use ($reservasi) {
                $idTransaksiBaru = IdService::next('transaksi', 'id_transaksi', 'T');

                DB::table('transaksi')->insert([
                    'id_transaksi' => $idTransaksiBaru,
                    'id_pelanggan' => $reservasi->id_pelanggan,
                    'id_karyawan' => session('id_karyawan'),
                    'waktu_transaksi' => now(),
                    'kanal_pemesanan' => $reservasi->kanal_pemesanan,
                    'metode_bayar' => 'Transfer',
                ]);

                $malam = (new \DateTime($reservasi->tgl_check_in))->diff(new \DateTime($reservasi->tgl_check_out))->days;
                $harga = \App\Services\TarifService::harga($reservasi->id_layanan, $reservasi->tgl_check_in) ?? 0;

                DB::table('detail_transaksi')->insert([
                    'id_transaksi' => $idTransaksiBaru,
                    'no_urut' => 1,
                    'id_layanan' => $reservasi->id_layanan,
                    'jumlah' => max(1, $malam),
                    'harga_satuan' => $harga,
                ]);

                DB::table('reservasi_penginapan')->where('id_reservasi', $reservasi->id_reservasi)->update([
                    'id_transaksi' => $idTransaksiBaru,
                    'status_reservasi' => 'Dibayar',
                ]);
            });
        } catch (QueryException $e) {
            return back()->with('pesan_error', PesanErrorService::dariException($e));
        }

        return back()->with('pesan_sukses', 'Reservasi '.$id.' berhasil ditandai Dibayar.');
    }

    // -------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------

    protected function daftarPelanggan()
    {
        return DB::table('pelanggan')
            ->where('is_tamu_penginapan', true)
            ->orderBy('nama')
            ->get();
    }

    protected function daftarUnit()
    {
        return DB::table('unit_penginapan as u')
            ->join('layanan as l', 'l.id_layanan', '=', 'u.id_layanan')
            ->orderBy('l.nama_layanan')
            ->orderBy('u.no_unit')
            ->select('u.id_layanan', 'u.no_unit', 'u.status_unit', 'l.nama_layanan')
            ->get();
    }

    /**
     * Validasi input form reservasi. Mengembalikan array data valid,
     * atau RedirectResponse berisi pesan error bila tidak valid.
     */
    protected function validasi(Request $request, ?string $idSaatIni = null)
    {
        $pesanKustom = [
            'id_pelanggan.required' => 'Pelanggan wajib dipilih.',
            'unit.required' => 'Unit wajib dipilih.',
            'tgl_check_in.required' => 'Tanggal check-in wajib diisi.',
            'tgl_check_out.required' => 'Tanggal check-out wajib diisi.',
            'jumlah_tamu.required' => 'Jumlah tamu wajib diisi.',
            'jumlah_tamu.integer' => 'Jumlah tamu harus berupa angka.',
            'jumlah_tamu.min' => 'Jumlah tamu minimal 1 orang.',
            'kanal_pemesanan.required' => 'Kanal pemesanan wajib dipilih.',
            'status_reservasi.required' => 'Status reservasi wajib dipilih.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'id_pelanggan' => 'required|string',
            'unit' => 'required|string',
            'tgl_check_in' => 'required|date',
            'tgl_check_out' => 'required|date',
            'jumlah_tamu' => 'required|integer|min:1',
            'jenis_kendaraan' => 'nullable|string|max:20',
            'kanal_pemesanan' => 'required|in:'.implode(',', $this->kanalList),
            'status_reservasi' => 'required|in:'.implode(',', $this->statusList),
        ], $pesanKustom);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $data = $validator->validated();

        [$idLayanan, $noUnit] = array_pad(explode('|', $data['unit']), 2, null);

        if (! $idLayanan || $noUnit === null) {
            return back()->withInput()->with('pesan_error', 'Unit yang dipilih tidak valid.');
        }

        $unit = DB::table('unit_penginapan')
            ->where('id_layanan', $idLayanan)
            ->where('no_unit', $noUnit)
            ->first();

        if (! $unit) {
            return back()->withInput()->with('pesan_error', 'Unit yang dipilih tidak ditemukan.');
        }

        if (strtotime($data['tgl_check_out']) <= strtotime($data['tgl_check_in'])) {
            return back()->withInput()->with('pesan_error', 'Tanggal check-out harus setelah tanggal check-in.');
        }

        if ($unit->status_unit === 'Perawatan') {
            return back()->withInput()->with('pesan_error', 'Unit ini sedang dalam perawatan dan tidak dapat dipesan.');
        }

        $tipe = DB::table('tipe_penginapan')->where('id_layanan', $idLayanan)->first();
        if ($tipe && $data['jumlah_tamu'] > $tipe->kapasitas) {
            return back()->withInput()->with('pesan_error',
                'Jumlah tamu ('.$data['jumlah_tamu'].') melebihi kapasitas unit ('.$tipe->kapasitas.' orang).');
        }

        $bentrokQuery = DB::table('reservasi_penginapan')
            ->where('id_layanan', $idLayanan)
            ->where('no_unit', $noUnit)
            ->whereIn('status_reservasi', ['Dipesan', 'Dibayar', 'Check-in'])
            ->where('tgl_check_in', '<', $data['tgl_check_out'])
            ->where('tgl_check_out', '>', $data['tgl_check_in']);

        if ($idSaatIni) {
            $bentrokQuery->where('id_reservasi', '!=', $idSaatIni);
        }

        $bentrok = $bentrokQuery->first();

        if ($bentrok) {
            return back()->withInput()->with('pesan_error',
                'Tanggal bentrok dengan reservasi '.$bentrok->id_reservasi.' pada unit yang sama.');
        }

        $data['id_layanan'] = $idLayanan;
        $data['no_unit'] = $noUnit;

        return $data;
    }
}
