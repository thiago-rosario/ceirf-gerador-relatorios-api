@forelse($figure['sources'] as $source)
    <div class="figure" @if($loop->first) id="{{ $figure['anchor'] }}" data-pdf-anchor="{{ $figure['anchor'] }}" @endif>
        <img src="{{ $source }}" alt="{{ $figure['caption'] }}">
        <p class="caption">Figura {{ $figure['number'] }} - {{ $figure['caption'] }}@if(!$loop->first) (continuação)@endif</p>
    </div>
@empty
    <p class="caption" id="{{ $figure['anchor'] }}" data-pdf-anchor="{{ $figure['anchor'] }}">Figura {{ $figure['number'] }} - {{ $figure['caption'] }}</p>
@endforelse
