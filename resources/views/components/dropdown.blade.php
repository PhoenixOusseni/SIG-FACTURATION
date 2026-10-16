@props(['align'=>'right','width'=>'48','contentClasses'=>'py-1 bg-white'])
<div class="dropdown" x-data="{open:false}" @click.away="open=false">
    <div @click="open=!open">{{ $trigger }}</div>
    <div x-show="open" class="dropdown-menu show {{ $align==='right'?'dropdown-menu-end':'' }} {{ $contentClasses }}">{{ $content }}</div>
</div>
