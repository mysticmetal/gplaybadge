/**
 * Created by massimilianocannarozzo on 21/06/14.
 */

$(function () {
    $('form').submit(function (event) {
        const createBadgeFormGroup = $('#createBadgeFormGroup')
            , buildButton = $('#buildButton')
            , packageIdInput = $('#packageIdInput')
            , packageId = packageIdInput.val();

        event.preventDefault();

        if (packageId) {
            resetUi();
            fetchBadge(packageId);
            packageIdInput.attr('disabled', true);
            buildButton.attr('disabled', true);
        } else {
            showError('Missing package id');
            createBadgeFormGroup.addClass('has-error');
        }

        packageIdInput.focus(function () {
            resetUi();
        });
    });

    const img = $("#badgeImg").on('load', function () {
            img.fadeIn(1000);
            resetUi();
        })
        .on('error', function () {
            showError('cazzo');
        })
});

var resetUi = function () {
        $('#packageIdInput').attr('disabled', false);
        $('#buildButton').attr('disabled', false);
    }
    , showError = function (error) {
        resetUi();
        alert(error);
    }
    , fetchBadge = function (packageId) {
        $('#packageIdInput').attr('disabled', true);
        $('#buildButton').attr('disabled', true);
        $("#badgeImg")
            .fadeOut(250)
            .attr('src', '/badge/?id=' + packageId);
    };