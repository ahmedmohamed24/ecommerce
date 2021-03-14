@component('mail::message')
# Reset Passowrd

You requested to reset your password

@component('mail::button', ['url' => $url])
Reset
@endcomponent
if you did not request to reset your password, then ignore this message!

Thanks,<br>
{{ config('app.name') }}
@endcomponent

