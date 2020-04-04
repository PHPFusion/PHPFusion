/**
 *
 * @type {{request: (function(*=, *, *, *=): Promise<unknown>)}}
 */
let adminpost = {

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
        let formData = new FormData(form);
        formData.append('action_hook', php_hook);

        // Display the key/value pairs
        for (let formValues of formData.entries()) {
            /** not form_id and fusion_token */
            if (formValues[0] !== 'form_id' && formValues[0] !=='fusion_token') {
                let sanitize = adminpost.sanitize(formData.get('form_id'), formData.get('fusion_token'), formValues[0], formValues[1])
                    .then(function(posts) {
                        console.log(posts);
                    });

            }
            //console.log(formValues[0]+ ', ' + formValues[1]);
        }
        // let post;
        // $('form#customlinksFrm input, form#customlinksFrm select').each(
        //     function(index){
        //         let input = $(this);
        //         let sanitize = adminpost.sanitize(input.attr('name'), '');
        //
        //         //alert('Type: ' + input.attr('type') + 'Name: ' + input.attr('name') + 'Value: ' + input.val());
        //     }
        // );


        // Display the values
        // for (var value of formData.values()) {
        // //     console.log(value);
        // }
        let request = new XMLHttpRequest();

        // Return it as a Promise
        return new Promise(function (resolve, reject) {

            request.onprogress = function () {
                // use the closest button to bind loader.
                console.log('READYSTATE: ', request.readyState);
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
    sanitize: function (form_id, fusion_token, input_name, input_value, default_value = '', input_multilang = false) {
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
                console.log('READYSTATE: ', request.readyState);
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


