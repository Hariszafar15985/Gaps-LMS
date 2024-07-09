<div>
    <select class="form-control @error('course') is-invalid @enderror" id="course" name="course">
        <option disabled selected>Select Course</option>
        @foreach ($webinar as $course)
        <option data-url="{{ $course->getUrl() }}" {{ $isSelected($course->id) ? 'selected="selected"' : '' }}
            value="{{$course->id}}" 
        >{{$course->title}} {{(isset($course->price) && (int)$course->price > 0) ? "(" .$currency . " " . (number_format($course->price, 2, ".", "")+0) .")" : "" }}</option>
        @endforeach
    </select>
</div>
