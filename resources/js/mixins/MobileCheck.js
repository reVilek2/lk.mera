const MobileCheck = {
    props: {
        agentType: {
            type: String,
            default: () => 'desktop'
        },
    },
    data: function() {
        return {
            mobileWindowWidth: 767,
            windowInnerWidth: 0,
        }
    },
    computed: {
        isMobile: function () {

            return this.agentType === 'mobile' || (this.windowInnerWidth > 0 && this.mobileWindowWidth >= this.windowInnerWidth);
        }
    },
    methods: {
        handleResize() {
            this.windowInnerWidth = window.innerWidth;
        }
    },
    created() {
        window.addEventListener('resize', this.handleResize);
        this.handleResize();
    },
    destroyed() {
        window.removeEventListener('resize', this.handleResize);
    }
}

export default MobileCheck;
