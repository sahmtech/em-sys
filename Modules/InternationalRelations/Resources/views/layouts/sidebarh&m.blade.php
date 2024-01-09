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
         <a ><span>العلاقات الدولية   </span></a>
        </li>
        <li class="treeview">
         <a  href="{{ route('home') }}"><i class="fa fas fa-home "></i> <span>الرئيسية</span></a>
        </li>
        
        <li class="treeview">
         <a  href=""><i class="fas fa-file-signature "></i> <span>أوامر الطلب </span> 
        </a>
         
      
        </li>

        <li class="treeview">
         <a  href=""><i class="fas fa-door-open"></i> <span>شركة التوظيف</span></a>
        </li>

        <li class="treeview">
         <a  href=""><i class="fas fa-edit"></i> <span>طلب الوظيفة  </span></a>
        </li>

        <li class="treeview">
         <a  href=""><i class="fas fa-pen-square"></i> <span>التفويض  </span></a>
        </li>

        <li class="treeview">
         <a  href=""><i class="fas fa-wallet "></i> <span>العمالة المفترحة    </span></a>
        </li>

        <li class="treeview">
         <a  href=""><i class="fas fa-passport"></i> <span>التأشيرات   </span></a>
        </li>

        
        <li class="treeview">
         <a  href=""><i class="	fas fa-spa"></i> <span>شركات الطيران     </span></a>
        </li>

        <li class="treeview">
         <a  href=""><i class="fas fa-comments"></i> <span>الشكاوى     </span></a>
        </li>

        <li class="treeview">
         <a  href=""><i class="fa far fa-envelope-open"></i> <span>شكاوى مدير القسم        </span></a>
        </li>

        <li class="treeview">
         <a  href=""><i class="fas fa-envelope-open"></i> <span>شكاوى مدير الدائرة         </span></a>
        </li>
        <li class="treeview">
         <a  href=""><i class="fas fa-map"></i> <span>  التذاكر         </span></a>
        </li>
        <li class="treeview">
         <a  href=""><i class="fas fa-clone"></i> <span>  مهام الموظفين          </span></a>
        </li>

        <li class="treeview">
         <a  href=""><i class="fa far fa-file-archive"></i> <span>  التقارير           </span></a>
        </li>
    
  </ul>
            
    <!-- /.sidebar-menu -->
  </section>
  <!-- /.sidebar -->
</aside>
