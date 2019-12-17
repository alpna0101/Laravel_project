<!DOCTYPE html>
<html>
<head>
    <!-- Standard Meta -->
    <meta name="robots" content="noindex">
    
    <meta name="viewport" content="width=device-width,  initial-scale=1">

    <!-- Site Properties -->
      <title>@if(Setting::get('site_name')) {{Setting::get('site_name') }} @else {{tr('site_name')}} @endif</title>  
     <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
   <link rel="stylesheet" type="text/css" href="{{asset('streamtube/fonts/font-awesome/css/font-awesome.min.css')}}">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
      rel="stylesheet">
      <link href="https://code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css" rel="Stylesheet"></link>
       <link rel="stylesheet" type="text/css" href="{{asset('streamtube/css/slick.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('streamtube/css/slick-theme.css')}}"/>
       <link rel="stylesheet" type="text/css" href="{{asset('streamtube/css/style.css')}}">
       <link rel="stylesheet" type="text/css" href="{{asset('streamtube/css/responsive.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('streamtube/css/responsive1.css')}}">

    <link rel="stylesheet" type="text/css" href="{{ asset('semantic/dist/semantic.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.css">

    <style type="text/css">
    body {
      background-color: #DADADA;
    }
    [v-cloak] {
      display: none;
    }
    </style>

    </script>
    @yield('header-scripts')

    @if(Setting::get('google_analytics'))
        <?php echo Setting::get('google_analytics'); ?>
    @endif


    @yield('meta_tags')

    @yield('styles')

    <?php echo Setting::get('header_scripts') ?>  
</head>

<body>

    <!-- NAVBAR -->
 
    <!-- END NAVBAR -->

   <div class="wrapper_content">

        <!-- <div id="preloader">

            <div class="cssload-box-loading">
            </div>

        </div> -->
        <!-- <div id="preloader">

    
            <div class="loader3"></div>
        
        </div> -->

        @include('layouts.user.header')

        <div class="common-streamtube">

            @yield('content')

        </div>

        @include('layouts.user.footer')

    </div>
  

   <script type="text/javascript">
var APP_URL = {!! json_encode(url('/')) !!}
</script>

 <script src="{{ asset('js/jquery.min.js') }}"></script>

<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
 <script src="{{ asset('js/chosen.jquery.min.js') }}"></script>

 <script type="text/javascript" src="{{asset('streamtube/js/script.js')}}"></script>
    <script src="{{ asset('semantic/dist/semantic.min.js') }}"></script>
    <script>
       jQuery(document).ready( function () {
            //autocomplete
            jQuery("#auto_complete_search").autocomplete({
                source: "{{route('search')}}",
                minLength: 1,
                create: function () {
                    $(this).data('ui-autocomplete')._renderMenu = function( ul, items ) {

                        var that = this,
                        currentCategory = "";

                        if(items.length > 0) {
                            $.each( items, function( index, item ) {
                                console.log(item);
                              var li;
                              if ( item.category != currentCategory ) {
                                ul.append( "<li class='ui-autocomplete-category'><b>" + item.category + "</b></li>" );
                                currentCategory = item.category;
                              }
                              li = that._renderItemData( ul, item );
                              if ( item.category ) {
                                li.attr( "aria-label", item.category + " : " + item.label );
                              }
                            });
                        }
                    };
                },
                select: function(event, ui){

                    // set the value of the currently focused text box to the correct value

                    if (event.type == "autocompleteselect"){
                        
                        // console.log( "logged correctly: " + ui.item.value );

                        var username = ui.item.label;

                        if(ui.item.label == 'View All') {

                            // console.log('View AALLLLLLLLL');

                            window.location.href = "{{route('search-all', array('q' => 'all'))}}";

                        } else {
                            // console.log("User Submit");

                            // window.location.href = "{{url('/')}}"+'/user/search?q='+ui.item.label+"&n=";

                            jQuery('#auto_complete_search').val(ui.item.label);

                            jQuery('#userSearch').submit();
                        }

                    }                        
                }      // select

            }); 
    
            $('.special.cards .image').dimmer({
                on: 'hover'
            });
            $('#logoutButton').click(function(){
                $('#logoutModal').modal('show');
            })
        });
        
    </script>

    @yield('script')

</body>
</html>