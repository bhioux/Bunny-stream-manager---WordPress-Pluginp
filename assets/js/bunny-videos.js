jQuery(document).ready(function($) {
    // View Toggle
    $('.view-toggle').click(function() {
        $('.view-toggle').removeClass('active');
        $(this).addClass('active');
        
        const view = $(this).data('view');
        $('.bunny-videos').removeClass('grid list').addClass(view);
    });

    // Sorting
    $('.sort-videos').change(function() {
        const videos = $('.video-item').get();
        const sortBy = $(this).val();

        videos.sort(function(a, b) {
            if (sortBy === 'title') {
                return $(a).find('.title').text()
                    .localeCompare($(b).find('.title').text());
            } else {
                const dateA = new Date($(a).find('.date').text());
                const dateB = new Date($(b).find('.date').text());
                return sortBy === 'newest' ? dateB - dateA : dateA - dateB;
            }
        });

        $('.bunny-videos').append(videos);
    });

    // Video Click Handler
    $('.video-item').click(function() {
        const videoId = $(this).data('id');
        // Add your video player implementation here
        console.log('Play video:', videoId);
    });
});
