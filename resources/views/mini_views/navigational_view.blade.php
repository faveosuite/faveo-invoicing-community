<style>
    .tab-content {
    border:none;
}
</style>
<div class="row">
    <div class="col col-md-3">
        <div class="featured-box-primary text-left mt-5">
            <div class="tabs tabs-vertical tabs-left">
            <ul class="nav nav-tabs">
                @foreach($navigations as $navigation)
                    <li class="nav-item {{isset($navigation['active'])? 'active': ''}}" id="demo">
                       <a class="nav-link {{isset($navigation['active'])? 'active': ''}}" href="#{{$navigation['id']}}" data-toggle="tab"> <i class="{{$navigation['icon']}}"> </i>&nbsp;{{$navigation['name']}}</a>
                    </li>
                @endforeach
            </ul>
       
    </div>
</div>
    </div>
     @include('themes.default1.user.2faModals')
    <div class="col col-md-9">

      
              <div class="featured-box featured-box-primary text-left mt-5">
                <div class="box-content">
                    <div class="tab-content" style="overflow-x: auto;border:none;">

            @foreach($navigations as $index => $navigation)
                <div id="{{$navigation['id']}}" class="tab-pane {{isset($navigation['active'])? 'active': ''}}">
                    <h4>{{$navigation['name']}}</h4>
                    {{-- making number as variable name so that slots can be injected using @slot('1'), @slot('2') --}}
                    @php
                        $slotVariableName = $navigation['slot']
                    @endphp

                    {{$$slotVariableName}}
                </div>
            @endforeach

        </div>
        </div>

    </div>
    </div>
</div>
