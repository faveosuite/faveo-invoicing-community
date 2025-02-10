<a onclick="getTables({{$productid}},{{$clientid}},{{$invoiceid}})" class="btn btn-light-scale-2 btn-sm text-dark" data-toggle="modal" data-target="#lists">  <i class='fa fa-download' data-toggle="tooltip"  title="Click here to download"></i>&nbsp;</a>
<style>
.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}
.tooltip .tooltiptext {
    visibility: hidden;
    width: 120px;
    background-color: #555;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 5px 0;
    position: absolute;
    z-index: 1;
    bottom: 125%;
    left: 50%;
    margin-left: -60px;
    opacity: 0;
    transition: opacity 0.3s;
}
.tooltip {
    position: relative;
    display: inline-block;
    border-bottom: 1px dotted black;
}
.tooltip .tooltiptext::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: #555 transparent transparent transparent;
}
.more-text{
     display:none;
}
</style>
<div class="modal fade" id="lists">
    <div class="modal-dialog">
        <div class="modal-content" style="width:700px;">
         <div class="modal-body" >
         <button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="hideModal()"><span aria-hidden="true">&times;</span></button>
                <?php
                //All the versions of Uploades Files
                $products = \App\Model\Product\Product::where('id', $productid)->pluck( 'name')->toArray();
               //End Date of the Current Product Version
                $endDate = \App\Model\Product\Subscription::select('ends_at')->where('product_id', $productid)->first();
                ?>

                <div class="row">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-header with-border">
                               <h3 class="box-title">{{ __('message.product_version')}}</h3>
                            </div>
                            <div class="box-body">

                                <div class="row">

                                    <div class="col-md-12">

                                        <table id="github-version-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">

                                            <thead><tr>

                                                    <th>{{ __('message.version')}}</th>
                                                    <th>{{ __('message.title')}}</th>
                                                    <th>{{ __('message.description')}}</th>
                                                    <th>{{ __('message.file')}}</th>

                                                </tr></thead>

                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>


                <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" />
                <script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
                <script type="text/javascript">
                        function hideModal(){
                        $('#lists').modal('hide');
                        }


                       function readmore(){
                        var maxLength = 300;
                        $("#github-version-table tbody tr td").each(function(){
                            var myStr = $(this).text();
                            if($.trim(myStr).length > maxLength){
                                var newStr = myStr.substring(0, maxLength);
                                 $(this).empty().html(newStr);
                                var removedStr = myStr.substring(maxLength, $.trim(myStr).length);
                                $(this).append('<span class="more-text">' + removedStr + '</span>');
                                $(this).append(' <a href="javascript:void(0);" class="read-more">read more...</a>');
                            }
                          }); 
                         }
    function getTables($productid, $clientid, $invoiceid){
    $('#github-version-table').DataTable({
            destroy: true,
            "initComplete": function(settings, json) {
                         readmore();
            },
            processing: true,
            serverSide: true,
            bDeferRender: true,
            order: [[ 0, "desc" ]],
            ajax: "{!! Url('get-github-versions') !!}/" + $productid + '/' + $clientid + '/' + $invoiceid,
            "oLanguage": {
            "sLengthMenu": "_MENU_ Records per page",
                    "sSearch"    : "Search: ",
                    "sProcessing": '<img id="blur-bg" class="backgroundfadein" style="top:40%;left:50%; width: 50px; height:50 px; display: block; position:    fixed;" src="{!! asset("lb-faveo/media/images/gifloader3.gif") !!}">'
            },
            columnDefs: [
            {
                
                    targets: 0,
                    orderable: false,
                    searchable: false,
                    order: []
            }
            ],
            columns: [
            {data: 'version', name: 'version'},
            {data: 'name', name: 'name'},
            {data: 'description', name: 'description'},
            {data: 'file', name: 'file'},
            ],
            "fnDrawCallback": function(oSettings) {
            $('.loader').css('display', 'none');
               readmore();
            },
            "fnPreDrawCallback": function(oSettings, json) {
            $('.loader').css('display', 'block');
            },
    });
    }

                </script>


        </div>


        </div>

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<!-- /.modal -->


<script>
    $(document).on('click','#github-version-table tbody tr td .read-more',function(){
        var text=$(this).siblings(".more-text").text().replace('read more...','');
        console.log(text)
        $(this).siblings(".more-text").html(text);
        $(this).siblings(".more-text").contents().unwrap();
        $(this).remove();
    });
    $(function () {
    $('[data-toggle="popover"]').popover()
    })
</script>
