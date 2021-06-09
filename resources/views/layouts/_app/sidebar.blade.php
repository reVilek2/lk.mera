<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu" data-widget="tree">
            <!-- Optionally, you can add icons to the links -->
            <li class="{{ set_active(['reports', 'reports/*']) }}"><a href="{{route('reports')}}"><i class="fa icon document mini"></i> <span>Отчеты</span></a></li>
            <li class="{{ set_active(['chat', 'chat/*']) }}"><a href="{{route('chat')}}"><i class="fa fa-comments"></i> <span>Чат</span></a></li>
            @hasanyrole('manager|client|introducer')
            <li class="{{ set_active(['recommendations', 'recommendations/*']) }}"><a href="{{route('recommendations')}}"><i class="fa icon recommendation mini"></i> <span>Рекомендации</span></a></li>
            @endhasallroles
            <li class="{{ set_active(['finances', 'finances/*']) }}"><a href="{{route('finances')}}"><i class="fa icon pay mini"></i> <span>Оплата услуг</span></a></li>
            <li class="{{ set_active(['profile', 'profile/*']) }}"><a href="{{route('profile')}}"><i class="fa fa-user"></i> <span>Профиль</span></a></li>
            @hasanyrole('manager|admin')
                <li class="{{ set_active(['users', 'users/*']) }}"><a href="{{route('users')}}"><i class="fa fa-users"></i> <span>Пользователи</span></a></li>
            @endhasallroles
            <hr>
            <li class="{{ set_active_route(['static.terms']) }}">
                <a href="{{route('static.terms')}}">
                    <span>Пользовательское соглашение</span></a>
            </li>
            <li class="{{ set_active_route(['static.services']) }}">
                <a href="{{route('static.services')}}">
                    <span>Услуги и правила оплаты</span></a>
            </li>
            <li class="{{ set_active_route(['static.organization_details']) }}">
                <a href="{{route('static.organization_details')}}">
                    <span>Реквизиты организации</span></a>
            </li>
            <li>
                <img class="payment-logo" src="{{asset('/images/payment-logo.png')}}" alt="payment-logo"/>
            </li>
            <li class="visible-xs"> <a href="{{route('logout')}}"><i class="fa fa-sign-out"></i> <span>Выход</span></a></li>
        </ul>
        <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
