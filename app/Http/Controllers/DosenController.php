<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Coba;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Dosen;
use App\Models\Matakuliah;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\Mahasiswa;
use App\Models\Dosen_jadwal;
use App\Models\HasilStudi;
use App\Models\Materi;
use App\Models\Pesan_dosen;
use App\Models\Semester;
use App\Models\Tugas;
use App\Models\UjianMhs;
use Faker\Core\Number;
use Termwind\Components\Dd;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use function GuzzleHttp\json_decode;

class DosenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        // $idd = 2;
        // $dosen = Dosen::with('user')->where('user_id', Auth::user()->id)->get();
        // $dosen = Dosen::with('matakuliah')->where('user_id', Auth::user()->id)->get();
        $m = Matakuliah::with('dosen')->where('dosen_id', Auth::user()->id)->get();
        $detailJ = Dosen_jadwal::where('dosen_id', Auth::user()->id)->get();
        // dd($dosen);
        return view('dosen.index', compact('m', 'detailJ'));
    }
    public function qrCode()
    {
        $dosena = Dosen::where('user_id', Auth::user()->id)->first();

        $dj = Dosen_jadwal::where('dosen_id', Auth::user()->id)->first();
        $dosen = User::where('id', $dj->dosen_id)->first();

        // $u = User
        // dd($dosen);
        $qr = QrCode::generate($dosen->id);
        $coba = Coba::create([

            'mahasiswa_id' => $qr
        ]);
        $qra = Coba::find(65);
        return view('test', compact('qra'));
    }
    public function detail($id)
    {
        $id = decrypt($id);
        // $dosen = Dosen::with('kelas')->find($id);
        // $dosen = Dosen::with('kelas')->find($id);
        // $dosen_jadwal = Dosen_jadwal::with('kelas')->find($id);

        $detailJ = Dosen_jadwal::with('kelas')->where('dosen_id', Auth::user()->id)->where('kelas_id', $id)->get();

        $kelas = Kelas::find($id);
        // $mk = Matakuliah::with('kelas')->where('dosen_id', Auth::user()->id)->where('kelas_id', $id)->first();

        // $kk = Pertemuan::
        // $dosen = Dosen::with('dosen_jadwal')->where('user_id', Auth::user()->id)->get();

        // dd($detailJ);
        $mahasiswa = Mahasiswa::where('kelas_id', $kelas->id)->get();

        // $str = random_int(10000000, 99999999);
        // dd($str);





        return view('dosen.show-kelas-dosen', compact('detailJ', 'kelas'));
    }
    public function detailKelasDosen($id)
    {
        $id = decrypt($id);
        $matkulDosen = Dosen::with('kelas')->find($id);
        // $matak = Matakuliah
        return view('dosen.show-Mk-dosen', compact('matkulDosen'));
    }
    //kelas matakuliah
    public function pertemuan($id)
    {
        $id = decrypt($id);
        $absensi = Absensi::with('mahasiswa')->where('dosen_jadwal_id', $id)->get();
        $mah = 1;
        $detailJ = Dosen_jadwal::with('kelas')->where('dosen_id', Auth::user()->id)->get();
        // $detailJ = Kelas::find($detailJ->kelas_id);
        // $dosen = Kelas::with('mahasiswa')->where('kelas_id', $)->get();
        // $ef = $detailJ->where('kelas_id', 1);
        // $d =  1;
        // $m = Mahasiswa::with('kelas')->where('kelas_id', $d)->get();
        // $d = $detailJ->kelas;
        // dd($absensi);
        // $mahas = Mahasiswa::where('kelas_id',)

        return view('dosen.absensiPertemuanKelas', compact('absensi'));
    }
    public function absen($id)
    {
        $id = decrypt($id);

        $kelas = Kelas::with('mahasiswa')->find($id);
        $absensi = Absensi::with('mahasiswa', 'dosen_jadwal')->where('tanggal_absen', NULL)->find($id);
        // $m = Mahasiswa::where('user_id', $);
        // $mah = 1;
        // $dosen = Kelas::with('dosen')->find($mah);
        // $m = Matakuliah::with('kelas')->find($id);

        $detail = Dosen_jadwal::with('absensi')->where('id', $id)->find($id);

        // dd($absensi);
        return view('dosen.absensiPertemuanKelas', compact('absensi', 'detail', 'kelas'));
    }
    public function kelasAll()
    {
        // $kelas = Kelas::with('mahasiswa')->find($id);
        // $m = Dosen::with('matakuliah', 'kelas')->find($id);
        $ti2020 = 1;
        $ti2021 = 2;
        $kelas2020 = Kelas::with('mahasiswa')->find($ti2020);
        $kelas2021 = Kelas::with('mahasiswa')->find($ti2021);
        // dd($dosen);
        $p = Mahasiswa::with('kelas')->get('id');
        $a = Absensi::where('mahasiswa_id', $p);
        if (empty($a)) {
            echo $p;
        } else {
            echo "astaga";
        }
        // dd($p);
        // return view('dosen.kelas-all', compact('kelas2020', 'kelas2021'));
    }

    public function absenMhs($id)
    {
        $id = decrypt($id);

        $kelas = Kelas::with('pertemuan')->find($id);
        $absensi = Absensi::with('dosen_jadwal', 'mahasiswa')->where('jadwal_id', $id)->get();
        $mk = Matakuliah::with('dosen')->where('dosen_id', Auth::user()->id)->first();
        // dd($kelas);
        $pertemuan = Dosen_jadwal::find($id);
        // dd($absensi);

        return view('dosen.absensiPertemuanKelas', compact('absensi', 'mk', 'pertemuan'));
    }
    public function ingatkanAbsen($id)
    {
        /// spesifik di table absen
        $id = decrypt($id);
        // $absensi = Absensi::with('dosen_jadwal', 'mahasiswa')->where('id', $id)->first();

        //dump data ke mahasiswa table
        $mahasiswa = Mahasiswa::find($id);
        dd($mahasiswa);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createpertemuan($id)
    {
        //tambah pertemuan dosen
        // $kelas = Kelas::with('pertemuan')->find($id);
        $id = decrypt($id);

        $dosen = Dosen::with('dosen_jadwal')->where('user_id', Auth::user()->id)->first();
        $mkForUrut = Dosen_jadwal::with('matakuliah')->where('dosen_id', Auth::user()->id)->where('kelas_id', $id)->get();
        $kell = Kelas::with('mahasiswa')->get();
        $kelas = Kelas::find($id);
        $mk = Matakuliah::with('dosen')->where('dosen_id', Auth::user()->id)->first();

        // dd($id);


        // dd($matkulDosen);
        return view('dosen.createPertemuan', compact('dosen', 'mk', 'kelas', 'mkForUrut'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storepertemuan(Request $request, $id)
    { {

            // tmabh pertemuan
            $request->validate(
                [

                    'dosen_id' => 'required',
                    'tanggal' => 'required',
                    'jam_mk' => 'required',


                ],
                [
                    // 'pertemuan_ke.required' => 'kode MK tidak boleh kosong',
                    'dosen_id.required' => 'Harap pilih file',
                    'tanggal.required' => 'Harap pilih file',
                    'matakuliah_id.required' => 'Harap pilih file',
                    'jam_mk.required' => 'Harap pilih file',
                ]
            );
            $id = decrypt($id);
            // qr

            $file_nm = $request->file->getClientOriginalName();
            $image = $request->file->storeAs('thumbnail', $file_nm);
            // $mahasiswa = Ma
            // foreach($mahasiswa as $item)
            // $p = dosen::findOrfail($id);
            $id_kelas = encrypt($request->kelas_id);
            $kelasid = Kelas::find($id_kelas);
            $dosena = Dosen::where('user_id', Auth::user()->id)->first();

            // $dj = Dosen::where('dosen_id', Auth::user()->id)->first();
            $matakuliah = Matakuliah::find($request->dosen_mk);


            $p = Dosen::find($id);
            $p->dosen_jadwal()->create(
                [
                    'tanggal' => $request->tanggal,
                    'pertemuan_ke' => $request->pertemuan_ke,
                    'dosen_id' => $request->dosen_id,
                    'dosen_mk' => $request->dosen_mk,
                    'jam_mk' => $request->jam_mk,
                    'kelas_id' => $request->kelas_id,
                    'file_pertemuan' => $image,



                ]
            );
            // dd($request->all());
            return (redirect('kelas-detail/' . $id_kelas));
        }
    }
    public function buatabsen($id)
    {
        $id = decrypt($id);

        $dosen = Dosen_jadwal::find($id);
        $kelas = Kelas::where('id', $dosen->kelas_id)->first();
        $dosen_id = Dosen::where('user_id', Auth::user()->id)->first();

        // dd($kelas);


        return view('dosen.buat-absen', compact('dosen', 'kelas', 'dosen_id'));
    }

    public function buatabsenProses(Request $request, $id)
    {

        // dd($request->all());
        // $urll = $request->id_jadwal;
        // $urll = Dosen_jadwal::where
        $id = decrypt($id);

        $ulangi = 14;
        $p = new Absensi;
        $qr = QrCode::Size(200)->generate($request->id_jadwal);

        foreach ($request->mahasiswa as $key => $name) {
            $p->create([


                'jadwal_id' => $request->id_jadwal,
                'mahasiswa_id' => $request->mahasiswa[$key],
                'dosen_jadwal_id' => $request->dosen_mk,
                'status_absensi' => 0



            ]);
            $jd = Dosen_jadwal::find($request->id_jadwal);
            $jd->update([

                'qr_code' => $qr

            ]);
        }

        return redirect('pertemuan/' . encrypt($id));


        // return view('dosen.buat-absen', compact('dosen', 'kelas'));
    }


    public function detailMateri($id)
    {
        $id = decrypt($id);

        $materi = Materi::with('dosen_jadwal')->where('dosen_jadwal_id', $id)->get();
        $pertemuan = Dosen_jadwal::find($id);
        // dd($materi);
        // $ma = Materi::where('u')
        // $m = Materi::with()
        return view('dosen.detailMateri', compact('materi', 'pertemuan'));
    }
    public function ujian()
    {
        // $ujian = Matakuliah::with('dosen')->where('dosen_id', Auth::user()->id)->get();
        // dd($ujian);
        $ujian = Dosen::with('kelas')->where('id', Auth::user()->id)->first();
        // dd($ujian);
        // $kelas = Kelas::with('matakuliah')->where('dosen_id', $ujian->id)->first();
        // dd($ujian);

        // dd($mk);
        return view('dosen.ujian', compact('ujian'));
    }
    //
    public function ujianActive($id, $nama_kelas)
    {
        // dd($u);
        // $kelas_id = $nama_kelas;
        // $id = decrypt($id);

        $kelas = Kelas::with('Mahasiswa')->where('id', $id)->first();
        // dd($kelas);
        $mk = kelas::with('matakuliah', 'dosen')->where('id', $id)->where('nama_kelas', $nama_kelas)->first();
        // dd($mk);
        $auth = Auth::user()->id;
        $matkul = Matakuliah::where('dosen_id', $auth)->first();
        $dosen = Dosen::where('user_id', $auth)->first();
        // $ujian = Dosen::with('kelas')->where('id', Auth::user()->id)->first();
        // dd($matkul);
        return view('dosen.ujian-create', compact('kelas', 'mk', 'dosen', 'matkul'));
    }
    public function prosesActive(Request $request)
    {
        $file_nm = $request->file->getClientOriginalName();
        $f = $request->file->storeAs('thumbnail', $file_nm);

        $p = new UjianMhs();
        foreach ($request->mahasiswa as $key => $name) {

            $p->create([

                'mahasiswa_id' => $request->mahasiswa[$key],
                'matakuliah_id' => $request->matakuliah_id,
                'soal_ujian' => $f,
                'kelas_id' => $request->kelas_id,
                'dosen_id' => $request->dosen_id,
                'type_ujian' => $request->type_ujian,
                'semester_id' => $request->semester_id[$key],
                'status_ujian' => 2,
                'tanggal_ujian' => $request->tanggal_ujian

            ]);
        }

        return redirect('/ujian-mhs');
    }
    public function listMhsUjian($id, $nama_kelas)
    {
        // $id = decrypt($id);

        $auth = Auth::user()->id;
        $m1 = UjianMhs::with('mahasiswa', 'semester')
            ->where('kelas_id', $nama_kelas)
            ->where('dosen_id', $auth)
            ->where('type_ujian', 'UTS')
            ->where('status_ujian', 2)

            ->paginate(4);

        $m2 = UjianMhs::with('mahasiswa', 'semester')
            ->where('kelas_id', $nama_kelas)
            ->where('dosen_id', $auth)
            ->where('status_ujian', 2)

            ->where('type_ujian', 'UAS')

            ->paginate(4);
        // dd($m1);
        return view('dosen.list-ujian-mhs', compact('m1', 'm2'));
    }

    //
    public function submitNilai($id, $mk_id, $semester_id)
    {
        // $id = 'tembak dulu';
        // $mk_id = 'tembak dulu';
        // $id = decrypt($id);
        $mahasiswa = Mahasiswa::find($id);

        $matakuliah = Matakuliah::with('dosen')->where('id', $mk_id)->first();
        $semester = Semester::find($semester_id);
        // dd($semester);


        return view('dosen.submit-nilai', compact('mahasiswa', 'matakuliah', 'semester'));
    }
    public function prosesNilai(Request $request)
    {

        $p = new HasilStudi;
        if ($request->type_ujian == "UTS") {
            #jika select inputnya uts
            $p->create([

                'mahasiswa_id' => $request->mahasiswa_id,
                'matakuliah_id' => $request->matakuliah_id,
                'semester_id' => $request->semester_id,
                'dosen_id' => $request->dosen_id,
                'nilai_uts' => $request->nilai
            ]);
        } elseif ($request->type_ujian == "UAS") {
            #jika select inputnya uas
            $mhs_id = $request->mahasiswa_id;
            $pp = HasilStudi::where('mahasiswa_id', $mhs_id,)
                ->where('matakuliah_id', $request->matakuliah_id)
                ->where('dosen_id', $request->dosen_id)
                ->first();
            $pp->update([

                // 'mahasiswa_id' => $request->mahasiswa_id,
                // 'matakuliah_id' => $request->matakuliah_id,
                // 'semester_id' => $request->semester_id,
                // 'dosen_id' => $request->dosen_id,
                'nilai_uas' => $request->nilai
            ]);
        }
        return redirect('/ujian-mhs');





        // return view('dosen.submit-nilai', compact('mahasiswa', 'matakuliah', 'semester'));
    }
    public function prosesMateri(Request $request, $id)
    {
        // $id = decrypt($id);
        $ur = encrypt($id);
        $p = new Materi();
        $p->insert([
            'dosen_jadwal_id' => $request->dosen_jadwal_id,
            'judul_materi' => $request->judul_materi,
            'teser_materi' => $request->teaser_materi,
            'penugasan' => $request->penugasan,

        ]);
        // return redirect('materi_detail/' . $ur);
        return redirect()->back();
    }
    public function detailPenugasan($id)
    {

        $id_kelas = decrypt($id);

        $auth = Auth::user()->id;
        $dosen = Dosen::where('id', $auth)->first();
        $dj = Dosen_jadwal::with('tugas_mhs', 'materi')->where('dosen_mk', $auth)->where('kelas_id', $id_kelas)->get();
        // dd($id_kelas);

        $ma = Tugas::with('mahasiswa', 'materi', 'dosen_jadwal')->where('dosen_id', $auth)->where('kelas_id', $id_kelas)->get();

        return view('dosen.penugasan-mhs', compact('dj', 'dosen', 'id', 'ma'));
    }
    public function validasiUts(Request $request, $id)
    {
        $ujian = UjianMhs::where('type_ujian', $id)->where('dosen_id', Auth::user()->id);

        foreach ($request->ujian as $key => $name) {

            $ujian->update([

                // 'matakuliah_id' => $request->krs[$key],
                // 'mahasiswa_id' => $request->mahasiswa_id,
                'status_ujian' => 1,

            ]);
        }
        return redirect()->back();
    }
    public function kotakMasuk()
    {

        $p = Pesan_dosen::where('dosen_id', Auth::user()->id)->get();
        // dd($p);
        return view('dosen.kotak-masuk', compact('p'));
    }
    public function updateStatus(Request $request)
    {
        $p = Pesan_dosen::find($request->id);
        $p->keterangan = $request->keterangan;
        $p->save();

        // return redirect('/data-pesanan');
        return response()->json([]);
    }
}