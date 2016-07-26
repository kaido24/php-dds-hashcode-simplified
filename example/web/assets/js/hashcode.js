'use strict';

function httpPost(path, params, method) {
    // Set method to post by default if not specified.
    method = method || 'post';

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var $submitForm = $('<form />').attr({
        method: method,
        action: path
    });

    for (var key in params) {
        if (!params.hasOwnProperty(key)) {
            continue;
        }

        var $hiddenField = $('<input />').attr({
            type: 'hidden',
            name: key,
            value: params[key]
        });

        $submitForm.append($hiddenField);
    }

    $(document.body).append($submitForm);
    $submitForm.submit();
}

/*
 * Logic for asynchronous and synchronous requests that need JavaScripts help.
 */
var ee = ee === undefined ? {} : ee;
ee.sk = ee.sk === undefined ? {} : ee.sk;
ee.sk.hashcode = ee.sk.hashcode === undefined ? {} : ee.sk.hashcode;

ee.sk.hashcode = {
    defaultPath: '',
    DownloadContainer: function () {
        var token = $('#download-container')
            .find('input[name=_token]')
            .val();

        httpPost(this.defaultPath, {
            _token: token,
            request_act: 'DOWNLOAD'
        });
    },

    RemoveDataFile: function (datafileId, datafileName) {
        var token = $('#container-data-files')
            .find('input[name=_token]')
            .val();

        httpPost(this.defaultPath, {
            _token: token,
            request_act: 'REMOVE_DATA_FILE',
            datafileId: datafileId,
            datafileName: datafileName
        });
    },

    RemoveSignature: function (signatureId) {
        var token = $('#container-signatures')
            .find('input[name=_token]')
            .val();

        httpPost(this.defaultPath, {
            _token: token,
            request_act: 'REMOVE_SIGNATURE',
            signatureId: signatureId
        });
    },

    StartMobileSign: function () {
        var $errorContainer = $('#mobileSignErrorContainer');

        $errorContainer.hide();
        var phoneNumber = $('#mid_PhoneNumber').val(),
            token = $('#mobileSignModalFooter').find('input[type=hidden][name=_token]').val(),
            idCode = $('#mid_idCode').val();

        if (!phoneNumber) {
            $errorContainer.html('Phone number is mandatory!').show();
        } else if (!idCode) {
            $errorContainer.html('Social security number is mandatory!').show();
        } else {
            $.post(this.defaultPath, {
                _token: token,
                request_act: 'MID_SIGN',
                phoneNo: phoneNumber,
                idCode: idCode,
                subAct: 'START_SIGNING'
            }).done(function (response) {
                    if (response.error_message) {
                        $errorContainer.html('There was an error initiating ' +
                            'MID signing: ' + response.error_message);
                        $errorContainer.show();
                    } else {
                        $('#mobileSignModalHeader').hide();
                        $('#mobileSignModalFooter').hide();
                        var challenge = response.challenge;
                        $('.mobileSignModalContent').html('<table><tr><td style="width: 75%;">' +
                            '<h4>Sending digital signing request to phone is in progress ...</h4>' +
                            '<p style="font-size: 12px;">Make sure control code matches with one in the ' +
                            'phone screen and enter Mobile-ID PIN2. ' +
                            'After you enter signing PIN, a digital signature is created to the document, ' +
                            'which may bind you to legal liabilities. ' +
                            'You must therefore agree to the content of the document. When in doubt, please ' +
                            'go back and check document contents.</p></td>' +
                            '<td style="vertical-align: middle; text-align: center;">' +
                            'Control code:' +
                            '<h2>' + challenge + '</h2>' +
                        '</td></tr></table>');
                        var intervalId = setInterval(function () {
                            $.post('', {
                                _token: token,
                                request_act: 'MID_SIGN',
                                subAct: 'GET_SIGNING_STATUS'
                            }).done(function (statusResponse) {
                                if (statusResponse.is_success === true) {
                                    clearInterval(intervalId);
                                    httpPost('', {
                                        _token: token,
                                        request_act: 'MID_SIGN_COMPLETE'
                                    });
                                } else if (!!statusResponse.error_message) {
                                    clearInterval(intervalId);
                                    httpPost('', {
                                        _token: token,
                                        request_act: 'MID_SIGN_COMPLETE',
                                        error_message: statusResponse.error_message
                                    });
                                }
                            }).fail(function (data) {
                                clearInterval(intervalId);
                                httpPost('', {
                                    _token: token,
                                    request_act: 'MID_SIGN_COMPLETE',
                                    error_message: data.status + '-' + data.statusText
                                });
                            });
                        }, 3000);
                    }
                }).fail(function (data) {
                    $errorContainer.html('There was an error performing AJAX request to initiate ' +
                        'MID signing: ' + data.status + '-' + data.statusText);
                    $errorContainer.show();
                });
        }

    },


    /**
     *
     * ID card signing methods
     * Please read: https://github.com/open-eid/js-token-signing/wiki/ModernAPI
     *
     * There You will have very good overview of API and much more compact example of signing using JavaScript
     *
     * @param reason
     */
    errorHandler: function (reason) {
        var longMessage = 'ID-card siging: ',
            $errorContainer = $('#idSignModalErrorContainer');

        $errorContainer.text('').hide();
        console.log('inside error handler');
        var hwcrypto = window.hwcrypto;
        switch (reason.message) {

            case 'no_backend':
                longMessage += 'Cannot find ID-card browser extensions';
                break;
            case hwcrypto.USER_CANCEL:
                longMessage += 'Signing canceled by user';
                break;
            case hwcrypto.INVALID_ARGUMENT:
                longMessage += 'Invalid argument';
                break;
            case hwcrypto.NO_CERTIFICATES_FOUND:
                longMessage += ' Failed reading ID-card certificates make sure. ' +
                    'ID-card reader or ID-card is inserted correctly';
                break;
            case hwcrypto.NO_IMPLEMENTATION:
                longMessage += ' Please install or update ID-card Utility or install missing browser extension. ' +
                    'More information about on id.installer.ee';
                break;
            case hwcrypto.TECHNICAL_ERROR:
                longMessage += 'Unknown technical error occurred';
                break;
            default: longMessage += 'Unknown error occurred that we can not explain';
        }

        $errorContainer.text(longMessage).show();
        console.log('exiting error handler...');
    },

    /**
     * Handle prepare for signing response
     *
     * @param statusResponse
     * @param language
     * @param certificate
     * @param token
     */
    hashCreateResponseHandler: function (statusResponse, language, certificate, token) {
        var self = this,
            actionToComplete = 'ID_SIGN_COMPLETE';

        if (statusResponse.is_success === true) {
            var signatureDigest = statusResponse.signature_info_digest,
                signatureID = statusResponse.signature_id,
                signatureHashType = statusResponse.signature_hash_type;

            window.hwcrypto
                .sign(certificate, {
                    hex: signatureDigest,
                    type: signatureHashType
                }, {lang: language})
                .then(function (signature) {
                     httpPost('', {
                        _token: token,
                        request_act: actionToComplete,
                        signature_id: signatureID,
                        signature_value: signature.hex
                    });
                }, function (reason) {
                    console.log('error occurred when started signing document');
                    self.errorHandler(reason);
                });
        } else if (!!statusResponse.error_message) {
            httpPost('', {
                _token: token,
                request_act: actionToComplete,
                error_message: statusResponse.error_message
            });
        }
    },

    prepareSigningParameters: function (cert) {
        var idSignCreateHashRequestParameters = {
                request_act: 'ID_SIGN_CREATE_HASH',
                signersCertificateHEX: cert.hex
            },
            prepareSigningParameterKeys = ['Role', 'City', 'Stat', 'PostalCode', 'Country'],
            len = prepareSigningParameterKeys.length;

        for (var i = 0; i < len; i++) {
            var key = prepareSigningParameterKeys[i],
                value = $('#idSign' + prepareSigningParameterKeys[i]).val();

            if (value) {
             idSignCreateHashRequestParameters['signers' + key] = value;
            }
        }

        return idSignCreateHashRequestParameters;
    },

    IDCardSign: function () {
        $('#idSignModalErrorContainer').hide();
        var self = this,
            token = $('#idSignModalFooter').find('input[name=_token]').val(),
            lang = 'eng';

        window.hwcrypto.getCertificate({lang: lang}).then(function (cert) {
            var idSignCreateHashRequestParameters = self.prepareSigningParameters(cert);

            idSignCreateHashRequestParameters._token = token;
            $.post('', idSignCreateHashRequestParameters)
                .done(function (statusResponse) {
                    self.hashCreateResponseHandler(statusResponse, lang, cert, token);
                })
                .fail(function (data) {
                    httpPost('', {
                        _token: token,
                        request_act: 'ID_SIGN_COMPLETE',
                        error_message: data.status + '-' + data.statusText
                    });
                });
        }, function (reason) {
            console.log('error occured when getting certificate');
            self.errorHandler(reason);
        });

    }
};
