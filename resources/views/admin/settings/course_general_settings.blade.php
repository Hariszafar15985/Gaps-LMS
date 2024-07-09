@extends('admin.layouts.app')

@push('styles_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Course General {{ trans('admin/main.settings') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="/admin/">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item active"><a href="/admin/settings">{{ trans('admin/main.settings') }}</a></div>
                <div class="breadcrumb-item ">Course</div>
            </div>
        </div>

        <div class="section-body">

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                           <form action="{{route('settings.course.customization.store')}}" method="POST">
                            @csrf
                                <div class="form-group custom-switches-stacked">
                                    <label class="custom-switch pl-0">
                                        <input type="checkbox" name="status" id="status-Switch" value="{{ old('status') ? old('status') : (isset($status) && isset($status->value) ? '1' : '') }}"
                                        class="custom-switch-input" {{(isset($status) && isset($status->value)) ? 'checked' : ''}}/>
                                        <span class="custom-switch-indicator"></span>
                                        <label class="custom-switch-description mb-0 cursor-pointer" for="status-Switch">Drip feed on course SideBar</label>
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-success">{{ trans('admin/main.save_change') }}</button>
                           </form>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush
