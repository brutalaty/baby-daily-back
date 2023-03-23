<x-mail::message>
# Introduction
Hello {{ $invitation->name }},
{{ $user->name }} has invited you to the {{ $invitation->family->name }} on {{ config('app.name') }}

<x-mail::button :url="$register_url">
Register
</x-mail::button>

Or

<x-mail::button :url="$login_url">
Login
</x-mail::button>

Regards,
{{ config('app.name') }}.

</x-mail::message>