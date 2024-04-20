jQuery(document).ready(function($) {
    $('.copy-icon').on('click', function(e) {
        e.preventDefault();
        var text = $(this).data('copytext');
        navigator.clipboard.writeText(text).then(function() {
            // alert('Text copied: ' + text);
        }, function(err) {
            console.error('Could not copy text: ', err);
        });
    });

    $('.copy-link').on('click', function(e) {
        e.preventDefault();
        var link = $(this).data('link');
        navigator.clipboard.writeText(link).then(function() {
            // alert('Link copied: ' + link);
        }, function(err) {
            console.error('Could not copy link: ', err);
        });
    });
});
