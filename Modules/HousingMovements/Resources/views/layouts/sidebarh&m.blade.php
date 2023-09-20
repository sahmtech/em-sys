<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">

  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">

	<a href="{{route('home')}}" class="logo">
		<span class="logo-lg">{{ Session::get('business.name') }}</span>
	</a>

    <!-- Sidebar Menu -->

       <ul class="sidebar-menu tree">
    
       <li class="treeview">
         <a ><span>إدارة السكن والحركة </span></a>
        </li>
        <li class="treeview">
         <a  href="{{ route('home') }}"><i class="fas fa-home "></i> <span>الرئيسية</span></a>
        </li>
        
        <li class="treeview">
         <a  href=""><i class="fas fa-user-check "></i> <span>تسكين</span> <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
        </a>
         
         <ul class="treeview-menu">
                    <li><a href=""> <span>السكنات</span></a></li>
                    <li><a href=""><span>الغرف</span></a></li>
                    <li><a href=""><span>المطابخ</span></a></li>
                    <li><a href=""> <span>المسافرون</span></a></li>

			      </ul>
        </li>

        <li class="treeview">
         <a  href=""><i class="fas fa-archive"></i> <span>الطلبات</span></a>
        </li>

        <li class="treeview">
         <a  href=""><i class="fas fa-american-sign-language-interpreting"></i> <span>مهام الموظفين</span></a>
        </li>

        <li class="treeview">
         <a  href=""><i class="fa far fa-envelope-open"></i> <span>الشكاوي </span></a>
        </li>

        <li class="treeview">
         <a  href=""><i class="fas fa-envelope-square "></i> <span>شكاوي مدير القسم </span></a>
        </li>

        <li class="treeview">
         <a  href=""><i class="	fas fa-envelope"></i> <span>شكاوي مدير الدائرة </span></a>
        </li>

        
        <li class="treeview">
         <a  href=""><i class="fas fa-inbox"></i> <span>طلب الوظيفة    </span></a>
        </li>

        <li class="treeview">
         <a  href=""><i class="fas fa-file-alt"></i> <span>التقارير     </span></a>
        </li>

        <li class="treeview">
         <a  href=""><i class="fa far fa-bell "></i> <span>تنبهات السكن والحركة     </span></a>
        </li>
    
  </ul>
            
    <!-- /.sidebar-menu -->
  </section>
  <!-- /.sidebar -->
</aside>
