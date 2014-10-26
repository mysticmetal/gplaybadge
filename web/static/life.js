/**
 * Created by massimilianocannarozzo on 21/06/14.
 */

const img = $('#badgeImg')
    , code = $('#badgeCode')
    , badgeCarousel = $('#badgeCarousel')
    , packageIdInput = $('#packageIdInput')
    , html = $('#html')
    , bbcode = $('#bbcode')
    , mdown = $('#mdown');
var buildButton;

$(function () {
    var imgSrc;

    $('#buildButton').click(function (event) {
        const packageId = packageIdInput.val();

        event.preventDefault();
        buildButton = Ladda.create(this);
        if (packageId) {
            if (imgSrc == null || img.attr('src').indexOf(packageId) < 0) {
                resetUi();
                fetchBadge(packageId);
            } else {
                showError('Again!?');
            }
        } else {
            showError('Unfortunately I couldn\'t read your mind :)');
        }
    });

    img.on('load',function () {
        imgSrc = img.attr('src');
        const packageId = packageIdInput.val()
            , storeUrl = 'https://play.google.com/store/apps/details?id=' + packageId;

        img.fadeIn(1000);
        code.fadeIn(1000);
        html.val('<a href="' + storeUrl + '"><img src="' + imgSrc + '</a>');
        bbcode.val('[url=' + storeUrl + '][img]' + imgSrc + '[/img][/url]');
        mdown.val('[![Badge](' + imgSrc + ')](' + storeUrl + ')');

        ga('send', 'event', 'badge', 'loaded', packageId);

        $('meta[name="twitter:image"]').attr('content', imgSrc);
        $('meta[property="og:image"]').attr('content', imgSrc);

        resetUi();
    }).on('error', function () {
        const packageId = packageIdInput.val();
        imgSrc = null;
        ga('send', 'event', 'badge', 'error', packageId);
        showError('Aw, Snap! Check the package name and try again');
    });

    badgeCarousel.slick({
        infinite: true,
        lazyLoad: 'progressive',
        fade: true,
        autoplay: true,
        arrows: false,
        draggable: false,
        swipe: false,
        touchMove: false,
        accessibility: false,
        onAfterChange: function () {
            if(!imgSrc) {
                const curImgSrc = badgePath + '?id=' + topApps[badgeCarousel.slickCurrentSlide()]['id'];
                $('meta[name="twitter:image"]').attr('content', curImgSrc);
                $('meta[property="og:image"]').attr('content', curImgSrc);
            }
            $('link[rel="shortcut icon"]').attr('href', topApps[badgeCarousel.slickCurrentSlide()]['image']);
        },
        autoplaySpeed: 7000
    });

    const afterCopy = function () {
        showMessage('Code copied to clipboard, paste, paste, paste!', false);
    };

    const clipHtml = new ZeroClipboard($('#copy-html'));
    clipHtml.on("beforecopy", function () {
        ga('send', 'event', 'code', 'copy', 'html');
        this.setText(html.val());
    });
    clipHtml.on("aftercopy", afterCopy);

    const clipBB = new ZeroClipboard($('#copy-bbcode'));
    clipBB.on("beforecopy", function () {
        ga('send', 'event', 'code', 'copy', 'bbcode');
        this.setText(bbcode.val());
    });
    clipBB.on("aftercopy", afterCopy);

    const clipMD = new ZeroClipboard($('#copy-mdown'));
    clipMD.on("beforecopy", function () {
        ga('send', 'event', 'code', 'copy', 'mdown');
        this.setText(mdown.val());
    });
    clipMD.on("aftercopy", afterCopy);

    ZeroClipboard.on("error", function() {
        ga('send', 'event', 'code', 'copy', 'error');
        code.find('.input-group').removeClass();
        code.find('.input-group-btn').remove();
    });
});

var resetUi = function () {
        packageIdInput.attr('disabled', false);
        buildButton.stop()
    }
    , showError = function (error) {
        resetUi();
        showMessage(error, true)
    }
    , showMessage = function (message, isError) {
        ga('send', 'event', 'message', 'show', message, isError ? 1 : 0);
        $.bootstrapGrowl(message, {
            ele: '#container',
            type: isError ? 'danger' : 'success',
            align: 'center',
            width: 'auto',
            delay: 4000,
            allow_dismiss: false,
            stackup_spacing: 10
        });
    }
    , fetchBadge = function (packageId) {
        packageIdInput.attr('disabled', true);
        buildButton.start();
        code.fadeOut(250);
        img.fadeOut(250, function () {
            img.attr('src', badgePath + '?id=' + packageId);
        });
    };