<template>
  <textarea
          @focus="resize"
          v-model="val"
          :style="computedStyles"
          @keydown="newlineHandle"
  ></textarea>
</template>
<script>
    export default {
        created () {
            this.updateVal() // fill val with initial value passed via the same name prop
        },
        mounted () {
            this.resize() // perform initial height adjustment
        },
        props: {
            /*
             * Property to accept value from parent
             */
            value: {
                type: [String, Number],
                default: ''
            },
            /*
             * Allow to enable/disable auto resizing dynamically
             */
            autosize: {
                type: Boolean,
                default: true
            },
            /*
             * Min textarea height
             */
            minHeight: {
                type: [Number],
                'default': null
            },
            /*
             * Max textarea height
             */
            maxHeight: {
                type: [Number],
                'default': null
            },
            /*
             * Force !important for style properties
             */
            important: {
                type: [Boolean, Array],
                default: false
            }
        },
        data () {
            return {
                // data property for v-model binding with real textarea tag
                val: null,
                // works when content height becomes more then value of the maxHeight property
                maxHeightScroll: false
            }
        },
        computed: {
            /*
             */
            computedStyles () {
                let objStyles = {}
                if (this.autosize) {
                    objStyles.resize = !this.isResizeImportant ? 'none' : 'none !important'
                    if (!this.maxHeightScroll) {
                        objStyles.overflow = !this.isOverflowImportant ? 'hidden' : 'hidden !important'
                    }
                }
                return objStyles
            },
            isResizeImportant () {
                const imp = this.important
                return imp === true || (Array.isArray(imp) && imp.includes('resize'))
            },
            isOverflowImportant () {
                const imp = this.important
                return imp === true || (Array.isArray(imp) && imp.includes('overflow'))
            },
            isHeightImportant () {
                const imp = this.important
                return imp === true || (Array.isArray(imp) && imp.includes('height'))
            }
        },
        methods: {
            newlineHandle(e) {
                if (e.keyCode === 13 && e.ctrlKey) {
                    e.preventDefault();
                    let p = this.getInputSelection(e.target).start;
                    let cv = $(e.target).val();
                    $(e.target).val(cv.substr(0, p) + "\r\n" + cv.substr(p));
                    this.setCaretPosition(e.target, p + 1);
                    this.val = $(e.target).val();
                }
            },
            getInputSelection(el){
                let start = 0, end = 0, normalizedValue, range, textInputRange, len, endRange;
                if (typeof el.selectionStart === "number" && typeof el.selectionEnd === "number") {
                    start = el.selectionStart;
                    end = el.selectionEnd;
                }
                else {
                    range = document.selection.createRange();
                    if (range && range.parentElement() === el) {
                        len = el.value.length;
                        normalizedValue = el.value.replace(/\r\n/g, "\n");

                        textInputRange = el.createTextRange();
                        textInputRange.moveToBookmark(range.getBookmark());

                        endRange = el.createTextRange();
                        endRange.collapse(false);

                        if (textInputRange.compareEndPoints("StartToEnd", endRange) > -1) {
                            start = end = len;
                        } else {
                            start = -textInputRange.moveStart("character", -len);
                            start += normalizedValue.slice(0, start).split("\n").length - 1;

                            if (textInputRange.compareEndPoints("EndToEnd", endRange) > -1) {
                                end = len;
                            } else {
                                end = -textInputRange.moveEnd("character", -len);
                                end += normalizedValue.slice(0, end).split("\n").length - 1;
                            }
                        }
                    }
                }
                return {
                    start: start,
                    end: end
                };
            },
            setCaretPosition(ctrl, pos){
                if(ctrl.setSelectionRange)
                {
                    ctrl.focus();
                    ctrl.setSelectionRange(pos,pos);
                }
                else if (ctrl.createTextRange) {
                    let range = ctrl.createTextRange();
                    range.collapse(true);
                    range.moveEnd('character', pos);
                    range.moveStart('character', pos);
                    range.select();
                }
            },

            /*
             * Update local val with prop value
             */
            updateVal () {
                this.val = this.value
            },
            /*
             * Auto resize textarea by height
             */
            resize: function () {
                const important = this.isHeightImportant ? 'important' : ''
                this.$el.style.setProperty('height', 'auto', important)
                let contentHeight = this.$el.scrollHeight + 1
                if (this.minHeight) {
                    contentHeight = contentHeight < this.minHeight ? this.minHeight : contentHeight
                }
                if (this.maxHeight) {
                    if (contentHeight > this.maxHeight) {
                        contentHeight = this.maxHeight
                        this.maxHeightScroll = true
                    } else {
                        this.maxHeightScroll = false
                    }
                }
                const heightVal = contentHeight + 'px'
                this.$el.style.setProperty('height', heightVal, important)
                return this
            }
        },
        watch: {
            /*
             * Update val from prop when changed in parent
             */
            value () {
                this.updateVal()
            },
            /*
             * Emit input event as in https://vuejs.org/v2/guide/components.html#Form-Input-Components-using-Custom-Events
             */
            val (val) {
                this.$nextTick(this.resize)
                this.$emit('input', val)
            }
        }
    }
</script>