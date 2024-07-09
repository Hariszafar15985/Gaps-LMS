<div>
    <!-- Let all your things have their places; let each part of your business have its time. - Benjamin Franklin -->
    <a href="{{ route($url) . (!empty($query) ? '?' . $query : '')}}" class="btn btn-primary"> {{ __($btnText) }} </a>
</div>
