@component('mail::message')
# Selamat bergabung dengan utomodeck,

Terimakasih telah mendaftar, mohon untuk mengkonfirmasi bahwa email <b> {{ $customer->Email }} </b> memang benar milik anda, klik tombol berikut.

@component('mail::button', ['url' => $url, 'color' => 'error'])
Konfirmasi, Ini email saya
@endcomponent

Apabila tombol tidak dapat di klik, gunakan link di bawah ini: <br>
<a href="{{ $url }}">{{ $url }}</a> <br>

Akun akan dihapus dalam jangka waktu 30 hari setelah registrasi, jika tidak di konfirmasi.

Terima kasih,<br>
Admin {{ env('APP_NAME') }}
@endcomponent
