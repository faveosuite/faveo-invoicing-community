<a href="#list/{{$productid}}{{$clientid}}{{$invoiceid}}" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#list{{$productid}}{{$clientid}}{{$invoiceid}}">  <i class='fa fa-download' title=Download></i></a>
<div class="modal fade" id="list{{$productid}}{{$clientid}}{{$invoiceid}}">
    <div class="modal-dialog">
        <div class="modal-content" style="width:700px;">
          <table>
            <form>

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Product Versions</h4>
            </div>
              <div class="modal-body" style="padding:0px;margin-bottom:-26px;padding-top:25px;">
                    
                    <?php
                    //All the versions of Uploades Files
                   $versions = \App\Model\Product\ProductUpload::where('product_id', $productid)->select('id', 'title', 'description', 'version', 'file', 'created_at')->get();
                   //End Date of the Current Product Version
                   $endDate = \App\Model\Product\Subscription::select('ends_at')->where('product_id', $productid)->first();

                ?>
               <head>
                <style>
                table {
                    font-family: arial, sans-serif;
                    border-collapse: collapse;
                    width: 100%;
                }



                tr:nth-child(even) {
                    background-color: #dddddd;
                }

                .tooltip {
                              position: relative;
                              display: inline-block;
                              border-bottom: 1px dotted black;
                          }
                .tooltip .tooltiptext {
                visibility: hidden;
                width: 130px;
                background-color: black;
                color: #fff;
                text-align: center;
                border-radius: 6px;
                padding: 5px 0;

                /* Position the tooltip */
                position: absolute;
                z-index: 1;
                top: -35px;
                left: -80%;
            } 

                .tooltip:hover .tooltiptext {
                    visibility: visible;
                }
                </style>
              </head>

                 
                <table>
                  <tr>
                <div class="form-group col-md-3" style="padding-left:35px; ">
                   <th> <label style="label: initial;"> Version </label></th>
                   <th style="text-align: center;">  <label > Title </label></th>
                   <th style="text-align: center;"> <label> Description</label></th>
                   <th style="padding-right: 80px;text-align: right;"><label> File </label></th>
                     <br/>
                       
                   
                    </div>
                  </tr>
                  @foreach ($versions as $version)
                  <tr style="text-align: center;">
                    <td>
                      <span><strong><?php echo $version->version; ?></strong></span> 
                    </td>

                <td> <div class="form-group col-md-12 {{ $errors->has('title') ? 'has-error' : '' }}">
                   
                 <span data-content= data-toggle="popover" data-placement="">{{$version->title}}</span></td>
                 <td>
                   <div class="form-group col-md-12{{ $errors->has('description') ? 'has-error' : '' }}">
                    <!-- Description -->
                   <span data-content="" title="Release Notes" data-toggle="popover" data-placement="">{{$version->description}}</span>
                   </div>
                 </td>

                   <td>
                    
                  
                    @if($version->created_at->toDateTimeString() < $endDate->ends_at->toDateTimeString())
                  
                    <a href="{{url('download/'.$clientid.'/'.$invoiceid.'/'.$version->id)}}" class="btn btn-primary btn-sm">Download</a>
                    @else
                    <div class="btn btn-primary btn-sm disabled tooltip">Download<span class="tooltiptext">Download Unavailable</span></div>

                    @endif
                   </td>
                 
               </tr>
                @endforeach
                
               

               
              
                
              
               
               </form>
              </table>
            </div>
        
           
       </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
<!-- /.modal --> 
 

<script>
    $(function () {
  $('[data-toggle="popover"]').popover()
})
</script>
