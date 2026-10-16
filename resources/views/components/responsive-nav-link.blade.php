@props(['active'])
<a {{ $attributes->merge(['class'=>$active?'dropdown-item active':'dropdown-item']) }}>{{ $slot }}</a>
