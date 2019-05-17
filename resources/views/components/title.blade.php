@if(isset($title) && !empty($title))
    <h1>
        {{ $title }}
        @if(isset($description) && !empty($description))
            <small>{{$description}}</small>
        @endif
    </h1>
@endif
