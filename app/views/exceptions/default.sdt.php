@extends 'layers/layer'
<div class="main error">
    <h1>Error {{$ex->getCode()}}!</h1>
    <h2>The page or resource you requested was {{$ex->getMessage()}}</h2>
    @if DEBUG_MODE
        <pre>{{$ex->getTraceAsString()}}</pre>
    :if
</div>
