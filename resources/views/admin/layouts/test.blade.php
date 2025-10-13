<!doctype html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="{{asset('assets/images/favicon-32x32.png')}}" type="image/png" />
	<!--plugins-->
	<link href="{{asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.css')}}" rel="stylesheet"/>
	<link href="{{asset('assets/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/plugins/metismenu/css/metisMenu.min.css')}}" rel="stylesheet" />
	<!-- Bootstrap CSS -->
	<link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/app.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/icons.css')}}" rel="stylesheet">
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
	<!-- Theme Style CSS -->
	<link rel="stylesheet" href="{{asset('assets/css/dark-theme.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/css/semi-dark.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/css/header-colors.css')}}" />
    <link href="{{asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet" />
	<link href="{{asset('assets/plugins/select2/css/select2-bootstrap4.css')}}" rel="stylesheet" />
	<link href="{{asset('assets/plugins/Drag-And-Drop/dist/imageuploadify.min.css')}}" rel="stylesheet" />

     <!-- Toastr CSS -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    @yield('custom-css')
    <title>Food Order</title>
</head>

<body>
	<!--wrapper-->
	<div class="wrapper">

        @yield('content')

		<!--start overlay-->
		<div class="overlay toggle-icon"></div>
		<!--end overlay-->
		<!--Start Back To Top Button-->
        <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
		<!--End Back To Top Button-->
		<footer class="page-footer">
            <p class="mb-0">Copyright © <span id="current-year"></span>. All right reserved.</p>
        </footer>
	</div>
	<!--end wrapper-->

	<!-- Bootstrap JS -->
	<script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

	<!--plugins-->
	<script src="{{asset('assets/js/jquery.min.js')}}"></script>
	<script src="{{asset('assets/plugins/simplebar/js/simplebar.min.js')}}"></script>
	<script src="{{asset('assets/plugins/metismenu/js/metisMenu.min.js')}}"></script>
	<script src="{{asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js')}}"></script>
    <script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
	<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
	<script src="{{asset('assets/plugins/chartjs/js/Chart.min.js')}}"></script>
	<script src="{{asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js')}}"></script>
    <script src="{{asset('assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js')}} "></script>
	<script src="{{asset('assets/plugins/jquery.easy-pie-chart/jquery.easypiechart.min.js')}}"></script>
	<script src="{{asset('assets/plugins/sparkline-charts/jquery.sparkline.min.js')}}"></script>
	<script src="{{asset('assets/plugins/jquery-knob/excanvas.js')}}"></script>
	<script src="{{asset('assets/plugins/jquery-knob/jquery.knob.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
    <script src="{{asset('assets/plugins/Drag-And-Drop/dist/imageuploadify.min.js')}}"></script>

	<script>
		  $(function() {
			  $(".knob").knob();
		  });
	</script>
    <script>
        // Ensure the script runs after the DOM is fully loaded using vanilla JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Check if the element exists before trying to access its textContent
            const currentYearSpan = document.getElementById('current-year');
            if (currentYearSpan) {
                currentYearSpan.textContent = new Date().getFullYear();
            }
        });
    </script>
	{{-- <script src="{{asset('assets/js/index.js')}}"></script> --}}
	<!--app JS-->
	<script src="{{asset('assets/js/app.js')}}"></script>
     <!-- Toastr JS -->
    <script>
         @if(Session::has('message'))
         var type = "{{ Session::get('alert-type','info') }}"
         switch (type) {
             case 'info':
                 toastr.info(" {{ Session::get('message') }} ");
                 break;

             case 'success':
                 toastr.success(" {{ Session::get('message') }} ");
                 break;

             case 'warning':
                 toastr.warning(" {{ Session::get('message') }} ");
                 break;

             case 'error':
                 toastr.error(" {{ Session::get('message') }} ");
                 break;
         }
         @endif
     </script>
    @yield('custom-js')
</body>

</html>
