var gallery;
var minPageIndex, maxPageIndex;
var loadingPage = false;
var jumpDistance = 10;

$(document).ready(function() {
    $(".pswp").keydown(function(e) {
        var currentIndex = gallery.getCurrentIndex();
        switch (e.which) {
            case 33:
                // Page up.
                if (gallery) {
                    gallery.goTo(Math.max(0, currentIndex - jumpDistance));
                }
                break;
            case 34:
                // Page down.
                if (gallery) {
                    gallery.goTo(Math.min(gallery.options.getNumItemsFn() - 1, currentIndex + jumpDistance));
                }
                break;
            case 36:
                // Home key.
                if (gallery) {
                    gallery.goTo(0);
                }
                break;
        }
    });
});

function OpenSlideshow() {
    gallery = false;
    minPageIndex = startPage;
    maxPageIndex = startPage;
    LoadMoreSlides(startPage, 1, 0);
    return false;
}

function InitSlideshow(items) {
    var pswpElement = document.querySelectorAll('.pswp')[0];
    var options = {
        index: 0,
        loop: true,
        preload: [1, 3],
        closeOnScroll: false,
        closeOnVerticalDrag: false,
        clickToCloseNonZoomable: false,
        shareButtons: [
            {id:'go-to-post', label:'Go to Post', url:'{{raw_image_url}}'},
            {id:'download', label:'Download image', url:'{{raw_image_url}}'},
        ],
        getImageURLForShare: function( shareButtonData ) {
            if (shareButtonData.id == 'go-to-post') {
                return gallery.currItem.postURL;
            } else if (shareButtonData.id == 'download') {
                return gallery.currItem.src;
            } else {
                return gallery.currItem.src;
            }
        },
    };
    gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
    gallery.listen("afterChange", function() {
        var currentIndex = gallery.getCurrentIndex();
        if (currentIndex < jumpDistance && minPageIndex > 1) {
            LoadMoreSlides(minPageIndex - 1, -1, 0);
        } else if (currentIndex >= gallery.options.getNumItemsFn() - jumpDistance) {
            LoadMoreSlides(maxPageIndex + 1, 1, 0);
        }
    });
    gallery.init();
}

function LoadMoreSlides(page, increment, numEmptyPages) {
    if (page <= 0) return;
    if (loadingPage) return;
    if (numEmptyPages >= 3) return;  // Skip up to 3 pages of unviewable posts.
    minPageIndex = Math.min(minPageIndex, page);
    maxPageIndex = Math.max(maxPageIndex, page);
    loadingPage = true;
    $.ajax("/gallery/post/slideshow/fetch/", {
        data: {
            page: page,
            search: searchString
        },
        method: "GET",
        success: function(response) {
            loadingPage = false;
            if (response.length > 0) {
                if (!gallery) {
                    InitSlideshow(response);
                } else {
                    if (increment == -1) {
                        var index = gallery.getCurrentIndex() + response.length;
                        for (i = response.length - 1; i >= 0; i--) {
                            gallery.items.unshift(response[i]);
                        }
                        gallery.goTo(index);
                    } else if (increment == 1) {
                        for (i = 0; i < response.length; i++) {
                            gallery.items.push(response[i]);
                        }
                    }
                    gallery.invalidateCurrItems();
                    gallery.updateSize(true);
                    gallery.ui.update();
                }
            } else {
                LoadMoreSlides(page + increment, increment, numEmptyPages + 1);
            }
        },
        error: function() {
            loadingPage = false;
        }
    });
}
