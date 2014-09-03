/**
 * Created by massimilianocannarozzo on 21/06/14.
 */

$(function () {
    const img = $("#badgeImg");

    $('form').submit(function (event) {
        const createBadgeFormGroup = $('#createBadgeFormGroup')
            , buildButton = $('#buildButton')
            , packageIdInput = $('#packageIdInput')
            , packageId = packageIdInput.val();

        event.preventDefault();

        if (packageId) {
            if (img.attr('src') === undefined || img.attr('src').indexOf(packageId) < 0) {
                resetUi();
                fetchBadge(packageId);
                packageIdInput.attr('disabled', true);
                buildButton.attr('disabled', true);
            } else {
                showError('Please change package id');
                createBadgeFormGroup.addClass('has-error');
            }
        } else {
            showError('Please enter a package id');
            createBadgeFormGroup.addClass('has-error');
        }

        packageIdInput.focus(function () {
            resetUi();
        });
    });

    img.on('load', function () {
            img.fadeIn(1000);
            resetUi();
        })
        .on('error', function () {
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
        $("#badgeImg")
            .fadeOut(250)
            .attr('src', badgePath + '?id=' + packageId);
    };