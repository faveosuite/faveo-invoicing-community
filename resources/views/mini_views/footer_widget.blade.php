<div class="{!! isset($colClass) ? $colClass : 'col-md-6 col-lg-3 mb-4 mb-md-0'!!}" >
    <h5 class="text-3 mb-3">{{strtoupper($title)}}</h5>
    <ul class="{!! isset($ulClass)? $ulClass:'list-unstyled' !!}">
        {{$slot}}
    </ul>
</div>


