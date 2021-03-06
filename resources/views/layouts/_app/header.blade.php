<!-- Main Header -->
<header class="main-header">

    <!-- Logo -->
    <a href="/" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b>М</b>К</span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg"></span>
    </a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu hidden-xs">
            <ul class="nav navbar-nav">
                @hasanyrole('user|client')
                <!-- Balance Menu -->
                {{-- <user-balance-menu></user-balance-menu> --}}
                @endhasallroles

                <!-- Messages: style can be found in dropdown.less-->
                <notification-messages :userid="{{auth()->id()}}"
                                       :notification-messages="{{auth()->user()->unreadNotificationMessages}}"
                                       :all-messages-url="'{{route('chat', [], false)}}'"
                ></notification-messages>
                <!-- /.messages-menu -->

                <!-- Documents Menu -->
                <notification-documents
                    :userid="{{auth()->id()}}"
                    :documents="{{auth()->user()->unreadDocuments}}"
                    :category-url="'{{route('reports', [], false)}}'"
                ></notification-documents>

                <!-- Recommendations Menu -->
                <notification-recommendations
                    :userid="{{auth()->id()}}"
                    :recommendations="{{auth()->user()->unreadRecommendations}}"
                    :category-url="'{{route('recommendations', [], false)}}'"
                ></notification-recommendations>

                <!-- Notifications Menu -->
                {{-- <notification-service-messages :notification-service-text-messages="{{auth()->user()->unreadServiceTextNotificationMessages}}"></notification-service-messages> --}}
                <!-- User Account Menu -->
                <user-account-menu></user-account-menu>
            </ul>
        </div>

        <div class="container-fluid visible-xs">
            <div class="row">
                <div class="navbar-header">
                    <div class="navbar-custom-title">
                        <div class="navbar-custom-title__item">
                            {{ $pageTitle }}
                        </div>
                    </div>
                </div>
            <div>
        </div>
    </nav>
</header>
<!-- Left side column. contains the logo and sidebar -->
