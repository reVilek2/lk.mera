@if (count($breadcrumbs))
    <ol class="breadcrumb">
        @foreach ($breadcrumbs as $breadcrumb)

            @if ($breadcrumb->url && !$loop->last)
                <li class="breadcrumb-item"><a href="{{ $breadcrumb->url }}">@if(isset($breadcrumb->fa))<i class="fa {{$breadcrumb->fa}}"></i> @endif{{ $breadcrumb->title }}</a></li>
            @else
                <li class="breadcrumb-item active">@if(isset($breadcrumb->fa))<i class="fa {{$breadcrumb->fa}}"></i> @endif{{ $breadcrumb->title }}</li>
            @endif

        @endforeach
    </ol>
@endif