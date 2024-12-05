@component('mail::message')
# Permintaan ubah password,

Jika anda tidak merasa mengajukan permintaan ubah password, abaikan email ini, jika ya, silahkan melanjutkan dengan klik tombol di bawah.

@component('mail::button', ['url' => $url, 'color' => 'error'])
Ubah Password
@endcomponent

Apabila tombol tidak dapat di klik, gunakan link di bawah ini: <br>
<a href="{{ $url }}">{{ $url }}</a> <br>

Terima kasih,<br>
Admin {{ env('APP_NAME') }}
@endcomponent
