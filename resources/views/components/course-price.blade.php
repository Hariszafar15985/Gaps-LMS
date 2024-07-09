<div>
    @if (isset($authUser) && $authUser->isUser())
        {{-- dont show price for students --}}
    @else
        {{ $slot }}
    @endif
</div>
