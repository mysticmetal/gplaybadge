/**
 * Created by massimilianocannarozzo on 21/06/14.
 */

const img = $('#badgeImg')
    , code = $('#badgeCode')
    , buildButton = $('#buildButton')
    , packageIdInput = $('#packageIdInput')
    , html = $('#html')
    , bbcode = $('#bbcode')
    , mdown = $('#mdown')
    , opts = {
        lines: 9, // The number of lines to draw
        length: 20, // The length of each line
        width: 6, // The line thickness
        radius: 26, // The radius of the inner circle
        color: '#2196F3', // #rgb or #rrggbb or array of colors
        trail: 10, // Afterglow percentage
        corners: 0, // Corner roundness (0..1)
        speed: 0.5, // Rounds per second
        hwaccel: true // Whether to use hardware acceleration
    };

$(function () {
    var imgSrc;

    $('form').submit(function (event) {
        const packageId = packageIdInput.val();

        event.preventDefault();

        if (packageId) {
            if (imgSrc == null || img.attr('src').indexOf(packageId) < 0) {
                resetUi();
                fetchBadge(packageId);
                packageIdInput.attr('disabled', true);
                buildButton.attr('disabled', true);
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
            , imgUrl = window.location.origin + imgSrc
            , storeUrl = 'https://play.google.com/store/apps/details?id=' + packageId;

        img.fadeIn(1000);
        code.fadeIn(1000);
        imgSrc = img.attr('src');
        html.val('<a href="' + storeUrl + '"><img src="' + imgUrl + '</a>');
        bbcode.val('[url=' + storeUrl + '][img]' + window.location.origin + imgSrc + '[/img][/url]');
        mdown.val('[![Badge](' + window.location.origin + imgSrc + ')](' + storeUrl + ')');
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
        $('#buildButton').attr('disabled', false);
        $('.spinner').fadeOut(250, function () {
            $('#createBadgeFormGroup').spin(false);
        });
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
        $('#buildButton').attr('disabled', true);
        $('#createBadgeFormGroup').spin(opts);
        code.fadeOut(250);
        img.fadeOut(250, function () {
            img.attr('src', badgePath + '?id=' + packageId);
        });
    };