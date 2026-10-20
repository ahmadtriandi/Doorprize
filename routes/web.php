<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\PickController;
use App\Http\Controllers\Admin\ParticipantController;
use App\Http\Controllers\Admin\PrizeController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\WinnerController;
use App\Http\Controllers\DrawController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OperatorController;
use App\Http\Middleware\OperatorAuth;
use Illuminate\Support\Facades\Route;

// ---------- Layar undian (dibuka di proyektor, tanpa login) ----------
Route::get('/', [DrawController::class, 'index'])->name('draw');
Route::post('/undi/{prize}', [DrawController::class, 'spin'])->name('draw.spin');
Route::get('/undi/{prize}/state', [DrawController::class, 'state'])->name('draw.state');
Route::post('/pilih/{prize}', [DrawController::class, 'select'])->name('draw.select');
Route::get('/pemenang', [DrawController::class, 'winners'])->name('draw.winners');
Route::get('/sesi/{sesi}', [DrawController::class, 'session'])->name('draw.session');
Route::post('/sesi/{sesi}/undi', [DrawController::class, 'sessionSpin'])->name('draw.session.spin');

// ---------- Masuk panitia ----------
Route::get('/masuk', [LoginController::class, 'show'])->name('login');
Route::post('/masuk', [LoginController::class, 'store'])->middleware('throttle:10,1');
Route::post('/keluar', [LoginController::class, 'destroy'])->name('logout');

// ---------- Halaman operator (hanya membaca) ----------
Route::middleware(OperatorAuth::class)->prefix('operator')->name('operator.')->group(function () {
    Route::get('/', [OperatorController::class, 'index'])->name('home');
    Route::get('pemenang', [OperatorController::class, 'winners'])->name('winners');

    // Operator boleh membatalkan pemenang, mengulang satu hadiah, dan
    // menghapus seluruh pemenang. "reset-semua" harus di atas {winner}.
    Route::delete('pemenang/reset-semua', [\App\Http\Controllers\Admin\WinnerController::class, 'resetAll'])->name('winners.resetAll');
    Route::delete('pemenang/hadiah/{prize}/reset', [\App\Http\Controllers\Admin\WinnerController::class, 'reset'])->name('winners.reset');
    Route::delete('pemenang/{winner}', [\App\Http\Controllers\Admin\WinnerController::class, 'destroy'])->name('winners.destroy');
});

// ---------- Dashboard panitia ----------
Route::middleware('admin')->prefix('panel')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Peserta
    Route::get('peserta', [ParticipantController::class, 'index'])->name('participants.index');
    Route::post('peserta', [ParticipantController::class, 'store'])->name('participants.store');
    Route::put('peserta/{participant}', [ParticipantController::class, 'update'])->name('participants.update');
    Route::delete('peserta/{participant}', [ParticipantController::class, 'destroy'])->name('participants.destroy');
    Route::post('peserta/impor', [ParticipantController::class, 'import'])->name('participants.import');
    Route::get('peserta/ekspor', [ParticipantController::class, 'export'])->name('participants.export');
    Route::get('peserta/template', [ParticipantController::class, 'template'])->name('participants.template');

    // Hadiah
    Route::get('hadiah', [PrizeController::class, 'index'])->name('prizes.index');
    Route::post('hadiah', [PrizeController::class, 'store'])->name('prizes.store');
    Route::put('hadiah/{prize}', [PrizeController::class, 'update'])->name('prizes.update');
    Route::delete('hadiah/{prize}', [PrizeController::class, 'destroy'])->name('prizes.destroy');
    Route::post('hadiah/{prize}/pilih', [PrizeController::class, 'select'])->name('prizes.select');

    // Posisi pemenang yang dikunci panitia
    Route::get('hadiah/{prize}/pemenang', [PickController::class, 'index'])->name('picks.index');
    Route::get('hadiah/{prize}/pemenang/cari', [PickController::class, 'search'])->name('picks.search');
    Route::post('hadiah/{prize}/pemenang', [PickController::class, 'store'])->name('picks.store');
    Route::post('hadiah/{prize}/pemenang/massal', [PickController::class, 'bulk'])->name('picks.bulk');
    Route::put('hadiah/{prize}/pemenang/{pick}', [PickController::class, 'reposition'])->name('picks.reposition');
    Route::delete('hadiah/{prize}/pemenang/{pick}', [PickController::class, 'destroy'])->name('picks.destroy');

    // Sesi undian: banyak hadiah sekali undi
    Route::get('sesi', [SessionController::class, 'index'])->name('sessions.index');
    Route::post('sesi', [SessionController::class, 'store'])->name('sessions.store');
    Route::get('sesi/{sesi}', [SessionController::class, 'show'])->name('sessions.show');
    Route::put('sesi/{sesi}', [SessionController::class, 'update'])->name('sessions.update');
    Route::delete('sesi/{sesi}', [SessionController::class, 'destroy'])->name('sessions.destroy');
    Route::delete('sesi/{sesi}/reset', [SessionController::class, 'reset'])->name('sessions.reset');

    // Grup pengecualian
    Route::get('grup', [GroupController::class, 'index'])->name('groups.index');
    Route::post('grup', [GroupController::class, 'store'])->name('groups.store');
    Route::get('grup/{group}', [GroupController::class, 'show'])->name('groups.show');
    Route::put('grup/{group}', [GroupController::class, 'update'])->name('groups.update');
    Route::delete('grup/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');
    Route::put('grup/{group}/hadiah', [GroupController::class, 'syncPrizes'])->name('groups.prizes');
    Route::get('grup/{group}/cari', [GroupController::class, 'search'])->name('groups.search');
    Route::post('grup/{group}/anggota', [GroupController::class, 'addMember'])->name('groups.members.add');
    Route::post('grup/{group}/anggota-massal', [GroupController::class, 'bulkMembers'])->name('groups.members.bulk');
    Route::delete('grup/{group}/anggota/{participant}', [GroupController::class, 'removeMember'])->name('groups.members.remove');

    // Pemenang
    Route::get('pemenang', [WinnerController::class, 'index'])->name('winners.index');
    Route::get('pemenang/ekspor', [WinnerController::class, 'export'])->name('winners.export');
    Route::post('pemenang/{winner}/ambil', [WinnerController::class, 'claim'])->name('winners.claim');
    // Harus di atas pemenang/{winner}, kalau tidak "reset-semua" akan
    // dianggap sebagai id pemenang dan menghasilkan 404.
    Route::delete('pemenang/reset-semua', [WinnerController::class, 'resetAll'])->name('winners.resetAll');
    Route::delete('pemenang/{winner}', [WinnerController::class, 'destroy'])->name('winners.destroy');
    Route::delete('pemenang/hadiah/{prize}/reset', [WinnerController::class, 'reset'])->name('winners.reset');

    // Tampilan layar
    Route::get('tampilan', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('tampilan', [SettingController::class, 'update'])->name('settings.update');
    Route::delete('tampilan/gambar/{key}', [SettingController::class, 'removeImage'])->name('settings.image.remove');
});
