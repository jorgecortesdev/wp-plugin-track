jQuery(document).ready(function($) {
    var posts = [];

    $('a.wpptrack').each(function() {
        var post_id = $(this).data('post-id');
        var section_id = $(this).data('section-id');
        posts.push([post_id, section_id]);
        $(this).bind("click", function(e) {
            var data = {
                'action': 'process_click',
                'post': [post_id, section_id]
            };
            jQuery.post(wpp_plugin_track_ajax_object.ajax_url, data, function(response) {
                // alert('Got this from the server: ' + response.data);
            });
        });
    });

    var data = {
        'action': 'process_impression',
        'posts': posts
    };

    jQuery.post(wpp_plugin_track_ajax_object.ajax_url, data, function(response) {
        // alert('Got this from the server: ' + response.data);
    });
});