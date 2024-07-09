<div class="section-body mt-40">
    <div class="card">
        
        <div class="card-body">
            <div class="row mt-20 mb-20">
                <div class="col-2">
                    <a id="addNoteBtn" class="btn btn-small btn-block btn-primary" href="#note-section">{{ trans('public.add_note') }}</a>
                </div>
            </div>
            @php
                $profileNotes = null;
                if (isset($user->profileNotes)) {
                    $profileNotes = $user->profileNotes->sortByDesc('created_at');
                }
            @endphp
            <table class="table table-striped">
                <thead>
                    <tr>
                    <th scope="col">#</th>
                    <th scope="col">{{ trans('public.title') }}</th>
                    <th scope="col">{{ trans('public.note') }}</th>
                    <th scope="col">{{ trans('public.added_by') }}</th>
                    <th scope="col">{{ trans('public.date_time') }}</th>
                    <th scope="col">{{ trans('public.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if ( isset($profileNotes) && $profileNotes->count() > 0)
                        @php $row = 1; @endphp
                        @foreach ($profileNotes as $profileNote)
                            <tr>
                                <th scope="row">{{$row}}</th>
                                <td>{{ isset($profileNote->title) ? $profileNote->title : "" }}</td>
                                <td>{{ isset($profileNote->message) ? $profileNote->message : "" }}</td>
                                <td>{{ isset($profileNote->creator) ? ucwords($profileNote->creator->full_name) : "" }}</td>
                                <td class="text-center">{{ isset($profileNote->created_at) ? date('d-M-Y | h:i a T', strtotime($profileNote->created_at)) : "-" }}</td>
                                <td class="text-center">
                                    <a href="#profileNoteId" data-noteId="{{$profileNote->id}}" 
                                        data-title="{{$profileNote->title}}" 
                                        data-message="{{$profileNote->message}}" 
                                        class="btn-transparent editProfileNote text-primary d-inline-block" 
                                        data-toggle="tooltip" 
                                        data-placement="top" 
                                        title="{{ trans('public.edit') }}"
                                        onclick="$('#profileNoteId').focus();">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form class="d-inline-block" action="{{route('panel.remove.profile.note', ['id' => $profileNote->id ])}}" method="POST">
                                        {{ csrf_field() }}
                                        <button class="btn-transparent text-primary d-inline-block" data-toggle="tooltip" data-placement="top" title="{{ trans('public.remove') }}" type="submit">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @php $row++; @endphp
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center">{{ trans('public.no_notes_against_user') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <hr />
        <div id="note-section" class="card-body">
            <h3>{{ trans('public.add_note') }}</h3>
            <form method="post" action="{{ route('panel.add.profile.note') }}" class="form-horizontal form-bordered mt-4">
                {{ csrf_field() }}
                <input type="hidden" name="user_id" value="{{isset($user->id) ? $user->id : "" }}" />
                <input type="hidden" name="noteId" id="profileNoteId" value="" />

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label" for="inputDefault">{!! trans('public.title') !!}</label>
                            <input type="text" name="title" id="profileNoteTitle" class="form-control @error('title') is-invalid @enderror" value="{{ !empty($notification) ? $notification->title : old('title') }}">
                            <div class="invalid-feedback">@error('title') {{ $message }} @enderror</div>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label class="control-label">{{ trans('public.note') }}</label>
                    <textarea name="message" id="profileNoteMessage" class="summernote form-control text-left  @error('message') is-invalid @enderror">{{ (!empty($notification)) ? $notification->message :'' }}</textarea>
                    <div class="invalid-feedback">@error('message') {{ $message }} @enderror</div>
                </div>


                <div class="form-group">
                    <div class="col-md-12">
                        <button class="btn btn-primary" type="submit">{{ trans('public.add_update_note') }}</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

@push('scripts_bottom')
    <script>
        $(document).ready(function() {
            $('.editProfileNote').click( function(event) {
                /* event.preventDefault(); */
                
                $('#profileNoteId').val($(this).attr('data-noteId'));
                $('#profileNoteTitle').val($(this).attr('data-title'));
                $('#profileNoteMessage').val($(this).attr('data-message'));
                $('#addNoteBtn')[0].click();
            });

        });
    </script>
@endpush