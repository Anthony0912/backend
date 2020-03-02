@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => 'http://localhost:4200'])
            <!-- header here -->
            Youtube Kids
        @endcomponent
    @endslot
    <center>Click on the button for authenticate your account</center>
    {{-- Subcopy --}}
    @slot('subcopy')
        @component('mail::subcopy')
            @component('mail::button', ['url' => 'http://localhost:4200/verification-account-signup?token='.$token])
                Confirm account
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

