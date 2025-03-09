
function updateContentHeight() {    
    document.documentElement.style.setProperty('--content-height', window.innerHeight + 'px');
}

$(document).ready(function () {
    updateContentHeight(); // Set on load
    $(window).on("resize", updateContentHeight);
});