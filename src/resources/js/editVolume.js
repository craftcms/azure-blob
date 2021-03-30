$(document).ready(function() {

    var $connectionString = $('.azure-connection-string'),
        $containerSelect = $('.azure-container-select > select'),
        $refreshContainersButton = $('.azure-refresh-containers'),
        $azureRefreshContainersSpinner = $refreshContainersButton.parent().next().children(),
        $manualContainer = $('.azure-manualContainer'),
        $volumeUrl = $('.volume-url'),
        $hasUrls = $('input[name=hasUrls]'),
        refreshingAzureContainers = false;

    $refreshContainersButton.click(function() {
        if ($refreshContainersButton.hasClass('disabled')) {
            return;
        }

        $refreshContainersButton.addClass('disabled');
        $azureRefreshContainersSpinner.removeClass('hidden');

        var data = {
            connectionString: $connectionString.val(),
        };

        Craft.postActionRequest('azure-blob', data, function(response, textStatus) {
            $refreshContainersButton.removeClass('disabled');
            $azureRefreshContainersSpinner.addClass('hidden');

            if (textStatus == 'success') {
                if (response.error) {
                    alert(response.error);
                } else if (response.length > 0) {
                    var currentContainer = $containerSelect.val(),
                        currentContainerStillExists = false;

                    refreshingAzureContainers = true;

                    $containerSelect.prop('readonly', false).empty();

                    for (var i = 0; i < response.length; i++) {
                        if (response[i].container == currentContainer) {
                            currentContainerStillExists = true;
                        }

                        $containerSelect.append('<option value="' + response[i].container + '" data-url-prefix="' + response[i].urlPrefix + '">' + response[i].container + '</option>');
                    }

                    if (currentContainerStillExists) {
                        $containerSelect.val(currentContainer);
                    }

                    refreshingAzureContainers = false;

                    if (!currentContainerStillExists) {
                        $containerSelect.trigger('change');
                    }
                }
            }
        });
    });

    $containerSelect.change(function() {
        if (refreshingAzureContainers) {
            return;
        }

        var $selectedOption = $containerSelect.children('option:selected');

        $('.volume-url').val($selectedOption.data('url-prefix'));
    });

    var azureChangeExpiryValue = function() {
        var parent = $(this).parents('.field'),
            amount = parent.find('.azure-expires-amount').val(),
            period = parent.find('.azure-expires-period select').val();

        var combinedValue = (parseInt(amount, 10) === 0 || period.length === 0) ? '' : amount + ' ' + period;

        parent.find('[type=hidden]').val(combinedValue);
    };

    $('.azure-expires-amount').keyup(azureChangeExpiryValue).change(azureChangeExpiryValue);
    $('.azure-expires-period select').change(azureChangeExpiryValue);


    var maybeUpdateUrl = function() {
        const str = $connectionString.val();
        const accountName = str.match(/AccountName=(.*?)(;|$)/)[1];
        const suffix = str.match(/EndpointSuffix=(.*?)(;|$)/)[1];
        const protocol = str.match(/DefaultEndpointsProtocol=(.*?)(;|$)/)[1];

        if ($hasUrls.val() && $manualContainer.val().length) {
            $volumeUrl.val(protocol + '://' + accountName + '.' + suffix + '/' + $manualContainer.val() + '/');
        }
    };

    $manualContainer.keyup(maybeUpdateUrl);
});
