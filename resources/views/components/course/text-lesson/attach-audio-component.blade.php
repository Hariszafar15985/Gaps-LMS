
<label for="audioFile" class="input-label">Audio File</label>
{{-- <input type="file" id="audioFile" class="form-control" name="audio-file" accept="audio/*" onchange="previewAudio(event)"> --}}
<input type="file" id="audioFile" class="form-control" name="audio-file" accept="audio/*" >
{{-- <audio id="audio-preview" controls style=""></audio> --}}

@push("scripts_bottom")
<script>
    // function previewAudio(event){
    //     var input = event.target;
    //     var url = URL.createObjectURL(input.files[0]);
    //     var audio = document.getElementById('audio-preview');
    //     audio.src = url;
    //     audio.style.display = "block";
    // }
</script>
@endpush
