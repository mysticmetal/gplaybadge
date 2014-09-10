/**
 * Created by massimilianocannarozzo on 21/06/14.
 */

const img = $('#badgeImg')
    , code = $('#badgeCode')
    , createBadgeFormGroup = $('#createBadgeFormGroup')
    , buildButton = $('#buildButton')
    , packageIdInput = $('#packageIdInput')
    , html = $('#html')
    , bbcode = $('#bbcode')
    , mdown = $('#mdown');

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

    img.on('load', function () {
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
    })
});

var resetUi = function () {
        $('#packageIdInput').attr('disabled', false);
        $('#buildButton').attr('disabled', false);
    }
    , showError = function (error) {
        resetUi();
        $('#alertModalBody').text(error);
        $('#alertModal').modal('show')
    }
    , fetchBadge = function (packageId) {
        $('#packageIdInput').attr('disabled', true);
        $('#buildButton').attr('disabled', true);
        code.fadeOut(250);
        img.fadeOut(250, function(){
            img.attr('src', badgePath + '?id=' + packageId);
        });
    };