  <!-- Main Sidebar Container -->
  <!-- Brand Logo -->
  <a href="{{ route('dashboard') }}" class="brand-link">
     @if(!empty($setting->img_logo) && file_exists(public_path('uploads/settings/'.$setting->img_logo)))
     <img src="{{ asset('uploads/settings/'.$setting->img_logo) }}" alt="Logo" class="brand-image img-circle elevation-3">
     @else
     <img src="{{ asset('adminlte/dist/img/logo.png') }}" alt="Default Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
     @endif

     <!-- <span class="brand-text font-weight-light">{{ env('APP_NAME') }}</span> -->
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
     <!-- Sidebar user panel (optional) -->
     <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
           @if(Auth::user()->image_icon)
           <img src="{{ asset('uploads/users/'. Auth::user()->image_icon) }}" class="img-circle elevation-2" alt="User Image">
           @else
           <img src="{{ asset('adminlte/dist/img/avatar4.png') }}" class="img-circle elevation-2" alt="User Image">
           @endif
        </div>
        <div class="info">
           <a href="{{ route('dashboard') }}" class="d-block">{{ Auth::user()->name }}</a>
        </div>
     </div>

     <!-- Sidebar Menu -->
     @php
     use App\Helpers\MenuHelper;
     $menuItems = config('sidebar');
     @endphp

     <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

           @foreach ($menuItems as $item)
           {{-- @if(MenuHelper::hasPermission($item)) --}}
           @if(true)
           @if(isset($item['children']))
           <li class="nav-item has-treeview {{ MenuHelper::isActive($item) ? 'menu-open' : '' }}">
              <a href="#" class="nav-link {{ MenuHelper::isActive($item) ? 'active' : '' }}">
                 <i class="nav-icon {{ $item['icon'] }}"></i>
                 <p>
                    {{ $item['title'] }}
                    <i class="right fas fa-angle-right"></i>
                 </p>
              </a>
              <ul class="nav nav-treeview">
                 @foreach($item['children'] as $child)
                 {{-- @can($child['permission']) --}}
                 <li class="nav-item" style="padding-left: 7px;">
                    <a href="{{ route($child['route']) }}" class="nav-link {{ MenuHelper::isActive($child) ? 'active' : '' }}">
                       <i class="nav-icon {{ $child['icon'] }}"></i>
                       <p>{{ $child['title'] }}</p>
                    </a>
                 </li>
                 {{-- @endcan --}}
                 @endforeach
              </ul>
           </li>
           @else
           <li class="nav-item">
              <a href="{{ route($item['route']) }}" class="nav-link {{ MenuHelper::isActive($item) ? 'active' : '' }}">
                 <i class="nav-icon {{ $item['icon'] }}"></i>
                 <p>{{ $item['title'] }}</p>
              </a>
           </li>
           @endif
           @endif
           @endforeach

        </ul>
     </nav>

     <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->