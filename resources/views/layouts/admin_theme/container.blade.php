<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard | {!! env('APP_NAME') !!}</title>
  <!-- <title>{{ config('app.name', 'Laravel') }}</title> -->
  @if(!empty($setting->img_fav))
  <link rel="icon" type="image/x-icon" href="{{ asset('uploads/settings/'.$setting->img_fav) }}">
  @endif


  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
  <!-- IonIcons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
  <!-- DataTables -->
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

  <!-- summernote -->
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/summernote/summernote-bs4.min.css') }}">
  <!-- Swal -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

  <!-- @vite(['resources/js/app.js']) -->

</head>

<body class="hold-transition white-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <div class="wrapper">


    @include('layouts.admin_theme.header')

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      @include('layouts.admin_theme.sidebar')
    </aside>

    <div class="content-wrapper">

      <!-- Main content -->

      @yield('dynamicdata')

      <!-- /. Main content -->
    </div>
    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
      <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->

    @include('layouts.admin_theme.footer')


  </div>
  <!-- ./wrapper -->

  <!-- jQuery -->
  <script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}">
  </script>
  <!-- Bootstrap -->
  <script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <!-- AdminLTE -->
  <script src="{{ asset('adminlte/dist/js/adminlte.js') }}"></script>

  <!-- OPTIONAL SCRIPTS -->
  <script src="{{ asset('adminlte/plugins/chart.js/Chart.min.js') }}"></script>
  <!-- AdminLTE for demo purposes -->
  <script src="{{ asset('adminlte/dist/js/demo.js') }}"></script>
  <!-- overlayScrollbars -->
  <script src="{{ asset('adminlte/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>


  <!-- DataTables  & Plugins -->
  <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>

  <!-- Summernote -->
  <script src="{{ asset('adminlte/plugins/summernote/summernote-bs4.min.js') }}"></script>
  <!-- Swal -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

  @yield('footer_js')

  <!-- Page specific script -->

  <script type="text/javascript">
    $(document).ready(function() {
      var oTable = $('.show-search-bar').dataTable();
    });
  </script>

  <!-- <script type="text/javascript">
    $(document).ready(function() {
      $(document).on('click', '.add-user-table', function(e) {
        e.preventDefault();
        $('#adduserForm').modal('show');
      });
    });
  </script>

  <script type="text/javascript">
    $(document).ready(function() {
      $(document).on('click', '.add-role-table', function(e) {
        e.preventDefault();
        $('#addroleForm').modal('show');
      });
    });
  </script> -->

  <script type="text/javascript">
    $(document).ready(function() {
      $(document).on('click', '.add-ticket-table', function(e) {
        e.preventDefault();
        $('#addticketForm').modal('show');
      });
    });
  </script>

  <script type="text/javascript">
    $(document).ready(function() {
      $(document).on('click', '.add-route-table', function(e) {
        e.preventDefault();
        $('#addRouteForm').modal('show');
      });
    });
  </script>

  <script type="text/javascript">
    $(document).ready(function() {
      $(document).on('click', '.add-fleet-table', function(e) {
        e.preventDefault();
        $('#addFleetForm').modal('show');
      });
    });
  </script>

  <script type="text/javascript">
    $(document).ready(function() {
      $(document).on('click', '.passcode-form', function(e) {
        e.preventDefault();
        $('#passcodeForm').modal('show');
      });
    });
  </script>

  <script type="text/javascript">
    $(document).ready(function() {
      $(document).on('click', '.add-language-table', function(e) {
        e.preventDefault();
        $('#addlanguageForm').modal('show');
      });
    });
  </script>

  <script type="text/javascript">
    $(document).ready(function() {
      $(document).on('click', '.add-popup-table', function(e) {
        e.preventDefault();
        $('#addpopupForm').modal('show');
      });
    });
  </script>

  <script>
    $(function() {
      // Summernote
      $('#summernote').summernote()
      $('#summernote_overviews').summernote()
      $('#summernote1').summernote()
      $('#summernote2').summernote()

    })
  </script>

  @yield('scripts')
</body>

</html>