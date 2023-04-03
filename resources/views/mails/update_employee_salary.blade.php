@component("mail::message")

{{-- # {{ __('auth.welcome', ['name' => $name]) }}!! --}}
{{-- # {{Hello Mr. $name}} --}}
{{-- @component('mail::button', ['url' => 'https://google.com'])
Button Text
@endcomponent --}}

@component('mail::panel')
Update salary
@endcomponent

## Table component:

@component('mail::table')
| employee       | old-salary         | new salary  |
| ------------- |:-------------:| --------:|
| {{$name}}      |       | {{$salary}}      | 500

@endcomponent


{{-- @component('mail::subcopy')
This is a subcopy component
@endcomponent --}}

Thanks, <br>
{{ config('app.name') }}

@endcomponent