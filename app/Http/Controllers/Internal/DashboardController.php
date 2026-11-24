<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return match (session('peran')) {
            'fo' => $this->dashboardFo(),
            'tix' => $this->dashboardTix(),
            'fin' => $this->dashboardFin(),
            'mgr' => $this->dashboardMgr(),
            default => abort(403),
        };
    }

    protected function dashboardFo()
    {
        $menungguBayar = DB::table('reservasi_penginapan')->where('status_reservasi', 'Dipesan')->count();
        $dibayarBelumCheckin = DB::table('reservasi_penginapan')->where('status_reservasi', 'Dibayar')->count();
        $sedangMenginap = DB::table('reservasi_penginapan')->where('status_reservasi', 'Check-in')->count();
        $unitPerawatan = DB::table('unit_penginapan')->where('status_unit', 'Perawatan')->count();

        $perluDitindaklanjuti = DB::table('reservasi_penginapan as r')
            ->join('pelanggan as p', 'p.id_pelanggan', '=', 'r.id_pelanggan')
            ->join('layanan as l', 'l.id_layanan', '=', 'r.id_layanan')
            ->whereIn('r.status_reservasi', ['Dipesan', 'Dibayar', 'Check-in'])
            ->orderBy('r.tgl_check_in')
            ->select('r.id_reservasi', 'p.nama as nama_pelanggan', 'l.nama_layanan', 'r.no_unit',
                'r.tgl_check_in', 'r.tgl_check_out', 'r.status_reservasi')
            ->get();

        return view('internal.dashboard.fo', compact(
            'menungguBayar', 'dibayarBelumCheckin', 'sedangMenginap', 'unitPerawatan', 'perluDitindaklanjuti'
        ));
    }

    protected function dashboardTix()
    {
        $jumlahTransaksi = DB::table('transaksi')->count();

        $pendapatan = DB::table('detail_transaksi as d')
            ->join('layanan as l', 'l.id_layanan', '=', 'd.id_layanan')
            ->where('l.jenis_layanan', '!=', 'Penginapan')
            ->sum(DB::raw('d.jumlah * d.harga_satuan'));

        $perKanal = DB::table('transaksi')
            ->select('kanal_pemesanan', DB::raw('count(*) as jumlah'))
            ->groupBy('kanal_pemesanan')
            ->get();

        $perMetode = DB::table('transaksi')
            ->select('metode_bayar', DB::raw('count(*) as jumlah'))
            ->groupBy('metode_bayar')
            ->get();

        $terbaru = DB::table('transaksi as t')
            ->join('pelanggan as p', 'p.id_pelanggan', '=', 't.id_pelanggan')
            ->orderByDesc('t.waktu_transaksi')
            ->limit(5)
            ->select('t.id_transaksi', 'p.nama as nama_pelanggan', 't.waktu_transaksi', 't.kanal_pemesanan', 't.metode_bayar')
            ->get();

        return view('internal.dashboard.tix', compact(
            'jumlahTransaksi', 'pendapatan', 'perKanal', 'perMetode', 'terbaru'
        ));
    }

    protected function dashboardFin()
    {
        $totalPendapatan = DB::table('detail_transaksi')->sum(DB::raw('jumlah * harga_satuan'));
        $jumlahTransaksi = DB::table('transaksi')->count();
        $reservasiDibayar = DB::table('reservasi_penginapan')->where('status_reservasi', 'Dibayar')->count();
        $reservasiDipesan = DB::table('reservasi_penginapan')->where('status_reservasi', 'Dipesan')->count();

        $perMetode = DB::table('transaksi')
            ->select('metode_bayar', DB::raw('count(*) as jumlah'))
            ->groupBy('metode_bayar')
            ->get();

        $menungguBayar = DB::table('reservasi_penginapan as r')
            ->join('pelanggan as p', 'p.id_pelanggan', '=', 'r.id_pelanggan')
            ->join('layanan as l', 'l.id_layanan', '=', 'r.id_layanan')
            ->where('r.status_reservasi', 'Dipesan')
            ->orderBy('r.tgl_check_in')
            ->select('r.id_reservasi', 'p.nama as nama_pelanggan', 'l.nama_layanan', 'r.no_unit',
                'r.tgl_check_in', 'r.tgl_check_out')
            ->get();

        return view('internal.dashboard.fin', compact(
            'totalPendapatan', 'jumlahTransaksi', 'reservasiDibayar', 'reservasiDipesan', 'perMetode', 'menungguBayar'
        ));
    }

    protected function dashboardMgr()
    {
        $totalUnit = DB::table('unit_penginapan')->count();
        $unitTerisi = DB::table('unit_penginapan')->where('status_unit', 'Terisi')->count();
        $unitTersedia = DB::table('unit_penginapan')->where('status_unit', 'Tersedia')->count();
        $okupansi = $totalUnit > 0 ? round($unitTerisi / $totalUnit * 100, 1) : 0;

        $totalReservasi = DB::table('reservasi_penginapan')->count();

        $pendapatanPenginapan = DB::table('detail_transaksi as d')
            ->join('layanan as l', 'l.id_layanan', '=', 'd.id_layanan')
            ->where('l.jenis_layanan', 'Penginapan')
            ->sum(DB::raw('d.jumlah * d.harga_satuan'));

        $pendapatanTiketPaket = DB::table('detail_transaksi as d')
            ->join('layanan as l', 'l.id_layanan', '=', 'd.id_layanan')
            ->where('l.jenis_layanan', '!=', 'Penginapan')
            ->sum(DB::raw('d.jumlah * d.harga_satuan'));

        $perStatusReservasi = DB::table('reservasi_penginapan')
            ->select('status_reservasi', DB::raw('count(*) as jumlah'))
            ->groupBy('status_reservasi')
            ->get();

        $perStatusUnit = DB::table('unit_penginapan')
            ->select('status_unit', DB::raw('count(*) as jumlah'))
            ->groupBy('status_unit')
            ->get();

        return view('internal.dashboard.mgr', compact(
            'okupansi', 'unitTersedia', 'totalUnit', 'totalReservasi',
            'pendapatanPenginapan', 'pendapatanTiketPaket', 'perStatusReservasi', 'perStatusUnit'
        ));
    }
}
