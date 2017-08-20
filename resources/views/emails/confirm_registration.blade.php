Hello {{ $name }},

Thank you for registering in our program
Please confirm your registration or ignore this message.

Copy and paste this link to your browser:
{{ config('app.url') }}/?a=confirm_registration&c={{ $confirm_string }}

Thank you.
{{ config('app.name') }}
