@component('mail::message')
# Hallo {{ $user->name }},

Terimakasih telah mendaftar, mohon untuk mengkonfirmasi bahwa email <b> {{ $user->email }} </b> memang benar milik anda, klik tombol berikut.

@component('mail::button', ['url' => $url, 'color' => 'error'])
Konfirmasi, Ini email saya
@endcomponent

Apabila email tidak di konfirmasi, akun akan dihapus dalam jangka waktu 30 hari setelah registrasi.

Terima kasih,<br>
Admin {{ config('app.name') }}
@endcomponent
