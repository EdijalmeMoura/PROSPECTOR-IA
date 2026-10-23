@php
    $color = match (true) {
        $score >= 80 => 'success',
        $score >= 60 => 'warning',
        default => 'secondary',
    };
@endphp
<span class="badge text-bg-{{ $color }} badge-score">{{ $score }}/100</span>
