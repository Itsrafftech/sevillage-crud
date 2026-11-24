<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Services\IdService;
use App\Services\PesanErrorService;
use App\Services\TarifService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransaksiController extends Controller
{
    protected array $metodeList = ['Tunai', 'Transfer', 'Debit'];

    protected array $kanalList = ['Loket', 'WhatsApp'];

    public function index()
    {
        $transaksi = DB::table('transaksi as t')
            ->join('pelanggan as p', 'p.id_pelanggan', '=', 't.id_pelanggan')
            ->orderByDesc('t.id_transaksi')
            ->select('t.id_transaksi', 'p.nama as nama_pelanggan', 't.waktu_transaksi',
                't.kanal_pemesanan', 't.metode_bayar')
            ->get();

        foreach ($transaksi as $t) {
            $items = DB::table('detail_transaksi as d')
                ->join('layanan as l', 'l.id_layanan', '=', 'd.id_layanan')
                ->where('d.id_transaksi', $t->id_transaksi)
                ->orderBy('d.no_urut')
                ->select('l.nama_layanan', 'd.jumlah', 'd.harga_satuan')
                ->get();

            $t->total = $items->sum(fn ($i) => $i->jumlah * $i->harga_satuan);

            if ($items->isEmpty()) {
                $t->ringkasan = '-';
            } else {
                $pertama = $items->first();
                $ringkasan = $pertama->nama_layanan.' x '.$pertama->jumlah;
                if ($items->count() > 1) {
                    $ringkasan .= ' +'.($items->count() - 1).' lainnya';
                }
                $t->ringkasan = $ringkasan;
            }
        }

        return view('internal.transaksi.index', compact('transaksi'));
    }

    public function create()
    {
        return view('internal.transaksi.create', [
            'pelangganList' => DB::table('pelanggan')->orderBy('nama')->get(),
            'layananList' => $this->daftarLayananTiketPaket(),
            'metodeList' => $this->metodeList,
            'kanalList' => $this->kanalList,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pelanggan' => 'required|string|exists:pelanggan,id_pelanggan',
            'id_layanan' => 'required|string',
            'jumlah' => 'required|integer|min:1',
            'tgl_kunjungan' => 'required|date',
            'metode_bayar' => 'required|in:'.implode(',', $this->metodeList),
            'kanal_pemesanan' => 'required|in:'.implode(',', $this->kanalList),
        ], [
            'id_pelanggan.required' => 'Pelanggan wajib dipilih.',
            'id_layanan.required' => 'Tiket/paket wajib dipilih.',
            'jumlah.required' => 'Jumlah wajib diisi.',
            'jumlah.min' => 'Jumlah minimal 1.',
            'tgl_kunjungan.required' => 'Tanggal kunjungan wajib diisi.',
            'metode_bayar.required' => 'Metode bayar wajib dipilih.',
            'kanal_pemesanan.required' => 'Kanal pemesanan wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $data = $validator->validated();

        if (strtotime($data['tgl_kunjungan']) < strtotime(date('Y-m-d'))) {
            return back()->withInput()->with('pesan_error', 'Tanggal kunjungan tidak boleh sebelum hari ini.');
        }

        $layanan = DB::table('layanan')->where('id_layanan', $data['id_layanan'])->first();
        if (! $layanan) {
            return back()->withInput()->with('pesan_error', 'Layanan tidak ditemukan.');
        }

        if ($layanan->jenis_layanan === 'Paket') {
            $paket = DB::table('paket')->where('id_layanan', $data['id_layanan'])->first();
            $minPax = $paket->min_pax ?? 20;
            if ($data['jumlah'] < $minPax) {
                return back()->withInput()->with('pesan_error', 'Paket ini membutuhkan minimal '.$minPax.' orang.');
            }
        }

        $harga = TarifService::harga($data['id_layanan'], $data['tgl_kunjungan']);
        if ($harga === null) {
            return back()->withInput()->with('pesan_error', 'Tarif untuk layanan ini belum tersedia pada tanggal tersebut.');
        }

        try {
            DB::transaction(function () use ($data, $harga) {
                $idTransaksi = IdService::next('transaksi', 'id_transaksi', 'T');

                DB::table('transaksi')->insert([
                    'id_transaksi' => $idTransaksi,
                    'id_pelanggan' => $data['id_pelanggan'],
                    'id_karyawan' => session('id_karyawan'),
                    'waktu_transaksi' => now(),
                    'kanal_pemesanan' => $data['kanal_pemesanan'],
                    'metode_bayar' => $data['metode_bayar'],
                ]);

                DB::table('detail_transaksi')->insert([
                    'id_transaksi' => $idTransaksi,
                    'no_urut' => 1,
                    'id_layanan' => $data['id_layanan'],
                    'jumlah' => $data['jumlah'],
                    'harga_satuan' => $harga,
                ]);

                for ($i = 0; $i < $data['jumlah']; $i++) {
                    DB::table('tiket')->insert([
                        'no_tiket' => IdService::next('tiket', 'no_tiket', 'TK'),
                        'id_transaksi' => $idTransaksi,
                        'no_urut' => 1,
                        'tgl_kunjungan' => $data['tgl_kunjungan'],
                        'status_tiket' => 'Aktif',
                    ]);
                }
            });
        } catch (QueryException $e) {
            return back()->withInput()->with('pesan_error', PesanErrorService::dariException($e));
        }

        return redirect()->route('internal.transaksi.index')->with('pesan_sukses', 'Transaksi berhasil disimpan.');
    }

    public function edit(string $id)
    {
        $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->first();

        if (! $transaksi) {
            abort(404);
        }

        $items = DB::table('detail_transaksi as d')
            ->join('layanan as l', 'l.id_layanan', '=', 'd.id_layanan')
            ->where('d.id_transaksi', $id)
            ->select('l.nama_layanan', 'd.jumlah', 'd.harga_satuan')
            ->get();

        return view('internal.transaksi.edit', [
            'transaksi' => $transaksi,
            'items' => $items,
            'pelangganList' => DB::table('pelanggan')->orderBy('nama')->get(),
            'metodeList' => $this->metodeList,
            'kanalList' => $this->kanalList,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->first();

        if (! $transaksi) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'id_pelanggan' => 'required|string|exists:pelanggan,id_pelanggan',
            'metode_bayar' => 'required|in:'.implode(',', $this->metodeList),
            'kanal_pemesanan' => 'required|in:Loket,WhatsApp,Tiket.com',
        ], [
            'id_pelanggan.required' => 'Pelanggan wajib dipilih.',
            'metode_bayar.required' => 'Metode bayar wajib dipilih.',
            'kanal_pemesanan.required' => 'Kanal pemesanan wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        try {
            DB::table('transaksi')->where('id_transaksi', $id)->update($validator->validated());
        } catch (QueryException $e) {
            return back()->withInput()->with('pesan_error', PesanErrorService::dariException($e));
        }

        return redirect()->route('internal.transaksi.index')->with('pesan_sukses', 'Transaksi berhasil diperbarui.');
    }

    public function konfirmasiHapus(string $id)
    {
        $transaksi = DB::table('transaksi as t')
            ->join('pelanggan as p', 'p.id_pelanggan', '=', 't.id_pelanggan')
            ->where('t.id_transaksi', $id)
            ->select('t.*', 'p.nama as nama_pelanggan')
            ->first();

        if (! $transaksi) {
            abort(404);
        }

        return view('internal.transaksi.hapus', compact('transaksi'));
    }

    public function destroy(string $id)
    {
        $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->first();

        if (! $transaksi) {
            abort(404);
        }

        try {
            DB::transaction(function () use ($id) {
                DB::table('tiket')->where('id_transaksi', $id)->delete();
                DB::table('detail_transaksi')->where('id_transaksi', $id)->delete();
                DB::table('transaksi')->where('id_transaksi', $id)->delete();
            });
        } catch (QueryException $e) {
            return back()->with('pesan_error', PesanErrorService::dariException($e));
        }

        return redirect()->route('internal.transaksi.index')->with('pesan_sukses', 'Transaksi berhasil dihapus.');
    }

    protected function daftarLayananTiketPaket()
    {
        return DB::table('layanan')
            ->whereIn('jenis_layanan', ['Tiket Masuk', 'Paket'])
            ->where('status_aktif', true)
            ->orderBy('jenis_layanan')
            ->orderBy('nama_layanan')
            ->get();
    }
}
