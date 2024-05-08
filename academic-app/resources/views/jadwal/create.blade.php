@extends('layouts.app', ['title' => 'Add Jadwal'])

@section('content')
    <div class="header bg-gradient-primary pb-8 pt-5 pt-md-8"></div>

    <div class="container-fluid mt--7">
        <div class="row justify-content-center">
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="mb-0">Add Jadwal</h3>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif
                        <form method="POST" action="{{ route('admin.jadwal.store') }}">
                            @csrf
                            <div class="form-group">
                                <label for="id_kelas" class="id_kelas">Kelas</label>
                                <select class="form-control" name="id_kelas" id="id_kelas">
                                    @foreach($kelas as $kelasItem)
                                        <option value="{{ $kelasItem->id_kelas }}">{{ $kelasItem->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="id_mapel" class="id_mapel">Mapel</label>
                                <select class="form-control" name="id_mapel" id="id_mapel">
                                    @foreach($mapel as $mapelItem)
                                        <option value="{{ $mapelItem->id_mapel }}">{{ $mapelItem->nama_mapel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="semester" class="semester">Semester</label>
                                <input class="form-control" type="text" name="semester" id="semester">
                            </div>
                            <div class="form-group">
                                <label for="jumlah_jam" class="jumlah_jam">Jumlah Jam</label>
                                <input class="form-control" type="number" name="jumlah_jam" id="jumlah_jam">
                            </div>
                            <button type="submit" class="btn btn-primary">Tambah Jadwal</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script untuk menampilkan pesan dengan SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Cek apakah ada pesan 'success' dari redirect sebelumnya
        @if(session('success'))
            Swal.fire('{{ session('success') }}');
        @endif
    </script>
@endsection
