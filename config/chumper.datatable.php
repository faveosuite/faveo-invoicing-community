<?php

return [
    /*
      |--------------------------------------------------------------------------
      | Table specific configuration options.
      |--------------------------------------------------------------------------
      |
     */

    'table' => [
        /*
          |--------------------------------------------------------------------------
          | Table class
          |--------------------------------------------------------------------------
          |
          | Class(es) added to the table
          | Supported: string
          |
         */

        'class' => 'table table-responsive table-bordered table-striped dataTable mailbox-messages',
        /*
          |--------------------------------------------------------------------------
          | Table ID
          |--------------------------------------------------------------------------
          |
          | ID given to the table. Used for connecting the table and the Datatables
          | jQuery plugin. If left empty a random ID will be generated.
          | Supported: string
          |
         */
        'id' => '',
        /*
          |--------------------------------------------------------------------------
          | DataTable options
          |--------------------------------------------------------------------------
          |
          | jQuery dataTable plugin options. The array will be json_encoded and
          | passed through to the plugin. See https://datatables.net/usage/options
          | for more information.
          | Supported: array
          |
         */
        'options' => [
            //            "dom" => "Bfrtip",
            //            "buttons" => [
            //                [
            //                    "text" => "My button",
            //                    "action" => "function ( e, dt, node, config ) {
            //                    alert( 'Button activated' );
            //                }"
            //                ]
            //            ],
            'pagingType' => 'simple_numbers',
            'bProcessing' => true,
            'columnDefs' => [['targets' => 0, 'orderable' => false]],
            // 'oLanguage'   => [
            //         'sProcessing'=> '<img id="blur-bg" class="backgroundfadein" style="top:40%;left:50%; width: 50px; height:50 px; display: block; position:    fixed;" src="'.asset('dist/gif/gifloader.gif').'">',
            //     ],

        ],
        /*
          |--------------------------------------------------------------------------
          | DataTable callbacks
          |--------------------------------------------------------------------------
          |
          | jQuery dataTable plugin callbacks. The array will be json_encoded and
          | passed through to the plugin. See https://datatables.net/usage/callbacks
          | for more information.
          | Supported: array
          |
         */
        'callbacks' => [
            'fnDrawCallback' => 'function( oSettings ) {
                    $(".box-body").css({"opacity": "1"});
                    $("#blur-bg").css({"opacity": "1", "z-index": "99999"});
                }',
            'fnPreDrawCallback' => 'function(oSettings, json) {
                    $(".box-body").css({"opacity":"0.3"});
                }',
        ],
        /*
          |--------------------------------------------------------------------------
          | Skip javascript in table template
          |--------------------------------------------------------------------------
          |
          | Determines if the template should echo the javascript
          | Supported: boolean
          |
         */
        'noScript' => false,
        /*
          |--------------------------------------------------------------------------
          | Table view
          |--------------------------------------------------------------------------
          |
          | Template used to render the table
          | Supported: string
          |
         */
        'table_view' => 'chumper.datatable::template',
        /*
          |--------------------------------------------------------------------------
          | Script view
          |--------------------------------------------------------------------------
          |
          | Template used to render the javascript
          | Supported: string
          |
         */
        'script_view' => 'chumper.datatable::javascript',
    ],
    /*
      |--------------------------------------------------------------------------
      | Engine specific configuration options.
      |--------------------------------------------------------------------------
      |
     */
    'engine' => [
        /*
          |--------------------------------------------------------------------------
          | Search for exact words
          |--------------------------------------------------------------------------
          |
          | If the search should be done with exact matching
          | Supported: boolean
          |
         */

        'exactWordSearch' => false,
    ],
    /*
      |--------------------------------------------------------------------------
      | Allow overrides Datatable core classes
      |--------------------------------------------------------------------------
      |
     */
    'classmap' => [
        'CollectionEngine' => 'Chumper\Datatable\Engines\CollectionEngine',
        'QueryEngine' => 'Chumper\Datatable\Engines\QueryEngine',
        'Table' => 'Chumper\Datatable\Table',
    ],
];
