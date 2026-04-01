  <!-- Main Sidebar Container -->
  <!-- Brand Logo -->
  <a href="{{ route('dashboard') }}" class="brand-link">
     {{-- @if(!empty($setting->img_logo) && file_exists(public_path('uploads/settings/'.$setting->img_logo))) --}}
        <div class="image">
            @php
               use App\Helpers\MenuHelper;
               $basic = MenuHelper::showBasicSetup();
            @endphp

            @if($basic->logo)
               <img src="{{ asset($basic->logo) }}" class="img-fluid rounded" width="40" alt="Company Logo">
            @else
               <img src="{{ asset('adminlte/logo4.png') }}" style="width:150px; margin-bottom:20px;"> 
            @endif
         </div>
     {{-- @else
     <img src="{{ asset('adminlte/dist/img/logo.png') }}" alt="Default Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
     @endif --}}

     <!-- <span class="brand-text font-weight-light">{{ env('APP_NAME') }}</span> -->
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
     <!-- Sidebar user panel (optional) -->
     <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
           @php
           
            $user = MenuHelper::showProfile();
            @endphp
           @if($user->img)
            <img src="{{ asset('uploads/users/'.$user->img) }}" class="img-fluid rounded-circle" width="130">
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
     $menuItems = MenuHelper::getParentModules();
     @endphp
     <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            
           @foreach ($menuItems as $item)
           {{-- @if(MenuHelper::hasPermission($item)) --}}
           @if(true)
           @php
           $childModules = MenuHelper::getSubModulesByParentId($item['id']);
           @endphp
           @if(!empty($childModules))
           
           @php
           $show_sub_module_count = '0';
            foreach($childModules as $child){
               if(auth()->user()->can($child['permission'])){
                  $show_sub_module_count = $show_sub_module_count + 1;
               }
            }
           @endphp
           @if($show_sub_module_count > 0)
            <li class="nav-item has-treeview {{ MenuHelper::isActive($item) ? 'menu-is-opening menu-open' : '' }}">
               <a href="#" class="nav-link {{ MenuHelper::isActive($item) ? 'active' : '' }}">
                  <i class="nav-icon {{ $item['icon'] }}"></i>
                  <p>
                     {{ $item['name'] }}
                     <i class="right fas fa-angle-right"></i>
                  </p>
               </a>

               <ul class="nav nav-treeview" style="display: {{ MenuHelper::isActive($item) ? 'block' : 'none' }};">
                  @foreach($childModules as $child)
                     @if(auth()->user()->can($child['permission']))
                     <li class="nav-item" style="padding-left: 7px;">
                        <a href="{{ route(!empty($child['route']) ? $child['route'] : 'dashboard') }}" 
                           class="nav-link {{ MenuHelper::isActive($child) ? 'active' : '' }}">
                           <i class="nav-icon {{ $child['icon'] }}"></i>
                           <p>{{ $child['name'] }}</p>
                        </a>
                     </li>
                     @endif
                  @endforeach
               </ul>
            </li>
            @endif
           @else
           @if(!@empty($item['permission']))
           @if(auth()->user()->can($item['permission']))
           <li class="nav-item">
              <a href="{{ route(!empty($item['route']) ? $item['route'] : 'dashboard') }}" class="nav-link {{ MenuHelper::isActive($item) ? 'active' : '' }}">
                 <i class="nav-icon {{ $item['icon'] }}"></i>
                 <p>{{ $item['name'] }}</p>
              </a>
           </li>
           @endif
           @else 
            <li class="nav-item">
              <a href="{{ route(!empty($item['route']) ? $item['route'] : 'dashboard') }}" class="nav-link {{ MenuHelper::isActive($item) ? 'active' : '' }}">
                 <i class="nav-icon {{ $item['icon'] }}"></i>
                 <p>{{ $item['name'] }}</p>
              </a>
           </li>
           @endif
           @endif
           @endif
           @endforeach

        </ul>
     </nav>

     <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->