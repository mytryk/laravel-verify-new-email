@component('mail::message')
# {{__('verify-new-email::messages.Verify Email Address')}}

{{__('verify-new-email::messages.Please click the button below to verify your email address')}}.

@component('mail::button', ['url' => $url])
    {{__('verify-new-email::messages.Verify Email Address')}}
@endcomponent

{{__('verify-new-email::messages.If you did not create an account, no further action is required')}}.

{{__('verify-new-email::messages.Thanks')}},<br>
{{ config('app.name') }}
@endcomponent
