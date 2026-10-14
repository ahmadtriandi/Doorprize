<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk — Panitia Doorprize</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Segoe UI',system-ui,sans-serif;background:#241B4A;display:grid;place-items:center;min-height:100vh;padding:20px;color:#241B4A}
  .kartu{background:#fff;border-radius:16px;padding:32px;width:min(380px,100%)}
  h1{font-size:20px;margin-bottom:4px}
  p.sub{color:#6B6390;font-size:14px;margin-bottom:22px}
  label{display:block;font-size:13px;font-weight:600;margin-bottom:5px}
  input{width:100%;padding:9px 12px;border:1px solid #E4E0F2;border-radius:8px;font:inherit;margin-bottom:14px}
  input:focus{outline:2px solid #6D28D9;outline-offset:1px}
  button{width:100%;padding:10px;border:0;border-radius:8px;background:#6D28D9;color:#fff;font:inherit;font-weight:600;cursor:pointer}
  .galat{background:#FEE2E2;color:#991B1B;padding:10px 13px;border-radius:8px;font-size:14px;margin-bottom:16px}
  .kembali{display:block;text-align:center;margin-top:16px;font-size:13px;color:#6B6390}
</style>
</head>
<body>
  <form class="kartu" method="post" action="{{ route('login') }}">
    @csrf
    <h1>Panel panitia</h1>
    <p class="sub">Masuk untuk mengatur peserta, hadiah, dan layar undian.</p>

    @if($errors->any())<div class="galat">{{ $errors->first() }}</div>@endif
    @if(session('error'))<div class="galat">{{ session('error') }}</div>@endif

    <label for="username">Nama pengguna</label>
    <input id="username" name="username" value="{{ old('username') }}" autofocus required>

    <label for="password">Kata sandi</label>
    <input id="password" name="password" type="password" required>

    <button>Masuk</button>
    <a class="kembali" href="{{ route('draw') }}">Lihat layar undian</a>
  </form>
</body>
</html>
