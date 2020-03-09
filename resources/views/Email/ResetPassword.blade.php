@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => 'http://localhost:4200/login'])
            <!-- header here -->
            Youtube Kids
        @endcomponent
    @endslot
    <center>Click on the button below to change password</center>
    {{-- Subcopy --}}
    @slot('subcopy')
        @component('mail::subcopy')
            @component('mail::button', ['url' => 'http://localhost:4200/response-password-reset?verify='.$verify])
                Reset Password
            @endcomponent
        @endcomponent
    @endslot


    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            <!-- footer here -->
            © {{ date('Y') }} Youtube Kids. The support team of Youtube Kids.
        @endcomponent
    @endslot
@endcomponent
