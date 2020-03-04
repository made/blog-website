Module('App.Navigation', (function () {
    let navbar = document.getElementById('main_navigation');

    function handleScroll(event) {
        console.log(event);
    }

    function registerEvent() {
        navbar.addEventListener('scroll', handleScroll)
    }

    function initialize() {
        registerEvent();
    }

    return {
        initialize: initialize,
    }
}));

// ToDo: working - but still neds to be cleaned :)
$(document).ready(function () {
    $(window).scroll(function () {
        $("#main_navigation").toggleClass("navbar-shrink", $(this).scrollTop() > 75)
    });
});
