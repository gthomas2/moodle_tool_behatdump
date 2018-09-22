define(['jquery', 'core/notification'], function($, notification) {
    return {
        /**
         *
         * @param string action
         * @param object data
         * @param string method
         * @param function ? customErrorHandler
         * @returns {deferred|boolean}
         */
        call: function(action, data, method, customErrorHandler) {
            if (!data) {
                data = {};
            }
            data.action = action;

            var notLoggedInError = function() {
                var logInLink = M.cfg.wwwroot + '/login';
                // TODO localise
                var msg = 'It appears that you are not logged in. Please '
                        + '<a target="_blank" href="' + logInLink + '">log in</a> to continue';
                notification.alert('Not logged in',
                    msg, 'OK');
            };

            //var onErrorGeneral = function(jqXHR, textStatus, errorThrownMsg) {
            var onErrorGeneral = function(jqXHR) {

                if (!jqXHR) {
                    jqXHR = {status: 'jqXHR object invalid', responseText: ''};
                }

                window.console.log('error - jqXHR', jqXHR);

                var error, errorcode, stacktrace;

                if (!jqXHR.responseJSON) {
                    error = 'Unknown error';
                    errorcode = 'unknown';
                    stacktrace = 'unknown - possible bad JSON? ' + jqXHR.responseText;
                } else {
                    error = jqXHR.responseJSON.error;
                    errorcode = jqXHR.responseJSON.errorcode;
                    stacktrace = jqXHR.responseJSON.stacktrace;
                }

                if (jqXHR.responseJSON && jqXHR.responseJSON.errorcode && jqXHR.responseJSON.errorcode === 'requireloginerror') {
                    return notLoggedInError();
                }

                var msg = '<div class="ajaxErrors">'
                    + '<div class="ajaxErrorMsg">' + error + '</div>'
                    + '<div class="ajaxErrorStatus">Error status code: ' + jqXHR.status + '</div>'
                    + '<div class="ajaxErrorCode">Error code: ' + errorcode + '</div>'
                    + '<div class="ajaxErrorStackTrace">Stack trace: ' + stacktrace + '</div>'
                    + '</div>';

                // TODO localise Error, OK.
                notification.alert('An error has occurred',
                    msg, 'OK');


            };

            var errorFunction = function(jqXHR) {
                if (typeof(customErrorHandler) === 'function') {
                    if (customErrorHandler(jqXHR)) {
                        return;
                    } else {
                        // If the custom error handler doesn't return true then we go onto call the general error handler.
                        onErrorGeneral(jqXHR);
                    }
                } else {
                    onErrorGeneral(jqXHR);
                }
            };

            return $.ajax({
                url: M.cfg.wwwroot + '/admin/tool/behatdump/rest.php',
                data: data,
                method: method,
                error: errorFunction
            });
        },
        get: function(action, data, error) {
            return this.call(action, data, 'GET', error);
        },
        post: function(action, data, error) {
            return this.call(action, data, 'POST', error);
        },
        put: function(action, data, error) {
            return this.call(action, data, 'PUT', error);
        },
        patch: function(action, data, error) {
            return this.call(action, data, 'PATCH', error);
        },
        delete: function(action, data, error) {
            return this.call(action, data, 'DELETE', error);
        }
    };
});