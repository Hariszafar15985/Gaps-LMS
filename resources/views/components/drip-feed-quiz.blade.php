<div>
    <!-- Order your soul. Reduce your wants. - Augustine -->
    <div class="row my-3">
        <div class="col-lg-6">
            <label class="input-label d-block">{{ trans('public.drip_feed') }}</label>
            <select name="ajax[{{ !isset($quiz) ? 'new' : $quiz->id }}][drip_feed]" class="form-control selectFeed">
                {{-- <option value="{{ isset($quiz->drip_feed) ? $quiz->drip_feed : 0 }}" disabled selected> {{ (isset($quiz->drip_feed) && $quiz->drip_feed == 1) ? "True" : "False" }} </option> --}}
                <option value="0" {{ (isset($quiz->drip_feed) && $quiz->drip_feed === 0) ? 'selected' : ''}}> {{ trans('public.drip_feed_false') }}  </option>
                <option value="1" {{ (isset($quiz->drip_feed) && $quiz->drip_feed === 1) ? 'selected' : ''}}> {{ trans('public.drip_feed_true') }} </option>
            </select>
            <div class="invalid-feedback"></div>
        </div>

        <div class="col-lg-6 feedDate" style="{{ (isset($quiz->drip_feed) && $quiz->drip_feed == 1) ? 'visibility:visible; display:block;' : 'visibility:hidden; display:none;'}} ">
            <label class="input-label d-block">{{ trans('public.show_after_days') }}</label>
            <input type="number" value="{{ isset($quiz->show_after_days) ? $quiz->show_after_days : "" }}" name="ajax[{{ !isset($quiz) ? 'new' : $quiz->id }}][show_after_days]" class="form-control feedDateField">
        </div>
    </div>
</div>
{{-- script to handle drip feed show hide --}}
<script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function(){
        $(document.body).on('change', ".selectFeed", function(e) {
            var optVal = $(this).val();
            if (parseInt(optVal) == 1) {
                $(".feedDate").css('visibility', 'visible');
                $(".feedDate").show();
            } else {
                $(".feedDate").css('visibility', 'hidden');
                $(".feedDate").hide();
                $(".feedDate input").val('0');
            }
        });
    });

</script>

