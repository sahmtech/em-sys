<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar" style="background:#12142e ">

  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar" style="background:#12142e ">

	<a href="{{route('home')}}" class="logo">
		<span class="logo-lg">{{ Session::get('business.name') }}</span>
	</a>
  
    <!-- Sidebar Menu -->
    {!! Menu::render('admin-sidebar-menu', 'adminltecustom'); !!}

    <!-- /.sidebar-menu -->
  </section>
  <!-- /.sidebar -->
</aside>
