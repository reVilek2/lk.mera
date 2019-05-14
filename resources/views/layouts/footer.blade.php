<div class="footer">
    <div class="container">
        <div class="footer__nav">
            <div class="footer__links">
                <div class="footer__title">WEALTHMAN</div>
                <ul class="footer__links-list">
                    <li><a class="link link_white" href="{{ route('roboAdvisors') }}">Advisor screener</a></li>
                    <li><a class="link link_white" href="{{ route('blog.index') }}">Blog</a></li>
                    <li><a class="link link_white" target="_blank" href="https://dapp.wealthman.io/">Investor relation</a></li>
                    <li><a class="link link_white" href="{{route('contacts')}}">Contacts</a></li>
                    {{--<li><a class="link link_white" href="#">About us</a></li>--}}
                </ul>
            </div>
            <div class="footer__links">
                <div class="footer__title">COMMUNITY</div>
                <ul class="footer__links-list">
                    <li><a class="link link_white" target="_blank" href="https://t.me/wealthman_global">Telegram</a></li>
                    <li><a class="link link_white" target="_blank" href="https://www.facebook.com/Wealthman.io">Facebook</a></li>
                    <li><a class="link link_white" target="_blank" href="https://www.instagram.com/wealthman_platform/">Instagram</a></li>
                    <li><a class="link link_white" target="_blank" href="https://bitcointalk.org/index.php?topic=2006205">Bitcointalk</a></li>
                </ul>
            </div>
            <div class="footer__links">
                <div class="footer__title">ROBO-ADVISORS</div>
                <ul class="footer__links-list">
                    @foreach(popularRoboAdvisors() as $popularRoboAdvisor)
                        <li><a class="link link_white" href="{{ route('roboAdvisorsShow', $popularRoboAdvisor->slug) }}">{{ $popularRoboAdvisor->name }}</a></li>
                    @endforeach
                    <li><a class="link link_white" href="{{ route('roboAdvisors') }}">More robo-advisors</a></li>
                </ul>
            </div>
            <div class="footer__links">
                <div class="footer__title">HAVE A QUESTIONS?</div>
                <div>
                    <a class="button button_blue js-jivo-button" href="#">Ask Question</a>
                </div>
            </div>
        </div>
        <div class="footer__bottom">
            <div class="footer__copy">
                Copyright © 2018 Wealthman LTD. All Rights Reserved. Privacy Policy
                <br>
                5 Persy st., London, W1T 1DG, UK
            </div>
        </div>
    </div>
</div>
