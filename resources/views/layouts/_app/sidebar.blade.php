<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        <user-sidebar-panel></user-sidebar-panel>

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">Навигация</li>
            <!-- Optionally, you can add icons to the links -->
            <li class="{{ set_active(['reports', 'reports/*']) }}"><a href="{{route('reports')}}"><i class="fa fa-file-pdf-o"></i> <span>Отчеты</span></a></li>
            <li class="{{ set_active(['profile', 'profile/*']) }}"><a href="{{route('profile')}}"><i class="fa fa-user"></i> <span>Профиль</span></a></li>
            <li class="{{ set_active(['finances', 'finances/*']) }}"><a href="{{route('finances')}}"><i class="fa fa-money"></i> <span>Платежи</span></a></li>
            <li class="{{ set_active(['chat', 'chat/*']) }}"><a href="{{route('chat')}}"><i class="fa fa-comments"></i> <span>Чат</span></a></li>
            @hasanyrole('manager|admin')
                <li class="{{ set_active(['users', 'users/*']) }}"><a href="{{route('users')}}"><i class="fa fa-users"></i> <span>Пользователи</span></a></li>
            @endhasallroles
        </ul>
        <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>