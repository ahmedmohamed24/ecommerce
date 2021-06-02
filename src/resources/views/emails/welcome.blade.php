@component('mail::message')
# Welcome to our E-commerce platform!

you should activate your email, to make sure its you.
@component('mail::button', ['url' => ''])
activate
@endcomponent

Thanks,{{ $email }}<br>
{{ config('app.name') }}
@endcomponent
