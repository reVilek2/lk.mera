<!-- Main Header -->
<header class="main-header">

    <!-- Logo -->
    <a href="index2.html" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b>M</b>C</span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg">MeraCapital</span>
    </a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                {{--@hasanyrole('user|client')--}}
                <!-- Balance Menu -->
                <user-balance-menu></user-balance-menu>
                {{--@endhasallroles--}}
                <!-- Messages: style can be found in dropdown.less-->
                <notification-messages :userid="{{auth()->id()}}"
                                       :notification-messages="{{auth()->user()->unreadNotificationMessages}}"
                                       :all-messages-url="'{{route('chat', [], false)}}'"
                ></notification-messages>
                <!-- /.messages-menu -->
                <!-- Notifications Menu -->
                <notification-service-messages :notification-service-text-messages="{{auth()->user()->unreadServiceTextNotificationMessages}}"></notification-service-messages>
                <!-- User Account Menu -->
                <user-account-menu></user-account-menu>
                <!-- Control Sidebar Toggle Button -->
                {{--<li>--}}
                    {{--<a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>--}}
                {{--</li>--}}
            </ul>
        </div>
    </nav>
</header>
<!-- Left side column. contains the logo and sidebar -->