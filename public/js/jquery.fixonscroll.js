;(function($) {
    $.fn.fixOnScroll = function(options) {
        this.each(function(){
            var b = $(this),
                o = {},

                sBoxTop = 'boxTop',
                sBoxHeight = 'boxHeight',
                sBlockHeight = 'blockHeight',
                sViewPortHeight = 'viewPortHeight',
                sFixedBottom = 'fixedBottom',
                sFixedTop = 'fixedTop',
                sForceBind = 'forceBind',

                scrollLeft,
                scrollLeftPrev = 0,
                left,
                width,
                originalStyle = b[0].style.cssText,
                status = 'top'; // bottom, f-top, f-bottom


            function option(key) {
                return typeof o[key] == 'function' ? o[key]() : o[key];
            }

            function init() {
                left = b.position().left;
                width = b.width() + 'px';

                o[sBoxTop] = options[sBoxTop] || b.offset().top;
                o[sBoxHeight] = options[sBoxHeight] || b.parent().height();
                o[sBlockHeight] = options[sBlockHeight] || b.height() + (parseInt(b.css('marginTop')) || 0) + (parseInt(b.css('marginBottom')) || 0);
                o[sViewPortHeight] = options[sViewPortHeight] || function(){return $(window).height();};
                o[sFixedTop] = options[sFixedTop] || b.offset().top - option(sBoxTop);
                o[sFixedBottom] = options[sFixedBottom] || 0;
                o[sForceBind] = options[sForceBind] || 0;
            }

            init();


            function setFixedShort() {
                /**
                 * top -> f-top
                 * f-top -> bottom
                 *
                 * bottom -> f-bottom
                 * f-bottom -> top
                 */
                if ('top' == status && 'f-top' != status) {
                    b.css({position: 'fixed', top: option(sFixedTop), bottom: 'auto', left: left, width: width});
                    status = 'f-top';
                }
                else if ('bottom'== status && 'f-bottom' != status) {
                    b.css({position: 'fixed', top: 'auto', bottom: option(sFixedBottom), left: left, width: width});
                    status = 'f-bottom';
                }

            }


            function setFixedLong() {
                /**
                 * top -> f-bottom
                 * f-bottom -> bottom
                 *
                 * bottom -> f-top
                 * f-top -> top
                 */

                var l = (left - $(window).scrollLeft()) + 'px';

                if ('top' == status && 'f-bottom' != status) {
                    b.css({position: 'fixed', top: 'auto', bottom: option(sFixedBottom), left: l, width: width});
                    status = 'f-bottom';
                }
                else if ('bottom'== status && 'f-top' != status) {
                    b.css({position: 'fixed', top: option(sFixedTop), bottom: 'auto', left: l, width: width});
                    status = 'f-top';
                }
            }


            /*
             var originalPosition = b.css('position'),
             originalTop = b.css('top'),
             originalLeft = b.css('left'),
             originalBottom = b.css('bottom');
             */

            function setTop() {
                if ('top' != status) {
                    //b.css({position: 'relative', 'top': 0, bottom: 'auto'});
                    //b.css({position: originalPosition, top: originalTop, bottom: originalBottom, left:originalLeft});
                    b[0].style.cssText = originalStyle;
                    status = 'top';
                }
            }

            function setBottom(top) {
                if ('bottom' != status) {
                    b[0].style.cssText = originalStyle;
                    b.css({position: 'relative', 'top': top + 'px', bottom: 'auto', width: width});
                    status = 'bottom';
                }
            }

            function scrollEvent() {
                var oBlockHeight = option(sBlockHeight),
                    oViewPortHeight = option(sViewPortHeight),
                    oBoxTop = option(sBoxTop),
                    oBoxHeight = option(sBoxHeight),
                    oFixedTop = option(sFixedTop),
                    oFixedBottom = option(sFixedBottom),
                    viewPortTop = ($("html").scrollTop() > 0) ? $("html").scrollTop() : $("body").scrollTop(),
                    viewPortBottom = viewPortTop + oViewPortHeight - oFixedTop - oFixedBottom,
                    boxBottom = oBoxTop + oBoxHeight,
                    boxBottomPadded = boxBottom - oBlockHeight,
                    boxTopPadded = oBoxTop + oBlockHeight;



                if (oBoxHeight <= oBlockHeight + oFixedTop + oFixedBottom) {
                    return;
                }

                if (oViewPortHeight > oBlockHeight) {
                    if (viewPortTop < oBoxTop) {
                        setTop();
                        return;
                    }

                    if (viewPortTop > boxBottomPadded - oFixedTop - oFixedBottom) {
                        setBottom(oBoxHeight - oBlockHeight - oFixedTop - oFixedBottom);
                        return;
                    }

                    setFixedShort();
                }


                else {
                    if (viewPortBottom < boxTopPadded) {
                        setTop();
                        return;
                    }
                    if (viewPortBottom > boxBottom - oFixedTop - oFixedBottom) {
                        setBottom(oBoxHeight - oBlockHeight - oFixedTop - oFixedBottom);
                        return;
                    }

                    setFixedLong();
                }

                scrollLeft = $(window).scrollLeft();
                if (scrollLeft != scrollLeftPrev) {
                    b.css('left', left - scrollLeft);
                    scrollLeftPrev = scrollLeft;
                }

            }

            function resizeEvent() {
                setTop();
                init();
                scrollEvent();
            }

            if (option(sForceBind) || option(sBoxHeight) > option(sBlockHeight)) {
                $(window).scroll(scrollEvent).resize(resizeEvent);
            }

            scrollEvent();

        });

        return this;
    };
}(jQuery));