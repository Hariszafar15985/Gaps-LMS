<div>
    <button id="btn-upload-document" type="button" class="btn btn-primary btn-round btn-sm mt-3">{{ trans('public.upload_document') }}</button>
    <table class="table table-striped">
        <thead>
        <tr>
            <th class="text-left" scope="col">#</th>
            <th class="text-left" scope="col">Title</th>
            <th class="text-left" scope="col">Type</th>
            <th class="text-left" scope="col">Uploaded By</th>
            <th class="text-left" scope="col">Date/Time</th>
            <th class="text-left" scope="col">Action</th>
        </tr>
        </thead>
        <tbody id="user-documents-div">
        @if(!$user->documents->isEmpty())
            @foreach($user->documents as $d)
                <tr id="row-{{$d->id}}">
                    <th scope="row">{{ $loop->iteration }}</th>
                    <td>{{ $d->title }}</td>
                    <td>{{ str_replace("-", " ", $d->type) }}</td>
                    <td>{{ $d->uploadedBy->full_name }}</td>
                    <td>{{ $d->created_at ? \Carbon\Carbon::parse($d->created_at)->format('m/d/Y H:i:s') : '' }}</td>
                    <td>
                        <a href="{{ asset('store/'.$d->user_id.'/user_documents/'.$d->document) }}" target="_blank" class="btn-transparent text-primary" data-toggle="tooltip" data-placement="top" title="View Document">
                            <i class="fa fa-print"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn-transparent text-primary" data-toggle="tooltip" data-placement="top" title="Delete Document" onclick="deleteUserDocument({{ $d->id }});">
                            <i class="fa fa-times"></i>
                        </a>
                        <a href="javascript:void(0);" data-visibility="{{$d->student_visibility}}" data-id="{{ $d->id }}" class="btn-transparent text-primary stdVisibility" data-toggle="tooltip" data-placement="top" title="{{ $d->student_visibility == 1 ? "Click to hide" : "Click to show" }}" onclick="">
                            <i class="fa {{ $d->student_visibility == 1 ? "fa-eye" : "fa-eye-slash" }}"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        @else
            <tr><td colspan="6" class="text-danger text-center">{{ trans('public.no_document_found') }}</td></tr>
        @endif
        </tbody>
    </table>
</div>
