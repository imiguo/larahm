Dear {{ $name }} ({{ $username }}),

Someone from IP address {{ $ip }} (most likely you) is trying to change your account data.

To confirm these changes please use this Confirmation Code:
{{ $confirmation_code }}

Thank you.
{{ config('app.name') }}
{{ config('app.url') }}
