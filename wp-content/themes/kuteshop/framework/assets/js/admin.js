/**
 * Created by khanh on 1/10/2017.
 */
(function ($) {
    "use strict"; // Start of use strict

    /* FRAMEWORK JS */
    $.KUTESHOP = $.KUTESHOP || {};
    // ======================================================
    // KUTESHOP TAB NAVIGATION
    // ------------------------------------------------------
    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires     = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires;
    }
    function getCookie(cname) {
        var name = cname + "=",
            ca   = document.cookie.split(';');
        for ( var i = 0; i < ca.length; i++ ) {
            var c = ca[ i ];
            while ( c.charAt(0) == ' ' ) {
                c = c.substring(1);
            }
            if ( c.indexOf(name) == 0 ) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }
    $.fn.KUTESHOP_TAB_NAVIGATION = function () {
        var _i = 0;
        return this.each(function () {
            var _active_panel = getCookie('tab-active'),
                $this         = $(this),
                $nav          = $this.find('.cs-nav'),
                $reset        = $this.find('.cs-reset'),
                $expand       = $this.find('.cs-expand-all');

            if ( _active_panel != '' ) {
                $this.find('[data-section=' + _active_panel + ']').each(function () {
                    var $el   = $(this),
                        $next = $el.closest('.cs-sub');

                    $('.cs-framework .cs-nav').children('ul').find('ul').slideUp('fast');
                    $nav.find('li').removeClass('cs-tab-active');
                    $nav.find('a').removeClass('cs-section-active');

                    if ( $next.length > 0 ) {
                        $next.children('ul').slideDown('fast');
                        $next.parent().addClass('cs-tab-active');
                        $el.addClass('cs-section-active');
                        $('#cs-tab-' + _active_panel).fadeIn('fast').siblings().hide();
                    } else {
                        $('#cs-tab-' + _active_panel).fadeIn('fast').siblings().hide();
                        $el.addClass('cs-section-active');
                    }
                });
            }

            $nav.find('ul:first a').on('click', function (e) {
                var _get_value = $(this).data('section');
                setCookie('tab-active', _get_value, 1);
                e.preventDefault();

                var $el     = $(this),
                    $next   = $el.next(),
                    $target = $el.data('section');

                $el.parent().parent().find('ul').slideUp('fast');
                $el.parent().parent().find('li').removeClass('cs-tab-active');
                if ( $next.is('ul') ) {
                    $next.slideToggle('fast');
                    $el.closest('li').toggleClass('cs-tab-active');

                } else {
                    $('#cs-tab-' + $target).fadeIn('fast').siblings().hide();
                    $nav.find('a').removeClass('cs-section-active');
                    $el.addClass('cs-section-active');
                    $reset.val($target);
                }

            });

            $expand.on('click', function (e) {
                e.preventDefault();
                $this.find('.cs-body').toggleClass('cs-show-all');
                $(this).find('.fa').toggleClass('fa-eye-slash').toggleClass('fa-eye');
            });

        });
    };
    // =====================================================
    // KUTESHOP DATEPICKER
    // ===================================================
    $.fn.KUTESHOP_DATEPICKER = function () {
        return this.each(function () {
            var $this   = $(this),
                $input  = $this.find('input'),
                options = JSON.parse($this.find('.cs-datepicker-options').val()),
                wrapper = '<div class="cs-datepicker-wrapper"></div>',
                $datepicker;

            var defaults = {
                beforeShow: function (input, inst) {
                    $datepicker = $('#ui-datepicker-div');
                    $datepicker.wrap(wrapper);
                },
                onClose: function () {
                    var cancelInterval = setInterval(function () {
                        if ( $datepicker.is(':hidden') ) {
                            $datepicker.unwrap(wrapper);
                            clearInterval(cancelInterval);
                        }
                    }, 100);
                }
            };

            options = $.extend({}, options, defaults);

            $input.datepicker(options);

        });
    };
    // ======================================================
    // JQUERY STICKY HEADER
    // ------------------------------------------------------
    $.fn.KUTESHOP_STICKYHEADER = function () {
        if ( $(this).find('.cs-header').length === 0 ) {
            return false;
        }
        return this.each(function () {
            var $this        = $(this),
                $window      = $(window),
                $inner       = $this.find('.cs-header'),
                padding      = parseInt($inner.css('padding-left')) + parseInt($inner.css('padding-right')),
                offset       = 32,
                scrollTop    = 0,
                lastTop      = 0,
                ticking      = false,
                onSticky     = function () {

                    scrollTop = $window.scrollTop();
                    requestTick();

                },
                requestTick  = function () {

                    if ( !ticking ) {
                        requestAnimationFrame(function () {
                            stickyUpdate();
                            ticking = false;
                        });
                    }

                    ticking = true;

                },
                stickyUpdate = function () {

                    var offsetTop = $this.offset().top,
                        stickyTop = Math.max(offset, offsetTop - scrollTop),
                        winWidth  = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);

                    if ( stickyTop <= offset && winWidth > 782 ) {
                        $inner.css({width: $this.outerWidth() - padding});
                        $this.css({height: $this.outerHeight()}).addClass('cs-sticky-header');
                    } else {
                        $inner.removeAttr('style');
                        $this.removeAttr('style').removeClass('cs-sticky-header');
                    }

                };

            $window.on('scroll resize', onSticky);

            onSticky();

        });
    };
    // ======================================================
    // SELECT PREVIEW
    // ------------------------------------------------------
    $.fn.KUTESHOP_PREVIEW_SELECT = function () {
        return this.each(function () {
            var url = jQuery(this).find(':selected').data('preview');
            $(this).closest('.container-select_preview').find('.image-preview img').attr('src', url);
        });
    }
    $.fn.KUTESHOP_AUTOCOMPLETE   = function () {
        return this.each(function () {
            if ( $(this).length > 0 ) {
                $(this).chosen();
            }
        })
    }
    // ======================================================
    // JQUERY DOCUMENT READY
    // ------------------------------------------------------
    $(document).ready(function () {
        $('.cs-framework').KUTESHOP_STICKYHEADER();
        $('.cs-field-date', this).KUTESHOP_DATEPICKER();
        $('.cs-select-images').KUTESHOP_PREVIEW_SELECT();
        $('.cs-framework').KUTESHOP_TAB_NAVIGATION();
    });
    $(document).on('change', function () {
        $('.cs-select-images').KUTESHOP_PREVIEW_SELECT();
    });
    /* ---------------------------------------------
     Scripts After Ajax
     --------------------------------------------- */

    $(document).ajaxComplete(function (event, xhr, settings) {
        $('.kuteshop_vc_taxonomy').KUTESHOP_AUTOCOMPLETE();
        $('.cs-field-date', this).KUTESHOP_DATEPICKER();
        $('.cs-select-images').KUTESHOP_PREVIEW_SELECT();
    });
})(jQuery); // End of use strict