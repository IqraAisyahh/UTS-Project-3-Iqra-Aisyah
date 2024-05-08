<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Support\Str;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $siswa = Siswa::all();
        return view('siswa.index')->with('siswa', $siswa);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $kelas = Kelas::pluck('nama_kelas', 'id_kelas'); // Menyimpan data kelas dalam format yang sesuai

        // Melewatkan data kelas ke view
        return view('siswa.create', compact('kelas'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nis' => 'required|unique:siswas,nis',
            'nama_siswa' => 'required',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required',
            'jenis_kelamin' => 'required',
            'agama' => 'required',
            'alamat' => 'required',
            'no_telp' => 'required',
            'kelas' => 'required'
        ]);

        // Dapatkan kelas yang dipilih dari request
        $kelas = $request->input('kelas');

        // Temukan kelas yang sesuai berdasarkan nilai kelas yang dipilih
        $kelasModel = Kelas::where('kelas', $kelas)->first();

        if (!$kelasModel) {
            // Handle jika kelas tidak ditemukan
            return redirect()->back()->withErrors(['kelas' => 'Kelas tidak valid']);
        }

        // Tetapkan id_kelas dari siswa ke id kelas yang sesuai
        $validatedData['id_kelas'] = $kelasModel->id_kelas;

        // Tetapkan nama_kelas dengan nilai acak sesuai dengan data kelas yang ada
        $validatedData['nama_kelas'] = $this->generateRandomNamaKelas($kelasModel->nama_kelas);

        // Simpan data siswa ke dalam database
        Siswa::create($validatedData);

        // Redirect ke halaman yang sesuai
        return redirect()->route('admin.siswa.index')->with('flash_message', 'Siswa Added!');
    }

    // Method untuk menghasilkan string acak untuk nama_kelas
    private function generateRandomNamaKelas($kelas)
    {
        // Implementasi logika Anda untuk menghasilkan nama_kelas secara acak
        return $kelas . ' ' . Str::random(2); // Misalnya, "X MIPA 1"
    }


    /**
     * Assign class automatically.
     *
     * @return int|null
     */
    private function assignClassAutomatically()
    {
        // Implement your logic here to assign class automatically
        // Misalnya, Anda bisa menggunakan logika acak untuk memilih kelas
        $randomClass = Kelas::inRandomOrder()->first();

        // Mengembalikan ID kelas atau null jika tidak ada kelas yang tersedia
        return $randomClass ? $randomClass->id_kelas : null;
    }

    public function show($nis)
    {
    $siswa = Siswa::findOrFail($nis);
    return view('siswa.show', compact('siswa'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $nis
     * @return \Illuminate\Http\Response
     */
    public function edit($nis)
    {
        $siswa = Siswa::findOrFail($nis);
        $kelas = Kelas::pluck('nama_kelas', 'id_kelas'); // Mendapatkan daftar kelas

        return view('siswa.edit', compact('siswa', 'kelas')); // Meneruskan data siswa dan kelas ke view
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $nis
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $nis)
    {
        $validatedData = $request->validate([
            'nama_siswa' => 'required',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required',
            'jenis_kelamin' => 'required',
            'agama' => 'required',
            'alamat' => 'required',
            'no_telp' => 'required',
            'id_kelas' => 'nullable|exists:kelas,id',
        ]);

        $siswa = Siswa::findOrFail($nis);
        $siswa->fill($validatedData)->save();

        return redirect()->route('admin.siswa.index')->with('flash_message', 'Siswa Updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $nis
     * @return \Illuminate\Http\Response
     */
    public function destroy($nis)
    {
        Siswa::findOrFail($nis)->delete();
        return redirect()->route('admin.siswa.index')->with('flash_message', 'Siswa deleted!');
    }
}
