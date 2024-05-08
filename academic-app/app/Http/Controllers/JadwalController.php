<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class JadwalController extends Controller
{
    public function index()
        {
            // Fetch data from the database
            $jadwals = Jadwal::with('mapel')->get();
            $kelas = Kelas::all();

            // Group jadwals by 'hari'
            $groupedJadwals = $jadwals->groupBy('hari');

            // Define the days and lesson times
            $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
            $jamPelajaran = [
                "07:30:00 - 08:20:00",
                "08:20:00 - 09:10:00",
                "09:10:00 - 10:00:00",
                "10:00:00 - 10:20:00",
                "10:20:00 - 11:10:00",
                "11:10:00 - 12:00:00",
                "12:00:00 - 13:00:00",
                "13:00:00 - 13:50:00",
                "13:50:00 - 14:40:00",
                "14:40:00 - 15:30:00"
            ];

            // Pass the necessary data to the view
            return view('jadwal.index', compact('jadwals', 'kelas', 'groupedJadwals', 'hari', 'jamPelajaran'));
        }

    public function create()
    {
        $kelas = Kelas::all();
        $mapel = Mapel::all();
        return view('jadwal.create', compact('kelas', 'mapel'));
    }

        public function generate(Request $request)
    {
        $request->validate([
            'id_kelas' => 'required',
            'id_mapel' => 'required',
            'semester' => 'required',
        ]);

        $id_kelas = $request->input('id_kelas');
        $id_mapel = $request->input('id_mapel');
        $semester = $request->input('semester');
        $jumlah_jam = $request->input('jumlah_jam');

        $jadwal = $this->generateJadwal($id_kelas, $id_mapel, $semester, $jumlah_jam);

        // Mengalihkan ke tampilan yang benar
        return view('jadwal.index', ['jadwal' => $jadwal]);
    }


    private function generateJadwal($id_kelas, $id_mapel, $semester, $jumlah_jam)
    {
    $newJadwal = [];
    $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    $jamPelajaran = [
        "07.30 - 08.20", "08.20 - 09.10", "09.10 - 10.00",
        "10.00 - 10.20 (Istirahat)", "10.20 - 11.10", "11.10 - 12.00",
        "12.00 - 13.00 (Istirahat)", "13.00 - 13.50", "13.50 - 14.40",
        "14.40 - 15.30"
    ];

    // Randomize the order of days
    shuffle($hari);

    // Initialize arrays to track scheduled hours for each subject
    $scheduledHours = [];
    foreach ($jamPelajaran as $jam) {
        $scheduledHours[$jam] = 0;
    }

    // Iterate over each day
    foreach ($hari as $h) {
        // Iterate over each lesson time slot
        foreach ($jamPelajaran as $jam) {
            // Check if the subject has already been scheduled for the maximum number of hours
            if ($scheduledHours[$jam] >= 3) {
                continue; // Skip this time slot
            }

            // Check if there's still time left to schedule
            if ($jumlah_jam <= 0) {
                break 2; // Exit both loops
            }

            // Check if there's no existing schedule for this time slot
            $jadwalExist = Jadwal::where('id_kelas', $id_kelas)
                ->where('hari', $h)
                ->where('jam_masuk', explode(' - ', $jam)[0])
                ->first();

            if (!$jadwalExist) {
                // Add the new schedule
                $newJadwal[$h][] = $jam;
                $scheduledHours[$jam]++;
                $jumlah_jam--;
            }
        }
    }

    return $newJadwal;
}

    private function storeJadwalToDatabase($input, $jumlah_jam)
    {
    $newJadwal = [];
    $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    shuffle($hari);

    $jamPelajaran = [
        "07:30 - 08:20",
        "08:20 - 09:10",
        "09:10 - 10:00",
        "10:20 - 11:10",
        "11:10 - 12:00",
        "13:00 - 13:50",
        "13:50 - 14:40",
        "14:40 - 15:30"
    ];

    // Initialize arrays to track scheduled hours for each subject
    $scheduledHours = [];
    foreach ($jamPelajaran as $jam) {
        $scheduledHours[$jam] = 0;
    }

    // Hitung jumlah hari yang akan digunakan
    $jumlah_hari = ceil($jumlah_jam / 8);

    // Ambil data dari request
    $id_kelas = $input['id_kelas'];
    $id_mapel = $input['id_mapel'];
    $semester = $input['semester'];

    // Simpan jadwal ke dalam database
    foreach ($hari as $h) {
        // Jika sudah mencapai jumlah hari yang dibutuhkan
        if ($jumlah_hari <= 0) {
            break;
        }

        // Iterate over each lesson time slot
        foreach ($jamPelajaran as $jam) {
            // Check if the subject has already been scheduled for the maximum number of hours
            if ($scheduledHours[$jam] >= 3) {
                continue; // Skip this time slot
            }

            // Check if there's no existing schedule for this time slot
            $jadwalExist = Jadwal::where('id_kelas', $id_kelas)
                ->where('hari', $h)
                ->where('jam_masuk', explode(' - ', $jam)[0])
                ->exists();

            if (!$jadwalExist) {
                // Add the new schedule
                $jam_masuk_keluar = explode(' - ', $jam);
                $jam_masuk = $jam_masuk_keluar[0];
                $jam_keluar = $jam_masuk_keluar[1];

                // Simpan ke dalam database menggunakan model Jadwal
                $newJadwal = new Jadwal();
                $newJadwal->id_kelas = $id_kelas;
                $newJadwal->id_mapel = $id_mapel;
                $newJadwal->semester = $semester;
                $newJadwal->hari = $h;
                $newJadwal->jam_masuk = $jam_masuk;
                $newJadwal->jam_keluar = $jam_keluar;
                $newJadwal->save();

                // Update scheduled hours
                $scheduledHours[$jam]++;
                $jumlah_jam--;

                // If the required number of hours is met, break the loop
                if ($jumlah_jam <= 0) {
                    break 2; // Exit both loops
                }
            }
        }
    }
}

public function store(Request $request)
{
    // Validasi input
    $request->validate([
        'id_kelas' => 'required',
        'id_mapel' => 'required',
        'semester' => 'required',
        'jumlah_jam' => 'required|numeric|min:1|max:8', // Jumlah jam pelajaran maksimal 8
    ]);

    $id_kelas = $request->input('id_kelas');

    // Periksa apakah kelas sudah memiliki jadwal penuh
    if ($this->isJadwalPenuh($id_kelas)) {
        return redirect()->route('admin.jadwal.index')->with('error', 'Jadwal pada kelas ini sudah penuh');
    }

    // Mendapatkan nilai jumlah_jam dari input
    $jumlah_jam = $request->input('jumlah_jam');

    // Jika jumlah jam lebih dari 3, bagi menjadi dua bagian
    if ($jumlah_jam > 3) {
        // Bagian pertama: 3 jam pada hari-hari berbeda
        $this->storeJadwalToDatabase($request->all(), 3);

        // Bagian kedua: sisanya ditempatkan pada hari-hari berbeda yang tersisa
        $this->storeJadwalToDatabase($request->all(), $jumlah_jam - 3);
    } else {
        // Jika jumlah jam tidak lebih dari 3, simpan langsung ke database
        $this->storeJadwalToDatabase($request->all(), $jumlah_jam);
    }

    // Redirect ke halaman daftar jadwal dengan pesan sukses
    return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil ditambahkan.');
}

private function isJadwalPenuh($id_kelas)
{
    // Periksa apakah ada jadwal untuk setiap hari dan jam pada kelas tersebut
    $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    $jamPelajaran = [
        "07:30:00 - 08:20:00",
        "08:20:00 - 09:10:00",
        "09:10:00 - 10:00:00",
        "10:00:00 - 10:20:00",
        "10:20:00 - 11:10:00",
        "11:10:00 - 12:00:00",
        "12:00:00 - 13:00:00",
        "13:00:00 - 13:50:00",
        "13:50:00 - 14:40:00",
        "14:40:00 - 15:30:00"
    ];

    foreach ($hari as $h) {
        foreach ($jamPelajaran as $jam) {
            // Periksa apakah jadwal sudah ada untuk setiap hari dan jam pada kelas
            $jadwalExist = Jadwal::where('id_kelas', $id_kelas)
                ->where('hari', $h)
                ->where('jam_masuk', explode(' - ', $jam)[0])
                ->exists();

            if (!$jadwalExist) {
                return false; // Jika ada jadwal yang belum ada, maka kelas belum penuh
            }
        }
    }

    return true; // Jika tidak ada jadwal yang belum ada, maka kelas sudah penuh
}


    public function show($id_kelas)
{
    $jadwals = Jadwal::where('id_kelas', $id_kelas)->get();

    // Mengelompokkan jadwal berdasarkan hari
    $groupedJadwals = [];
    foreach ($jadwals as $jadwal) {
        if (!isset($groupedJadwals[$jadwal->hari])) {
            $groupedJadwals[$jadwal->hari] = [];
        }
        $groupedJadwals[$jadwal->hari][] = $jadwal;
    }

    $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    $jamPelajaran = [
        "07:30:00 - 08:20:00",
        "08:20:00 - 09:10:00",
        "09:10:00 - 10:00:00",
        "10:00:00 - 10:20:00",
        "10:20:00 - 11:10:00",
        "11:10:00 - 12:00:00",
        "12:00:00 - 13:00:00",
        "13:00:00 - 13:50:00",
        "13:50:00 - 14:40:00",
        "14:40:00 - 15:30:00"
    ];

    return view('jadwal.show', compact('jadwals'));
}

public function edit($id_kelas)
{
    $kelas = Kelas::findOrFail($id_kelas);
    // Mengambil jadwal untuk kelas tertentu
    $jadwals = Jadwal::where('id_kelas', $id_kelas)->get();
    return view('jadwal.edit', compact('jadwals', 'kelas'));
}

public function update(Request $request, $id_kelas)
{
    $request->validate([
        'id_mapel' => 'required',
        'semester' => 'required',
    ]);

    // Mengambil jadwal untuk kelas tertentu
    $jadwals = Jadwal::where('id_kelas', $id_kelas)->get();
    foreach ($jadwals as $jadwal) {
        $jadwal->id_mapel = $request->id_mapel;
        $jadwal->semester = $request->semester;
        $jadwal->save();
    }

    return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil diperbarui.');
}
public function destroy($id_kelas)
{
    // Hapus semua jadwal yang terkait dengan id_kelas
    Jadwal::where('id_kelas', $id_kelas)->delete();

    // Redirect dengan pesan sukses
    return redirect()->route('admin.jadwal.index')->with('success', 'Semua jadwal untuk kelas ini berhasil dihapus.');
}




}
