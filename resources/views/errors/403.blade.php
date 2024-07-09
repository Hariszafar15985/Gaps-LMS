@extends(getTemplate().'.layouts.app')

@section('content')
    @php
        $get403ErrorPageSettings = get403ErrorPageSettings();
    @endphp

    <section class="my-50 container text-center">
        <div class="row justify-content-md-center">
            <div class="col col-md-6">
                <img src="{{ $get403ErrorPageSettings['error_image'] ?? '' }}" class="img-cover " alt="">
            </div>
        </div>

        <h2 class="mt-25 font-36">{{ $get403ErrorPageSettings['error_title'] ?? '' }}</h2>
        <p class="mt-25 font-16">{{ $get403ErrorPageSettings['error_description'] ?? '' }}</p>
    </section>
@endsection
