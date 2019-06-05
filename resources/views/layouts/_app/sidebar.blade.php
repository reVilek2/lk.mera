<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ Auth::user()->getAvatar('thumb') }}" class="img-circle js-user-avatar-thumb" alt="User Image">
            </div>
            <div class="pull-left info">
                <p>{{ Auth::user()->getUserName() }}</p>
                <!-- Status -->
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <!-- search form (Optional) -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search...">
                <span class="input-group-btn">
              <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
              </button>
            </span>
            </div>
        </form>
        <!-- /.search form -->

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">Навигация</li>
            <!-- Optionally, you can add icons to the links -->
            <li class="{{ set_active(['profile', 'profile/*']) }}"><a href="{{route('profile')}}"><i class="fa fa-user"></i> <span>Профиль</span></a></li>
            <li class="{{ set_active(['chat', 'chat/*']) }}"><a href="{{route('chat')}}"><i class="fa fa-comments"></i> <span>Чат</span></a></li>
            @hasanyrole('manager|admin')
                <li class="{{ set_active(['users', 'users/*']) }}"><a href="{{route('users')}}"><i class="fa fa-users"></i> <span>Пользователи</span></a></li>
                <li class="{{ set_active(['documents', 'documents/*']) }}"><a href="{{route('documents')}}"><i class="fa fa-file-pdf-o"></i> <span>Документы</span></a></li>
            @endhasallroles
            {{--<li class="treeview">--}}
                {{--<a href="#"><i class="fa fa-link"></i> <span>Multilevel</span>--}}
                    {{--<span class="pull-right-container">--}}
                {{--<i class="fa fa-angle-left pull-right"></i>--}}
              {{--</span>--}}
                {{--</a>--}}
                {{--<ul class="treeview-menu">--}}
                    {{--<li><a href="#">Link in level 2</a></li>--}}
                    {{--<li><a href="#">Link in level 2</a></li>--}}
                {{--</ul>--}}
            {{--</li>--}}
        </ul>
        <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>