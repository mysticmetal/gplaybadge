/**
 * Created by massimilianocannarozzo on 21/06/14.
 */

const img = $('#badgeImg')
    , code = $('#badgeCode')
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
                showError('Please change package id');
            }
        } else {
            showError('Please enter a package id');
        }

        packageIdInput.focus(function () {
            resetUi();
        });
    });

    img.on('load',function () {
        imgSrc = img.attr('src');
        const packageId = packageIdInput.val()
            , imgUrl = window.location.origin + imgSrc
            , storeUrl = 'https://play.google.com/store/apps/details?id=' + packageId;

        img.fadeIn(1000);
        code.fadeIn(1000);
        html.val('<a href="' + storeUrl + '"><img src="' + imgUrl + '</a>');
        bbcode.val('[url=' + storeUrl + '][img]' + imgUrl + '[/img][/url]');
        mdown.val('[![Badge](' + window.location.origin + imgUrl + ')](' + storeUrl + ')');
        resetUi();
    }).on('error', function () {
        imgSrc = null;
        showError('Error generating badge, please check the package name and try again');
    });

    $('.slick').slick({
        infinite: true,
        lazyLoad: 'ondemand',
        fade: true,
        autoplay: true,
        autoplaySpeed: 7000
    });

    const afterCopy = function () {
        showMessage('Code copied to clipboard', false);
    };

    const clipHtml = new ZeroClipboard($('#copy-html'));
    clipHtml.on("beforecopy", function () {
        this.setText(html.val());
    });
    clipHtml.on("aftercopy", afterCopy);

    const clipBB = new ZeroClipboard($('#copy-bbcode'));
    clipBB.on("beforecopy", function () {
        this.setText(bbcode.val());
    });
    clipBB.on("aftercopy", afterCopy);

    const clipMD = new ZeroClipboard($('#copy-mdown'));
    clipMD.on("beforecopy", function () {
        this.setText(mdown.val());
    });
    clipMD.on("aftercopy", afterCopy);

    ZeroClipboard.on("error", function() {
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
        $.bootstrapGrowl(message, {
            ele: '#container', // which element to append to
            type: isError ? 'danger' : 'success', // (null, 'info', 'danger', 'success')
            align: 'center', // ('left', 'right', or 'center')
            width: 'auto', // (integer, or 'auto')
            delay: 4000, // Time while the message will be displayed. It's not equivalent to the *demo* timeOut!
            allow_dismiss: false, // If true then will display a cross to close the popup.
            stackup_spacing: 10 // spacing between consecutively stacked growls.
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