/**
 * Fusion Post API
 * @param form_id
 * @param token
 * @param rights
 * @param php_hook
 * @constructor
 */
let FusionPost = function (form_id, token, rights, php_hook) {

    let requesturl = SITE_PATH + 'administration/includes/acp-post.php?token=' + token + '&rights=' + rights;
    let form = document.getElementById(form_id);
    let button = form.querySelectorAll('button[type="submit"]')[0];
    let buttonText = button.innerHTML;
    let formData = new FormData(form);
    //let formData = new FormData(form);
    //formData.append('action_hook', php_hook);

    // Display the key/value pairs. A loop.
    let error_message = '';
    let input_errors = [];
    let input_names = [];
    let input_values = [];

    /**
     * Submit for sanitization
     * @returns {Promise<unknown>}
     */
    this.submit = function () {
        return new Promise(function (resolve, reject) {
            //formData = new FormData(form);
            formData.append('action_hook', php_hook);

            // honeypot is not implemented yet.
            let formlength = -3; // omit form_id and form_token and form_honeypot.
            for (let formValues of formData.entries()) {
                formlength++;
            }

            for (let formValues of formData.entries()) {
                /** not form_id and fusion_token */
                if (formValues[0] !== 'form_id' && formValues[0] !== 'fusion_token') {
                    let _form_id =  formData.get('form_id');
                    let _form_token = formData.get('fusion_token');
                    _sanitizer(_form_id, _form_token, formValues[0], formValues[1])
                        .then(function (posts) {
                            const {responseText} = posts;
                            //console.log(responseText);
                            return JSON.parse(responseText);
                        }).then(function (jsonResponse) {
                            // console.log(jsonResponse);

                        /** use indexer access for all response array */
                        input_names.push(jsonResponse["input_name"]);
                        input_values.push(jsonResponse["input_value"]);

                        /** perform live error validation */
                        if (jsonResponse["error"]) {
                            /** add input errors */
                            input_errors.push(jsonResponse["input_name"]);

                            error_message = jsonResponse["error_message"];
                            if (!error_message) {
                                // need to localize this text here.
                                error_message = 'Please fill in this field.';
                            }

                            /** Append error tags */
                            let input_name = form.querySelectorAll('input[name="' + formValues[0] + '"]')[0];
                            if (input_name) {
                                let error_tag = document.createElement('div');
                                error_tag.innerHTML = error_message;
                                error_tag.classList.add('invalid-feedback');
                                input_name.classList.add('is-invalid');
                                // add label
                                if (!input_name.nextSibling) {
                                    input_name.parentNode.appendChild(error_tag);
                                    //input_name.parentNode.insertBefore(error_tag, input_name.nextSibling);
                                }
                                input_errors.length = 0;
                            }

                            reject({
                                error: true,
                                status: 'failed',
                                statusText: 'There were errors in the form submit'
                            });
                            //let input_error = input_name.innerHTML = '<span class="input-error spacer-xs">'+error_message+'</span>';
                            //console.log(input_error);
                            // do the action for error input
                        }
                        /** To send when all fields has finished sanitizing */
                        if (!formlength && !input_errors.length) {
                            resolve({
                                input: input_names,
                                values: input_values,
                                errors: input_errors
                            });
                            //send_request(input_names, input_values, input_errors);
                        }
                        formlength--;
                    });
                }
            }

        });
    };

    /**
     * Fetch php hook execution results
     * @returns {Promise<unknown>}
     */
    this.return = function() {
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
    };

    /**
     * Helper for form interface on successful request
     */
    this.bs4Success = function() {
        // clean up all .invalid-feedback divs
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        // remove all the is-invalid in form-control class.
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        // clear the form fields except for hidden type
        form.querySelectorAll('input, select').forEach(function(el){
            if (el.name !== "form_id" && el.name !=="fusion_token") {
                el.value = "";
            }
        });
        // append a new token to the form

        // reset the button back to original text
        button.innerHTML = buttonText;
    };

    /**
     * Private function
     * Sanitizer
     * @param form_id
     * @param fusion_token
     * @param input_name
     * @param input_value
     * @param default_value
     * @param input_multilang
     * @returns {Promise<unknown>}
     * @private
     */
    let _sanitizer = function (form_id, fusion_token, input_name, input_value, default_value = '', input_multilang = false) {
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
    };

};
