/**
 *
 * @type {{request: (function(*=, *, *, *=): Promise<unknown>)}}
 */
let adminpost = {

    // do another model here.














    /**
     * Makes a XHR request
     * @param form_id
     * @param token
     * @param rights
     * @param php_hook
     * @returns {Promise<unknown>}
     */
    request: function (form_id, token, rights, php_hook) {

        let requesturl = SITE_PATH + 'administration/includes/acp-post.php?token=' + token + '&rights=' + rights;
        let form = document.getElementById(form_id);
        let button = form.querySelectorAll('button[type="submit"]')[0];
        let buttonText = button.innerHTML;

        let formData = new FormData(form);
        formData.append('action_hook', php_hook);

        // Display the key/value pairs. A loop.
        let error_message = '';
        let input_errors = [];
        let input_names = [];
        let input_values = [];
        // let errors = [];

        let formlength = -3; // each form will be paired with form_id and form_token to be omitted from count and a honeypot.
        for (let formValues of formData.entries()) {
            formlength ++;
        }

        for (let formValues of formData.entries()) {
            /** not form_id and fusion_token */
            if (formValues[0] !== 'form_id' && formValues[0] !== 'fusion_token') {
                adminpost.sanitizer(formData.get('form_id'), formData.get('fusion_token'), formValues[0], formValues[1])
                    .then(function (posts) {
                        return JSON.parse(posts.responseText);
                    }).then(function (jsonResponse) {

                    /** use indexer access for all response array */
                    input_names.push(jsonResponse["input_name"]);
                    input_values.push(jsonResponse["input_value"]);
                    //input_values[jsonResponse["input_name"]] = jsonResponse["input_value"];

                    /** perform live error validation */
                    if (jsonResponse.error) {
                        /** add input errors */
                        input_errors.push(jsonResponse["input_name"]);

                        if (!jsonResponse["error_message"]) {
                            // need to localize this text here.
                            error_message = 'Please fill in this field.';
                        }
                        /** Append error tags */
                        let error_tag = document.createElement('div');
                        error_tag.innerHTML = error_message;
                        error_tag.classList.add('invalid-feedback');
                        let input_name = form.querySelectorAll('input[name="' + formValues[0] + '"]')[0];
                        input_name.classList.add('is-invalid');
                        if (input_name.nextSibling) {
                            input_name.parentNode.insertBefore(error_tag, input_name.nextSibling);
                        } else {
                            input_name.parentNode.appendChild(error_tag);
                        }
                        //let input_error = input_name.innerHTML = '<span class="input-error spacer-xs">'+error_message+'</span>';
                        //console.log(input_error);
                        // do the action for error input
                    }

                    /** To send when all fields has finished sanitizing */
                    if (!formlength && !input_errors.length) {
                        send_request(input_names, input_values, input_errors);
                    }
                    formlength--;
                });
            }
        }

        // maybe we approach it wrongly.
        // we need to first do sanitize, return all sanitized values using php..

        /**
         * When finish...
         * @param input_names
         * @param input_values
         * @param input_errors
         */
        function send_request(input_names, input_values, input_errors) {
            // check if there are any errors, then abort.
            if (input_errors.length === 0) {
                // reappend the formdata with our new form data
                input_names.forEach(function (input_name, index) {
                    let value = input_values[index];
                    formData.set(input_name, value);
                });
            }
        }

        /** Problems with this approach is that we cannot scrub the data from sanitizer ! */
        // Display the values
        let request = new XMLHttpRequest();
        // Return it as a Promise
        return new Promise(function (resolve, reject) {
            request.onprogress = function () {
                // use the closest button to bind loader.
                button.innerHTML = '<img class="loader" style="width:15px; height: 15px;" src="' + IMAGES + 'load.svg" alt="loading..."/> Submitting..';
                //console.log('READYSTATE: ', request.readyState);
            };
            // Setup our listener to process compeleted requests
            request.onreadystatechange = function () {
                // Only run if the request is complete
                if (request.readyState !== 4) return;
                // Process the response
                if (request.status >= 200 && request.status < 300) {
                    // If successful
                    resolve(request);
                } else {
                    // If failed
                    reject({
                        status: request.status,
                        statusText: request.statusText
                    });
                }
            };
            // Setup our HTTP request
            request.open('POST', requesturl, true);
            // Send the request
            request.send(formData);
        });
    },

    /**
     *
     * @param form_id
     * @param fusion_token
     * @param input_name
     * @param input_value
     * @param default_value
     * @param input_multilang
     * @returns {Promise<unknown>}
     */
    sanitizer: function (form_id, fusion_token, input_name, input_value, default_value = '', input_multilang = false) {
        let requesturl = SITE_PATH + 'administration/includes/sanitize.php';
        // Display the values
        // for (var value of formData.values()) {
        //     console.log(value);
        // }
        let request = new XMLHttpRequest();
        // Return it as a Promise
        return new Promise(function (resolve, reject) {

            request.onprogress = function () {
                // use the closest button to bind loader.
                //console.log('READYSTATE: ', request.readyState);
            };

            // Setup our listener to process compeleted requests
            request.onreadystatechange = function () {

                // Only run if the request is complete
                if (request.readyState !== 4) return;

                // Process the response
                if (request.status >= 200 && request.status < 300) {
                    // If successful
                    resolve(request);
                } else {
                    // If failed
                    reject({
                        status: request.status,
                        statusText: request.statusText
                    });
                }

            };
            // Setup our HTTP request
            request.open('POST', requesturl, true);
            // Send the request
            let formData = new FormData();
            formData.append('form_id', form_id);
            formData.append('fusion_token', fusion_token);
            formData.append('input_name', input_name);
            formData.append('input_value', input_value);
            formData.append('default_value', default_value);
            if (input_multilang) {
                formData.append('input_multilang', '1');
            }
            request.send(formData);
        });
    },
};


