@component('mail::message')
# {{__('verify-new-email::messages.Verify New Email Address')}}

{{__('verify-new-email::messages.Please click the button below to verify your new email address')}}.

@component('mail::button', ['url' => $url])
{{__('verify-new-email::messages.Verify New Email Address')}}
@endcomponent

{{__('verify-new-email::messages.If you did not update your email address, no further action is required')}}.

{{__('verify-new-email::messages.Thanks')}},<br>
{{ config('app.name') }}
@endcomponent
