@php
    if(strlen($count) == 1)
        $countWidth = strlen($count) * 15;
    elseif(strlen($count) == 2)
        $countWidth = strlen($count) * 9;
    else
        $countWidth = strlen($count) * 8;
@endphp

<div class="rectangle" style="width: {{ $countWidth }}px;">
    <div class="count">{{ $count }}</div>
</div>
