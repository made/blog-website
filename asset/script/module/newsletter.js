/*
 * Made Blog
 * Copyright (c) 2019-2020 Made
 *
 * This program  is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

Module('App.Newsletter', (function () {

    /**
     * Handling the submission of newsletter email - Step 1 of the multi level form.
     * @param event
     */
    function handleFormSubmitEmail(event) {
        event.preventDefault();
        let formEmail = document.getElementById('newsletter_form_email');
        let button = formEmail.querySelector('#newsletter_email_register');
        let email = formEmail.querySelector('#newsletter_email_email');
        button.disabled = true;

        if (email.value) {
            sendPostRequest('/newsletter_register', formEmail, function (element, response) {
                element.outerHTML = response;

                // This is necessary because the page is not reloaded (XHR)
                // If the below function would not be called, then no event listener would be added to the newly fetched form.
                registerEvent();
            });
        } else {
            button.disabled = false;
        }
    }

    /**
     * Handling the submission of newsletter email - Step 2 of the multi level form.
     * @param event
     */
    function handleFormSubmitToken(event) {
        event.preventDefault();
        let formToken = document.getElementById('newsletter_form_token');
        let button = formToken.querySelector('#newsletter_token_confirm');
        let email = formToken.querySelector('#newsletter_token_token');
        button.disabled = true;

        if (email.value) {
            sendPostRequest('/newsletter_register_token', formToken, function (element, response) {
                element.outerHTML = response;

                // This is necessary because the page is not reloaded (XHR)
                // If the below function would not be called, then no event listener would be added to the newly fetched form.
                registerEvent();
            })
        } else {
            button.disabled = false;
        }

    }

    /**
     * Sends an ajax request to the specified url with given body.
     * Also then replaces the innerHtml of the form element to the response.
     * @param url the url to post to
     * @param formElement HTML element which contains the complete form.
     * @param __callback Callback Function to call after the response is here
     *
     * @link https://codepen.io/mrrocks/pen/EiplA Loader is from here.
     * @link https://stackoverflow.com/questions/3038901/how-to-get-the-response-of-xmlhttprequest
     * @link https://stackoverflow.com/a/42344858 Without this Symfony would not recognize this as an ajax request
     * @link https://developer.mozilla.org/en-US/docs/Web/API/FormData/Using_FormData_Objects
     */
    function sendPostRequest(url, formElement, __callback) {

        let request = new XMLHttpRequest();
        request.onreadystatechange = function () {
            if (XMLHttpRequest.DONE === request.readyState) {
                __callback(formElement, request.responseText);
            }
        };
        request.open("POST", url);
        request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        request.send(new FormData(formElement));

        // Loader
        formElement.innerHTML = '<div class="loader"><svg class="spinner" width="65px" height="65px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">\n' +
            '   <circle class="path" fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30"></circle>\n' +
            '</svg></div>' + formElement.innerHTML

    }

    function registerEvent() {
        // This needs to be placed here, since the page is not reloaded, we need to check everytime if the below elements exists.
        let formEmail = document.getElementById('newsletter_form_email');
        let formToken = document.getElementById('newsletter_form_token');

        if (formEmail) {
            formEmail.addEventListener('submit', handleFormSubmitEmail);
        }
        if (formToken) {
            formToken.addEventListener('submit', handleFormSubmitToken);
        }
    }

    function initialize() {
        registerEvent();
    }

    return {
        initialize: initialize,
    }
}));
