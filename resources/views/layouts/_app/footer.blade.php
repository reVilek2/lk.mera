<!-- Main Footer -->
<footer class="main-footer hidden-xs">
    <!-- To the right -->
    <div class="pull-right hidden-xs">
        {{--Anything you want--}}
    </div>
    <a href="https://mera-capital.com/user-agreement" target="_blank" class="f-link">Пользовательское соглашение</a>
    <!-- Default to the left -->
    {{--<strong>Copyright &copy; {{ date( 'Y' ) }} <a href="http://mera-capital.com">MeraCapital</a>.</strong> All rights reserved.--}}
</footer>
<footer class="main-footer main-footer-mobile visible-xs">
    <ul class="footer-nav">
            <!-- Messages: style can be found in dropdown.less-->
        <notification-messages :userid="{{auth()->id()}}"
            :notification-messages="{{auth()->user()->unreadNotificationMessages}}"
            :all-messages-url="'{{route('chat', [], false)}}'"
            :is-mobile="true"
            class="{{ set_active(['chat', 'chat/*']) }}"
        >
        </notification-messages>
        <!-- /.messages-menu -->
        <notification-recommendations
            :userid="{{auth()->id()}}"
            :recommendations="{{auth()->user()->unreadRecommendations}}"
            :category-url="'{{route('recommendations', [], false)}}'"
            :is-mobile="true"
            class="{{ set_active(['recommendations', 'recommendations/*']) }}"
        ></notification-recommendations>
        <notification-documents
            :userid="{{auth()->id()}}"
            :documents="{{auth()->user()->unreadDocuments}}"
            :category-url="'{{route('reports', [], false)}}'"
            :is-mobile="true"
            class="{{ set_active(['reports', 'reports/*']) }}"
        ></notification-documents>
    </ul>
</footer>
<modals-container/>
