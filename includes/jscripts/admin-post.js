let adminpost = {
    request: function (form_id, token, rights, action_hook) {
        let requesturl = SITE_PATH + 'administration/includes/acp-post.php?token=' + token + '&rights=' + rights;
        // cannot do this, because this will not be valid for dynamic generated content.
        let form = document.getElementById(form_id);
        $(document).on('submit', 'form#'+form_id, function (e) {
            e.preventDefault();
            let formData = new FormData(form);
            formData.append('action_hook', action_hook);
            // Display the values
            // for (var value of formData.values()) {
            //     console.log(value);
            // }
            let xhr = new XMLHttpRequest();
            /** async: set to true to not interrupt other requests */
            xhr.open('POST', requesturl, true);

            xhr.onprogress = function() {
                console.log('READYSTATE: ', xhr.readyState);
            };

            /** 0 unsent 1opened 2 headers_received 3 loading 4 done */
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 400) {
                    let ourData = xhr.responseText;
                    console.log(ourData);
                } else if (xhr.status === 404) {
                    console.log('Not working.')
                }
            };

            xhr.onerror = function() {
                // popper.js
                console.log('Connection error');
            };

            xhr.send(formData);
        });
    }
};


