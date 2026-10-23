/**
 * News Detail interactive scripts (Share button)
 */
document.addEventListener('DOMContentLoaded', function () {
    var shareBtn = document.querySelector('.news-detail-share-btn');
    if (!shareBtn) return;

    var toast = document.createElement('div');
    toast.className = 'news-share-toast';
    toast.textContent = 'Link copied to clipboard!';
    document.body.appendChild(toast);

    function showToast(msg) {
        if (msg) toast.textContent = msg;
        toast.classList.add('show');
        setTimeout(function () {
            toast.classList.remove('show');
        }, 2500);
    }

    shareBtn.addEventListener('click', function () {
        var pageUrl = window.location.href;
        var pageTitle = document.title;

        if (navigator.share) {
            navigator.share({
                title: pageTitle,
                url: pageUrl
            }).catch(function (err) {
                // Ignore AbortError if user closes dialog
            });
        } else if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(pageUrl).then(function () {
                showToast('Link copied to clipboard!');
            }).catch(function () {
                promptCopy(pageUrl);
            });
        } else {
            promptCopy(pageUrl);
        }
    });

    function promptCopy(url) {
        var tempInput = document.createElement('input');
        tempInput.value = url;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        showToast('Link copied to clipboard!');
    }
});

