<a href="{{ $params}}"
    @if(!empty($linkTarget))
        target='{{ $linkTarget }}'
    @endif
    class="btn custom-style {{ !empty($addClass) ? 'saveProgress' : '' }} btn-sm {{ !empty($params) ? 'btn-primary' : 'btn-gray disabled' }}">
    {{ $slot }}
</a>
