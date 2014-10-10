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
                packageIdInput.attr('disabled', true);
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
        const packageId = packageIdInput.val()
            , imgUrl = window.location.origin + img.attr('src')
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

});

var resetUi = function () {
        $('#packageIdInput').attr('disabled', false);
        buildButton.stop()
    }
    , showError = function (error) {
        resetUi();
        $.bootstrapGrowl(error, {
            ele: '#container', // which element to append to
            type: 'danger', // (null, 'info', 'danger', 'success')
            align: 'center', // ('left', 'right', or 'center')
            width: 'auto', // (integer, or 'auto')
            delay: 4000, // Time while the message will be displayed. It's not equivalent to the *demo* timeOut!
            allow_dismiss: false, // If true then will display a cross to close the popup.
            stackup_spacing: 10 // spacing between consecutively stacked growls.
        });
    }
    , fetchBadge = function (packageId) {
        $('#packageIdInput').attr('disabled', true);
        buildButton.start();
        code.fadeOut(250);
        img.fadeOut(250, function () {
            img.attr('src', badgePath + '?id=' + packageId);
        });
    };