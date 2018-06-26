;(function ($) {
    "use strict";

    var KUTETHEMES_FRAMEWORK = {
        init: function () {
            this.kuteshop_init_carousel();
            this.kuteshop_alert_variable_product();
            this.kuteshop_ajax_tabs();
            this.kuteshop_ajax_tabs_filter();
            this.kuteshop_countdown();
            this.kuteshop_ajax_add_to_cart_single();
            this.kuteshop_woo_quantily();
            this.kuteshop_show_other_item_vertical_menu();
            this.kuteshop_google_maps();
            this.kuteshop_category_product();
            this.kuteshop_category_vertical();
            this.kuteshop_click_action();
            this.kuteshop_dropdown();
            this.kuteshop_init_popup();
            this.kuteshop_to_section();
            this.kuteshop_auto_width_vertical_menu();
            this.kuteshop_resizeMegamenu();
            this.kuteshop_better_equal_elems();
            this.kuteshop_clone_main_menu();
            this.kuteshop_box_mobile_menu();
            this.kuteshop_parallaxInit();
            this.kuteshop_init_lazy_load();
        },
        onReady: function () {
            this.kuteshop_fix_full_width_row_rtl();
        },
        onChange: function () {
            this.kuteshop_alert_variable_product();
        },
        onResize: function () {
            this.kuteshop_better_equal_elems();
            this.kuteshop_resizeMegamenu();
            this.kuteshop_clone_main_menu();
            this.kuteshop_box_mobile_menu();
            this.kuteshop_auto_width_vertical_menu();
        },
        onScroll: function () {
            this.kuteshop_sticky_header();
            if ( $(window).scrollTop() > 200 ) {
                $('.backtotop').addClass('active');
            } else {
                $('.backtotop').removeClass('active');
            }
        },
        kuteshop_parallaxInit: function () {
            if ( $('.vc_parallax').length > 0 ) {
                //Mobile Detect
                var testMobile,
                    isMobile = {
                        Android: function () {
                            return navigator.userAgent.match(/Android/i);
                        },
                        BlackBerry: function () {
                            return navigator.userAgent.match(/BlackBerry/i);
                        },
                        iOS: function () {
                            return navigator.userAgent.match(/iPhone|iPad|iPod/i);
                        },
                        Opera: function () {
                            return navigator.userAgent.match(/Opera Mini/i);
                        },
                        Windows: function () {
                            return navigator.userAgent.match(/IEMobile/i);
                        },
                        any: function () {
                            return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
                        }
                    };

                testMobile = isMobile.any();
                if ( testMobile == null ) {
                    $('.vc_parallax').each(function () {
                        $(this).parallax('50%', 0.3);
                    });
                }

                if ( isMobile.iOS() ) {
                    $('.vc_parallax').each(function () {
                        $(this).css({
                            "background-size": "auto 100%",
                            "background-attachment": "scroll"
                        });
                    });
                }
            }
        },
        kuteshop_get_scrollbar_width: function () {
            var _value1 = jQuery('<div style="width: 100%; height:200px;">test</div>'),
                _value2 = jQuery('<div style="width:200px;height:150px; position: absolute; top: 0; left: 0; visibility: hidden; overflow:hidden;"></div>').append(_value1),
                _width1 = _value1[ 0 ].offsetWidth,
                _width2 = _value2[ 0 ].clientWidth;
            $('body').append(_value2[ 0 ]);
            _value2.css('overflow', 'scroll');
            _value2.remove();
            return (_width1 - _width2);
        },
        kuteshop_init_carousel: function () {
            if ( $('.flex-control-thumbs').length == 1 ) {
                $('.flex-control-thumbs').not('.slick-initialized').each(function () {
                    var _this   = $(this),
                        _config = [];

                    if ( $('body').hasClass('rtl') ) {
                        _config.rtl = true;
                    }
                    _config.prevArrow     = '<span class="pe-7s-angle-left"></span>';
                    _config.nextArrow     = '<span class="pe-7s-angle-right"></span>';
                    _config.focusOnSelect = true;
                    _config.infinite      = false;
                    _config.slidesToShow  = 3;
                    _config.cssEase       = 'linear';
                    _this.slick(_config);
                })
            }
            $('.owl-slick').not('.slick-initialized').each(function () {
                var _this       = $(this),
                    _responsive = _this.data('responsive'),
                    _config     = [];

                if ( $('body').hasClass('rtl') ) {
                    _config.rtl = true;
                }
                if ( _this.hasClass('custom-dots') ) {
                    _config.customPaging = function (slider, i) {
                        var thumb = $(slider.$slides[ i ]).data('thumb');
                        return '<figure><img src="' + thumb + '" alt="kuteshop"></figure>';
                    };
                }
                if ( _this.hasClass('slick-vertical') ) {
                    _config.prevArrow = '<span class="pe-7s-angle-up"></span>';
                    _config.nextArrow = '<span class="pe-7s-angle-down"></span>';
                } else {
                    _config.prevArrow = '<span class="pe-7s-angle-left"></span>';
                    _config.nextArrow = '<span class="pe-7s-angle-right"></span>';
                }
                _config.responsive = _responsive;
                _config.cssEase    = 'linear';

                _this.slick(_config);
                _this.on('afterChange', function (event, slick, direction) {
                    _this.find('.slick-active:first').addClass('first-slick');
                    _this.find('.slick-active:last').addClass('last-slick');
                });
                _this.on('beforeChange', function (event, slick, currentSlide) {
                    KUTETHEMES_FRAMEWORK.kuteshop_init_lazy_load();
                    _this.find('.slick-slide').removeClass('last-slick');
                    _this.find('.slick-slide').removeClass('first-slick');
                });
                if ( _this.hasClass('slick-vertical') ) {
                    KUTETHEMES_FRAMEWORK.kuteshop_better_equal_elems();
                    setTimeout(function () {
                        _this.slick('setPosition');
                    }, 0);
                }
                _this.find('.slick-active:first').addClass('first-slick');
                _this.find('.slick-active:last').addClass('last-slick');
            });
            if ( $('.owl-slick').hasClass('custom-dots') ) {
                $('.custom-dots .slick-dots').not('.slick-initialized').each(function () {
                    var _config = [];
                    if ( $('body').hasClass('rtl') ) {
                        _config.rtl = true;
                    }
                    _config.slidesToShow  = 3;
                    _config.initialSlide  = 1;
                    _config.centerPadding = 0;
                    _config.infinite      = false;
                    _config.centerMode    = true;
                    _config.focusOnSelect = true;
                    _config.arrows        = false;
                    _config.dots          = true;
                    $(this).slick(_config);
                })
            }
        },
        kuteshop_to_section: function () {
            $('.kuteshop-tabs').each(function (index, el) {
                $(this).find('.section-down').on('click', function (e) {
                    if ( $('.kuteshop-tabs').eq(index + 1).length == 1 ) {
                        $('html, body').animate({
                            scrollTop: $('.kuteshop-tabs').eq(index + 1).offset().top - 100
                        }, 'slow');
                    }

                    e.preventDefault();
                });
                $(this).find('.section-up').on('click', function (e) {
                    if ( $('.kuteshop-tabs').eq(index - 1).length == 1 ) {
                        $('html, body').animate({
                            scrollTop: $('.kuteshop-tabs').eq(index - 1).offset().top - 100
                        }, 'slow');
                    }

                    e.preventDefault();
                });
            });
        },
        kuteshop_dropdown: function () {
            $(document).on('click', function (event) {
                var _value1 = $(event.target).closest('.kuteshop-dropdown'),
                    _value2 = $('.kuteshop-dropdown');

                if ( _value1.length > 0 ) {
                    _value2.not(_value1).removeClass('open');
                    if (
                        $(event.target).is('[data-kuteshop="kuteshop-dropdown"]') ||
                        $(event.target).closest('[data-kuteshop="kuteshop-dropdown"]').length > 0
                    ) {
                        _value1.toggleClass('open');
                        event.preventDefault();
                    }
                } else {
                    $('.kuteshop-dropdown').removeClass('open');
                }
            });
        },
        kuteshop_resizeMegamenu: function () {
            var _window_size = jQuery('body').innerWidth();
            _window_size += KUTETHEMES_FRAMEWORK.kuteshop_get_scrollbar_width();
            if ( _window_size > 1024 ) {
                $('.main-menu-wapper').each(function () {
                    var _this             = $(this),
                        _container_offset = _this.offset(),
                        _item_megamenu    = _this.find('.main-menu .item-megamenu'),
                        _width            = _this.innerWidth();
                    if ( _this.length > 0 && _this != 'undefined' && _item_megamenu.length > 0 ) {
                        _item_megamenu.each(function (index, element) {
                            var _self     = $(element),
                                _megamenu = _self.children('.megamenu');

                            _megamenu.css({'max-width': _width + 'px'});
                            var _sub_menu_width  = _megamenu.outerWidth(),
                                _item_width      = _self.outerWidth(),
                                _container_left  = _container_offset.left,
                                _container_right = (_container_left + _width),
                                _item_left       = _self.offset().left,
                                _overflow_left   = (_sub_menu_width / 2 > (_item_left - _container_left)),
                                _overflow_right  = ((_sub_menu_width / 2 + _item_left) > _container_right),
                                _left            = (_item_left - _container_left);

                            _megamenu.css({
                                'left': '-' + (_sub_menu_width / 2 - _item_width / 2) + 'px',
                                'right': 'auto'
                            });
                            if ( _overflow_left ) {
                                _megamenu.css({
                                    'left': -_left + 'px',
                                    'right': 'auto'
                                });
                            }
                            if ( _overflow_right && !_overflow_left ) {
                                _left = _left - (_width - _sub_menu_width);
                                _megamenu.css({
                                    'left': -_left + 'px',
                                    'right': 'auto'
                                });
                            }
                        })
                    }
                })
            }
        },
        kuteshop_auto_width_vertical_menu: function () {
            if ( $(window).innerWidth() > 1024 ) {
                setTimeout(function () {
                    var _width1 = parseInt($('.container').innerWidth()) - 30,
                        _width2 = parseInt($('.verticalmenu-content').outerWidth()),
                        _value  = (_width1 - _width2);
                    $('.verticalmenu-content').find('.megamenu').each(function () {
                        $(this).css('max-width', _value + 'px');
                    });
                }, 100)
            }
        },
        kuteshop_show_other_item_vertical_menu: function () {
            if ( $('.block-nav-category').length > 0 ) {
                var _value2 = 0;
                $('.block-nav-category').each(function () {
                    var _value1 = $(this).data('items') - 1;
                    _value2     = $(this).find('.vertical-menu>li').length;

                    if ( _value2 > (_value1 + 1) ) {
                        $(this).addClass('show-button-all');
                    }
                    $(this).find('.vertical-menu>li').each(function (i) {
                        _value2 = _value2 + 1;
                        if ( i > _value1 ) {
                            $(this).addClass('link-other');
                        }
                    })
                })
            }
        },
        kuteshop_category_product: function () {
            if ( $('.widget_product_categories .product-categories').length > 0 ) {
                $('.widget_product_categories .product-categories').each(function () {
                    var _main = $(this);
                    _main.find('.cat-parent').each(function () {
                        if ( $(this).hasClass('current-cat-parent') ) {
                            $(this).addClass('show-sub');
                            $(this).children('.children').slideDown(400);
                        }
                        $(this).children('.children').before('<span class="carets"></span>');
                    });
                    _main.children('.cat-parent').each(function () {
                        var curent = $(this).find('.children');
                        $(this).children('.carets').on('click', function () {
                            $(this).parent().toggleClass('show-sub');
                            $(this).parent().children('.children').slideToggle(400);
                            _main.find('.children').not(curent).slideUp(400);
                            _main.find('.cat-parent').not($(this).parent()).removeClass('show-sub');
                        });
                        var next_curent = $(this).find('.children');
                        next_curent.children('.cat-parent').each(function () {
                            var child_curent = $(this).find('.children');
                            $(this).children('.carets').on('click', function () {
                                $(this).parent().toggleClass('show-sub');
                                $(this).parent().parent().find('.cat-parent').not($(this).parent()).removeClass('show-sub');
                                $(this).parent().parent().find('.children').not(child_curent).slideUp(400);
                                $(this).parent().children('.children').slideToggle(400);
                            })
                        });
                    });
                });
            }
        },
        kuteshop_category_vertical: function () {
            if ( $('.block-nav-category .vertical-menu').length > 0 ) {
                $('.block-nav-category .vertical-menu').each(function () {
                    var _main = $(this);
                    _main.children('.menu-item.parent').each(function () {
                        var curent = $(this).find('.submenu');
                        $(this).children('.toggle-submenu').on('click', function () {
                            $(this).parent().children('.submenu').slideToggle(500);
                            _main.find('.submenu').not(curent).slideUp(500);
                            $(this).parent().toggleClass('show-submenu');
                            _main.find('.menu-item.parent').not($(this).parent()).removeClass('show-submenu');
                        });
                        var next_curent = $(this).find('.submenu');
                        next_curent.children('.menu-item.parent').each(function () {
                            var child_curent = $(this).find('.submenu');
                            $(this).children('.toggle-submenu').on('click', function () {
                                $(this).parent().parent().find('.submenu').not(child_curent).slideUp(500);
                                $(this).parent().children('.submenu').slideToggle(500);
                                $(this).parent().parent().find('.menu-item.parent').not($(this).parent()).removeClass('show-submenu');
                                $(this).parent().toggleClass('show-submenu');
                            })
                        });
                    });
                });
                $(document).on('click', '.open-cate', function (e) {
                    $(this).closest('.block-nav-category').find('li.link-other').each(function () {
                        $(this).slideDown();
                    });
                    $(this).addClass('close-cate').removeClass('open-cate').html($(this).data('closetext'));
                    e.preventDefault();
                });
                $(document).on('click', '.close-cate', function (e) {
                    $(this).closest('.block-nav-category').find('li.link-other').each(function () {
                        $(this).slideUp();
                    });
                    $(this).addClass('open-cate').removeClass('close-cate').html($(this).data('alltext'));
                    e.preventDefault();
                });

                $('.block-nav-category .block-title').on('click', function () {
                    $(this).toggleClass('active');
                    $(this).parent().toggleClass('has-open');
                    $('body').toggleClass('category-open');
                });
            }
            if ( $('.category-search-option').length > 0 ) {
                $('.category-search-option').chosen();
            }
        },
        kuteshop_clone_main_menu: function () {
            var _header_clone  = $(document.getElementById('header')).find('.clone-main-menu'),
                _mobile_clone  = $(document.getElementById('box-mobile-menu')).find('.clone-main-menu'),
                _header_target = $(document.getElementById('header')).find('.box-header-nav'),
                _mobile_target = $(document.getElementById('box-mobile-menu')).find('.box-inner');

            if ( $(window).innerWidth() <= 1024 ) {
                if ( _header_clone.length > 0 ) {
                    _header_clone.each(function () {
                        $(this).appendTo(_mobile_target);
                    });
                }
            } else {
                if ( _mobile_clone.length > 0 ) {
                    _mobile_clone.each(function () {
                        $(this).appendTo(_header_target);
                    });
                }
            }
        },
        kuteshop_box_mobile_menu: function () {
            if ( $(window).innerWidth() <= 1024 ) {
                var _content_mobile = $(document.getElementById('box-mobile-menu')),
                    _back_button    = _content_mobile.find('#back-menu'),
                    _clone_menu     = _content_mobile.find('.clone-main-menu'),
                    _title_menu     = _content_mobile.find('.box-title');

                _clone_menu.each(function () {
                    var _this = $(this);
                    _this.addClass('active');
                    _this.find('.toggle-submenu').on('click', function (e) {
                        var _self      = $(this),
                            _text_next = _self.prev().text();

                        _this.removeClass('active');
                        _title_menu.html(_text_next);
                        _this.find('li').removeClass('mobile-active');
                        _self.parent().addClass('mobile-active');
                        _self.parent().closest('.submenu').css({
                            'position': 'static',
                            'height': '0',
                        });
                        _back_button.css('display', 'block');
                        e.preventDefault();
                    })
                });
                _back_button.on('click', function (e) {
                    _clone_menu.find('li.mobile-active').each(function () {
                        _clone_menu.find('li').removeClass('mobile-active');
                        if ( $(this).parent().hasClass('main-menu') ) {
                            _clone_menu.addClass('active');
                            _title_menu.html('MAIN MENU');
                            _back_button.css('display', 'none');
                        } else {
                            _clone_menu.removeClass('active');
                            $(this).parent().parent().addClass('mobile-active');
                            $(this).parent().css({
                                'position': 'absolute',
                                'height': 'auto',
                            });
                            var text_prev = $(this).parent().parent().children('a').text();
                            _title_menu.html(text_prev);
                        }
                    })
                    e.preventDefault();
                });
            } else {
                $('html').css('overflow', 'visible');
                $('body').removeClass('box-mobile-menu-open');
            }
            $('.mobile-navigation').on('click', function (e) {
                $('html').css('overflow', 'hidden');
                $('body').addClass('box-mobile-menu-open');
                e.preventDefault();
            });
            $('#box-mobile-menu .close-menu,.body-overlay').on('click', function (e) {
                $('html').css('overflow', 'visible');
                $('body').removeClass('box-mobile-menu-open');
                e.preventDefault();
            });
        },
        kuteshop_google_maps: function () {
            if ( $('.kuteshop-google-maps').length == 1 && kuteshop_global_frontend.kuteshop_gmap_api_key != '' ) {
                $('.kuteshop-google-maps').each(function () {
                    var $this            = $(this),
                        $id              = $this.attr('id'),
                        $title_maps      = $this.attr('data-title_maps'),
                        $phone           = $this.attr('data-phone'),
                        $email           = $this.attr('data-email'),
                        $zoom            = parseInt($this.attr('data-zoom')),
                        $latitude        = $this.attr('data-latitude'),
                        $longitude       = $this.attr('data-longitude'),
                        $address         = $this.attr('data-address'),
                        $map_type        = $this.attr('data-map-type'),
                        $pin_icon        = $this.attr('data-pin-icon'),
                        $modify_coloring = $this.attr('data-modify-coloring') === "true" ? true : false,
                        $saturation      = $this.attr('data-saturation'),
                        $hue             = $this.attr('data-hue'),
                        $map_style       = $this.data('map-style'),
                        $styles;

                    if ( $modify_coloring == true ) {
                        var $styles = [
                            {
                                stylers: [
                                    {hue: $hue},
                                    {invert_lightness: false},
                                    {saturation: $saturation},
                                    {lightness: 1},
                                    {
                                        featureType: "landscape.man_made",
                                        stylers: [ {
                                            visibility: "on"
                                        } ]
                                    }
                                ]
                            }, {
                                featureType: 'water',
                                elementType: 'geometry',
                                stylers: [
                                    {color: '#46bcec'}
                                ]
                            }
                        ];
                    }
                    var map,
                        bounds     = new google.maps.LatLngBounds(),
                        mapOptions = {
                            zoom: $zoom,
                            panControl: true,
                            zoomControl: true,
                            mapTypeControl: true,
                            scaleControl: true,
                            draggable: true,
                            scrollwheel: false,
                            mapTypeId: google.maps.MapTypeId[ $map_type ],
                            styles: $styles
                        };

                    map = new google.maps.Map(document.getElementById($id), mapOptions);
                    map.setTilt(45);

                    var markers           = [],
                        infoWindowContent = [];

                    if ( $latitude != '' && $longitude != '' ) {
                        markers[ 0 ]           = [ $address, $latitude, $longitude ];
                        infoWindowContent[ 0 ] = [ $address ];
                    }

                    var infoWindow = new google.maps.InfoWindow(), marker, i;

                    for ( i = 0; i < markers.length; i++ ) {
                        var position = new google.maps.LatLng(markers[ i ][ 1 ], markers[ i ][ 2 ]);
                        bounds.extend(position);
                        marker = new google.maps.Marker({
                            position: position,
                            map: map,
                            title: markers[ i ][ 0 ],
                            icon: $pin_icon
                        });
                        if ( $map_style == '1' ) {

                            if ( infoWindowContent[ i ][ 0 ].length > 1 ) {
                                infoWindow.setContent(
                                    '<div style="background-color:#fff; padding: 30px 30px 10px 25px; width:290px;line-height: 22px" class="kuteshop-map-info">' +
                                    '<h4 class="map-title">' + $title_maps + '</h4>' +
                                    '<div class="map-field"><i class="fa fa-map-marker"></i><span>&nbsp;' + $address + '</span></div>' +
                                    '<div class="map-field"><i class="fa fa-phone"></i><span>&nbsp;' + $phone + '</span></div>' +
                                    '<div class="map-field"><i class="fa fa-envelope"></i><span><a href="mailto:' + $email + '">&nbsp;' + $email + '</a></span></div> ' +
                                    '</div>'
                                );
                            }

                            infoWindow.open(map, marker);

                        }
                        if ( $map_style == '2' ) {
                            google.maps.event.addListener(marker, 'click', (function (marker, i) {
                                return function () {
                                    if ( infoWindowContent[ i ][ 0 ].length > 1 ) {
                                        infoWindow.setContent(
                                            '<div style="background-color:#fff; padding: 30px 30px 10px 25px; width:290px;line-height: 22px" class="kuteshop-map-info">' +
                                            '<h4 class="map-title">' + $title_maps + '</h4>' +
                                            '<div class="map-field"><i class="fa fa-map-marker"></i><span>&nbsp;' + $address + '</span></div>' +
                                            '<div class="map-field"><i class="fa fa-phone"></i><span>&nbsp;' + $phone + '</span></div>' +
                                            '<div class="map-field"><i class="fa fa-envelope"></i><span><a href="mailto:' + $email + '">&nbsp;' + $email + '</a></span></div> ' +
                                            '</div>'
                                        );
                                    }

                                    infoWindow.open(map, marker);
                                }
                            })(marker, i));
                        }

                        map.fitBounds(bounds);
                    }

                    var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function (event) {
                        this.setZoom($zoom);
                        google.maps.event.removeListener(boundsListener);
                    });
                });
            }
        },
        kuteshop_woo_quantily: function () {
            $('body').on('click', '.quantity .quantity-plus', function (e) {
                var _this  = $(this).closest('.quantity').find('input.qty'),
                    _value = parseInt(_this.val()),
                    _max   = parseInt(_this.attr('max')),
                    _step  = parseInt(_this.data('step')),
                    _value = _value + _step;
                if ( _max && _value > _max ) {
                    _value = _max;
                }
                _this.val(_value);
                _this.trigger("change");
                e.preventDefault();
            });
            $(document).on('change', function () {
                $('.quantity').each(function () {
                    var _this  = $(this).find('input.qty'),
                        _value = _this.val(),
                        _max   = parseInt(_this.attr('max'));
                    if ( _value > _max ) {
                        $(this).find('.quantity-plus').css('pointer-events', 'none')
                    } else {
                        $(this).find('.quantity-plus').css('pointer-events', 'auto')
                    }
                })
            });
            $('body').on('click', '.quantity .quantity-minus', function (e) {
                var _this  = $(this).closest('.quantity').find('input.qty'),
                    _value = parseInt(_this.val()),
                    _min   = parseInt(_this.attr('min')),
                    _step  = parseInt(_this.data('step')),
                    _value = _value - _step;
                if ( _min && _value < _min ) {
                    _value = _min;
                }
                if ( !_min && _value < 0 ) {
                    _value = 0;
                }
                _this.val(_value);
                _this.trigger("change");
                e.preventDefault();
            });
        },
        kuteshop_ajax_add_to_cart_single: function () {
            $(document).on('click', '.single_add_to_cart_button', function (e) {
                if ( !$(this).hasClass('disabled') && !$(this).closest('.product').hasClass('product-type-external') ) {
                    var _this      = $(this),
                        _ID        = _this.val(),
                        _container = _this.closest('form'),
                        _value     = _container.serialize(),
                        _data      = _value;

                    if ( _ID != '' ) {
                        _data = 'add-to-cart=' + _ID + '&' + _value;
                    }
                    _this.addClass('loading');
                    $.post(wc_add_to_cart_params.wc_ajax_url.toString().replace('wc-ajax=%%endpoint%%', ''), _data, function (response) {
                        $(document.body).trigger('wc_fragment_refresh');
                        _this.removeClass('loading');
                    });
                    e.preventDefault();
                }
            });
        },
        kuteshop_countdown: function () {
            function kuteshop_get_digit() {
                var _text = '',
                    _num  = 0,
                    i     = 0,
                    j     = 1;

                for ( ; i < arguments[ 0 ]; ++i ) {
                    _num = ~~(arguments[ 1 ] / j) % 10;
                    j    = j * 10;
                    _text += '<span class="digit">' + _num + '</span>';
                }
                return _text;
            }

            var _day  = 0,
                _hour = 0,
                _min  = 0,
                _sec  = 0;

            if ( $('.kuteshop-countdown').length > 0 ) {
                $('.kuteshop-countdown').each(function () {
                    var _this           = $(this),
                        _text_countdown = '',
                        _text_hours     = '',
                        _text_mins      = '',
                        _text_secs      = '';

                    _this.countdown(_this.data('datetime'), {defer: false})
                        .on('update.countdown', function (event) {
                            if ( _this.parent().hasClass('style6') ) {
                                if ( event.strftime('%D') != _day ) {
                                    _day = event.strftime('%D');
                                    _this.find('.days .curr').html('<span>' + _day + '</span>');
                                    _this.find('.days .next').html('<span>' + _day + '</span>');
                                    _this.find('.days').addClass('flip');
                                }
                                if ( event.strftime('%H') != _hour ) {
                                    _hour = event.strftime('%H');
                                    _this.find('.hours .curr').html('<span>' + _hour + '</span>');
                                    _this.find('.hours .next').html('<span>' + _hour + '</span>');
                                    _this.find('.hours').addClass('flip');
                                }
                                if ( event.strftime('%M') != _min ) {
                                    _min = event.strftime('%M');
                                    _this.find('.minutes .curr').html('<span>' + _min + '</span>');
                                    _this.find('.minutes .next').html('<span>' + _min + '</span>');
                                    _this.find('.minutes').addClass('flip');
                                }
                                if ( event.strftime('%S') != _sec ) {
                                    _sec = event.strftime('%S');
                                    _this.find('.seconds .curr').html('<span>' + _sec + '</span>');
                                    _this.find('.seconds .next').html('<span>' + _sec + '</span>');
                                    _this.find('.seconds').addClass('flip');
                                }
                                setTimeout(function () {
                                    _this.find('.time').removeClass('flip');
                                }, 500);
                            } else {
                                if ( _this.parent().hasClass('default') ) {
                                    var _length_hours = event.strftime('%I').toString().length,
                                        _num_hours    = event.strftime('%I'),
                                        _length_mins  = event.strftime('%M').toString().length,
                                        _num_mins     = event.strftime('%M'),
                                        _length_secs  = event.strftime('%S').toString().length,
                                        _num_secs     = event.strftime('%S');

                                    _text_hours     = '<span class="hours">' + kuteshop_get_digit(_length_hours, _num_hours) + '</span>';
                                    _text_mins      = '<span class="mins">' + kuteshop_get_digit(_length_mins, _num_mins) + '</span>';
                                    _text_secs      = '<span class="secs">' + kuteshop_get_digit(_length_secs, _num_secs) + '</span>';
                                    _text_countdown = _text_hours + _text_mins + _text_secs;
                                } else {
                                    _text_countdown = event.strftime(
                                        '<span class="days"><span class="number">%D</span><span class="text">Days</span></span>' +
                                        '<span class="hour"><span class="number">%H</span><span class="text">Hrs</span></span>' +
                                        '<span class="mins"><span class="number">%M</span><span class="text">Mins</span></span>' +
                                        '<span class="secs"><span class="number">%S</span><span class="text">Secs</span></span>'
                                    );
                                }
                                _this.html(_text_countdown);
                            }
                        });
                });
            }
        },
        kuteshop_init_lazy_load: function () {
            if ( $('.lazy').length > 0 ) {
                var _config = [];

                _config.beforeLoad     = function (element) {
                    if ( element.is('div') == true ) {
                        element.addClass('loading-lazy');
                    } else {
                        if ( element.hasClass('vc_single_image-img') ) {
                            element.closest('.vc_figure').addClass('loading-lazy');
                        } else {
                            element.parent().addClass('loading-lazy');
                        }
                    }
                };
                _config.afterLoad      = function (element) {
                    if ( element.is('div') == true ) {
                        element.removeClass('loading-lazy');
                    } else {
                        if ( element.hasClass('vc_single_image-img') ) {
                            element.closest('.vc_figure').removeClass('loading-lazy');
                        } else {
                            element.parent().removeClass('loading-lazy');
                        }
                    }
                };
                _config.effect         = "fadeIn";
                _config.enableThrottle = true;
                _config.throttle       = 250;
                _config.effectTime     = 1000;
                _config.threshold      = 0;
                $('.lazy').lazy(_config);
                //$('.megamenu .lazy').lazy({bind: 'load'});
            }
        },
        kuteshop_animation_tabs: function (_tab_animated, _tab_id) {
            _tab_animated = (_tab_animated == undefined || _tab_animated == "") ? '' : _tab_animated;
            if ( _tab_animated == "" ) {
                return;
            }
            $(_tab_id).find('.owl-slick .slick-active, .product-list-grid .product-item').each(function (i) {
                var _this  = $(this),
                    _style = _this.attr('style'),
                    _delay = i * 200;

                _style = (_style == undefined) ? '' : _style;
                _this.attr('style', _style +
                    ';-webkit-animation-delay:' + _delay + 'ms;'
                    + '-moz-animation-delay:' + _delay + 'ms;'
                    + '-o-animation-delay:' + _delay + 'ms;'
                    + 'animation-delay:' + _delay + 'ms;'
                ).addClass(_tab_animated + ' animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function () {
                    _this.removeClass(_tab_animated + ' animated');
                    _this.attr('style', _style);
                });
            })
        },
        kuteshop_ajax_tabs: function () {
            $(document).on('click', '.kuteshop-tabs .tab-link a', function (e) {
                var _this         = $(this),
                    _ID           = _this.data('id'),
                    _tabID        = _this.attr('href'),
                    _ajax_tabs    = _this.data('ajax'),
                    _sectionID    = _this.data('section'),
                    _tab_animated = _this.data('animate'),
                    _loaded       = _this.closest('.tab-link').find('a.loaded').attr('href');

                if ( _ajax_tabs == 1 && !_this.hasClass('loaded') ) {
                    $(_tabID).closest('.tab-container').addClass('loading');
                    _this.parent().addClass('active').siblings().removeClass('active');
                    $.ajax({
                        type: 'POST',
                        url: kuteshop_ajax_frontend.ajaxurl,
                        data: {
                            action: 'kuteshop_ajax_tabs',
                            security: kuteshop_ajax_frontend.security,
                            id: _ID,
                            section_id: _sectionID,
                        },
                        success: function (response) {
                            if ( response[ 'success' ] == 'ok' ) {
                                $(_tabID).html($(response[ 'html' ]).find('.vc_tta-panel-body').html());
                                $(_loaded).html('');
                                $(_tabID).closest('.tab-container').removeClass('loading');
                                $('[href="' + _loaded + '"]').removeClass('loaded');
                                $(_tabID).addClass('active').siblings().removeClass('active');
                                _this.addClass('loaded');
                            } else {
                                $(_tabID).html('<strong>Error: Can not Load Data ...</strong>');
                            }
                        },
                        complete: function () {
                            KUTETHEMES_FRAMEWORK.kuteshop_countdown();
                            KUTETHEMES_FRAMEWORK.kuteshop_init_carousel();
                            KUTETHEMES_FRAMEWORK.kuteshop_init_lazy_load();
                            KUTETHEMES_FRAMEWORK.kuteshop_better_equal_elems();
                            setTimeout(function () {
                                KUTETHEMES_FRAMEWORK.kuteshop_animation_tabs(_tab_animated, _tabID);
                            }, 100)
                        }
                    });
                } else {
                    _this.parent().addClass('active').siblings().removeClass('active');
                    $(_tabID).addClass('active').siblings().removeClass('active');
                    KUTETHEMES_FRAMEWORK.kuteshop_animation_tabs(_tab_animated, _tabID);
                }

                _this.closest('.kuteshop-tabs').find('.cat-filter').removeClass('cat-active');

                e.preventDefault();
            })
        },

        kuteshop_ajax_tabs_filter: function () {
            $(document).on('click', '.filter-tabs .cat-filter', function (e) {
                var _this      = $(this),
                    _container = _this.closest('.content-tabs').find('.tab-panel.active'),
                    _catID     = _this.data('cat'),
                    _tabID     = _this.data('id'),
                    _check     = 1;
                if ( _this.hasClass('cat-active') ) {
                    _check = 0;
                }
                _container.find('.kuteshop-products').each(function () {
                    var _target       = $(this),
                        _containerID  = _target.data('self_id'),
                        _productStyle = _target.data('list_style');

                    if ( _containerID != '' ) {
                        $('.' + _containerID).addClass('loading');
                        $.ajax({
                            type: 'POST',
                            url: kuteshop_ajax_frontend.ajaxurl,
                            data: {
                                action: 'kuteshop_ajax_tabs_filter',
                                security: kuteshop_ajax_frontend.security,
                                cat: _catID,
                                id: _tabID,
                                check: _check,
                                product_id: _containerID,
                                list_style: _productStyle,
                            },
                            success: function (response) {
                                _this.closest('.filter-tabs').find('.cat-filter').removeClass('cat-active');
                                if ( response[ 'success' ] == 'ok' ) {
                                    if ( _productStyle == 'owl' ) {
                                        $('.' + _containerID).children('.content-product-append').slick('unslick');
                                    }
                                    if ( _check == 1 ) {
                                        $('.' + _containerID).children('.content-product-append').html($(response[ 'html' ]));
                                        _this.addClass('cat-active');
                                    } else {
                                        $('.' + _containerID).children('.content-product-append').html($(response[ 'html' ]).children().html());
                                    }
                                } else {
                                    $('.' + _containerID).html('<strong>Error: Can not Load Data ...</strong>');
                                }
                                $('.' + _containerID).removeClass('loading');
                            },
                            complete: function () {
                                KUTETHEMES_FRAMEWORK.kuteshop_countdown();
                                KUTETHEMES_FRAMEWORK.kuteshop_init_carousel();
                                KUTETHEMES_FRAMEWORK.kuteshop_init_lazy_load();
                                KUTETHEMES_FRAMEWORK.kuteshop_better_equal_elems();
                            }
                        });

                        e.preventDefault();
                    }
                });
            })
        },
        kuteshop_better_equal_elems: function () {
            $('.equal-container.better-height').each(function () {
                if ( $(this).find('.equal-elem').length ) {
                    $(this).find('.equal-elem').css({
                        'height': 'auto'
                    });
                    var _height = 0;
                    $(this).find('.equal-elem').each(function () {
                        if ( _height < $(this).height() ) {
                            _height = $(this).height();
                        }
                    });
                    $(this).find('.equal-elem').height(_height);
                }
            });
        },
        kuteshop_init_popup: function (e) {
            var _popup = document.getElementById('popup-newsletter');
            if ( _popup != null ) {
                if ( kuteshop_global_frontend.kuteshop_enable_popup_mobile != 1 ) {
                    if ( $(window).innerWidth() <= 992 ) {
                        return;
                    }
                }
                var disabled_popup_by_user = getCookie('kuteshop_disabled_popup_by_user');
                if ( disabled_popup_by_user == 'true' ) {
                    return;
                } else {
                    if ( $('body').hasClass('home') && kuteshop_global_frontend.kuteshop_enable_popup == 1 ) {
                        setTimeout(function () {
                            $(_popup).modal({
                                keyboard: false
                            });
                            $(_popup).find('.lazy').lazy({
                                delay: 0
                            });
                        }, kuteshop_global_frontend.kuteshop_popup_delay_time);
                    }
                }
                $(document).on('change', '.kuteshop_disabled_popup_by_user', function () {
                    if ( $(this).is(":checked") ) {
                        setCookie('kuteshop_disabled_popup_by_user', 'true', 7);
                    } else {
                        setCookie('kuteshop_disabled_popup_by_user', '', 0);
                    }
                });
            }

            function setCookie() {
                var d = new Date();
                d.setTime(d.getTime() + (arguments[ 2 ] * 24 * 60 * 60 * 1000));
                var expires     = "expires=" + d.toUTCString();
                document.cookie = arguments[ 0 ] + "=" + arguments[ 1 ] + "; " + arguments[ 2 ];
            }

            function getCookie() {
                var name = arguments[ 0 ] + "=",
                    ca   = document.cookie.split(';'),
                    i    = 0,
                    c    = 0;
                for ( ; i < ca.length; ++i ) {
                    c = ca[ i ];
                    while ( c.charAt(0) == ' ' ) {
                        c = c.substring(1);
                    }
                    if ( c.indexOf(name) == 0 ) {
                        return c.substring(name.length, c.length);
                    }
                }
                return "";
            }
        },
        kuteshop_sticky_header: function () {
            if ( kuteshop_global_frontend.kuteshop_enable_sticky_menu == 1 ) {
                var _head   = document.getElementById('header'),
                    _cat    = document.getElementsByClassName('block-nav-category')[ 0 ],
                    _sticky = document.getElementById('header-sticky-menu'),
                    _height = _head.getBoundingClientRect().height;

                if ( $(window).scrollTop() > _height + 100 ) {
                    _sticky.classList.add('active');
                } else {
                    _sticky.classList.remove('active');
                    if ( _cat != null ) {
                        _cat.classList.remove('has-open');
                    }
                }
            }
        },
        kuteshop_click_action: function () {
            $(document).on('click', 'a.backtotop', function (e) {
                $('html, body').animate({scrollTop: 0}, 800);
                e.preventDefault();
            });
            $(document).on('click', '.toggle-category', function () {
                $(this).closest('.kuteshop-tabs').toggleClass('cat-active');
            });
        },
        kuteshop_alert_variable_product: function () {
            $('.single_add_to_cart_button').each(function () {
                if ( $(this).hasClass('disabled') ) {
                    $(this).popover({
                        content: 'Plz Select option before Add To Cart.',
                        trigger: 'hover'
                    });
                } else {
                    $(this).popover('destroy');
                }
            })
        },
        kuteshop_fix_full_width_row_rtl: function () {
            if ( $('body').hasClass('rtl') ) {
                console.log('Right To Left');
                $('.chosen-container').each(function () {
                    $(this).addClass('chosen-rtl');
                });
                $(document).on('vc-full-width-row', function () {
                    console.log('Start Full Width Row');
                    ovic_force_vc_full_width_row_rtl();
                });

                function ovic_force_vc_full_width_row_rtl() {
                    var _elements = $('[data-vc-full-width="true"]');
                    $.each(_elements, function (key, item) {
                        var $this = $(this);
                        if ( $this.parent('[data-vc-full-width="true"]').length > 0 ) {
                            return;
                        } else {
                            var this_left  = $this.css('left'),
                                this_child = $this.find('[data-vc-full-width="true"]');

                            if ( this_child.length > 0 ) {
                                $this.css({
                                    'left': '',
                                    'right': this_left
                                });
                                this_child.css({
                                    'left': 'auto',
                                    'padding-left': this_left.replace('-', ''),
                                    'padding-right': this_left.replace('-', ''),
                                    'right': this_left
                                });
                            } else {
                                $this.css({
                                    'left': 'auto',
                                    'right': this_left
                                });
                            }
                        }
                    }), $(document).trigger('ovic-force-vc-full-width-row-rtl', _elements);
                }
            }
        }
    }

    function load_libary($url) {
        var wfm   = document.createElement('script');
        wfm.src   = $url;
        wfm.type  = 'text/javascript';
        wfm.defer = true;
        var sm    = document.getElementsByTagName('script')[ 0 ];
        sm.parentNode.insertBefore(wfm, sm);
    }

    $(window).on('change', function () {
        KUTETHEMES_FRAMEWORK.onChange();
    });
    $(window).on('resize', function () {
        KUTETHEMES_FRAMEWORK.onResize();
    });
    $(document).scroll(function () {
        KUTETHEMES_FRAMEWORK.onScroll();
    });
    $(document).ready(function () {
        KUTETHEMES_FRAMEWORK.onReady();
        if ( $('.kuteshop-google-maps').length == 1 && kuteshop_global_frontend.kuteshop_gmap_api_key != '' ) {
            load_libary('//maps.googleapis.com/maps/api/js?key=' + kuteshop_global_frontend.kuteshop_gmap_api_key)
        }
        if ( $('.vc_parallax').length > 0 ) {
            load_libary(kuteshop_global_frontend.kuteshop_parallax)
        }
    });
    window.onload = function () {
        KUTETHEMES_FRAMEWORK.init();
    }

})(jQuery, window, document);