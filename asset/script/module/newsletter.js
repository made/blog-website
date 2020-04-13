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
        let form = document.getElementById('form_newsletter_container').getElementsByTagName('form')[0];
        let email = form.querySelector('#newsletter_email_email');

        if (email.value) {
            sendPostRequest('/newsletter/register', form, function (element, response) {
                element.outerHTML = response;

                // This is necessary because the page is not reloaded (XHR)
                // If the below function would not be called, then no event listener would be added to the newly fetched form.
                registerEvent();
            });
        }
    }

    /**
     * Handling the submission of newsletter email - Step 2 of the multi level form.
     * @param event
     */
    function handleFormSubmitToken(event) {
        event.preventDefault();
        let form = document.getElementById('form_newsletter_container').getElementsByTagName('form')[0];
        let email = form.querySelector('#newsletter_token_token');

        if (email.value) {
            sendPostRequest('/newsletter/activate/code', form, function (element, response) {
                element.outerHTML = response;

                // This is necessary because the page is not reloaded (XHR)
                // If the below function would not be called, then no event listener would be added to the newly fetched form.
                registerEvent();
            })
        }

    }

    /**
     * @param event
     */
    function handleChangeEmail(event) {
        let form = document.getElementById('form_newsletter_container').getElementsByTagName('form')[0];

        sendPostRequest('/newsletter/register', form, function (element, response) {
            element.outerHTML = response;

            registerEvent();
        });
    }

    /**
     * @param event
     */
    function handleResendEmail(event) {
        let form = document.getElementById('form_newsletter_container').getElementsByTagName('form')[0];

        sendPostRequest('/newsletter/activate/resend', form, function (element, response) {
            element.outerHTML = response + element.outerHTML;
            document.getElementById('loader').remove();

            registerEvent();
        });
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
        formElement.innerHTML = '<div class="loader" id ="loader"><svg class="spinner" width="50px" height="50px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">\n' +
            '   <circle class="path" fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30"></circle>\n' +
            '</svg><span class="pl-4">Loading chunks...</span></div>' + formElement.innerHTML
    }

    /**
     * Check which form is active to register the correct event
     */
    function registerEvent() {
        // This needs to be placed here, since the page is not reloaded, we need to check everytime if the below elements exists.

        // This adds the event listener to the first step, when the user enters his email address
        let formEmail = document.getElementById('newsletter_form_email');
        if (formEmail) {
            formEmail.addEventListener('submit', handleFormSubmitEmail);
        }

        // This adds the event listener to the second step, when the user can activate his token
        let formToken = document.getElementById('newsletter_form_token');
        if (formToken) {
            formToken.addEventListener('submit', handleFormSubmitToken);
        }

        // During the registration process in the second step the user can change his email address
        let changeEmail = document.getElementById('change_email');
        if (changeEmail) {
            changeEmail.addEventListener('click', handleChangeEmail);
        }

        // During the registration process in the second step the user can resend his email address
        let resendEmail = document.getElementById('resend_email');
        if (resendEmail) {
            resendEmail.addEventListener('click', handleResendEmail);
        }
    }

    function initialize() {
        registerEvent();
    }

    return {
        initialize: initialize,
    }
}));
