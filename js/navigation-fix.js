
(function() {
    'use strict';

    
    window.addEventListener('pageshow', function(event) {
        
        if (event.persisted) {
            console.log('Page loaded from cache');
        }
    });

    
    if (document.referrer) {
        
        const referrerUrl = new URL(document.referrer, window.location.origin);

        
        if (referrerUrl.pathname.includes('dashboard.php') && referrerUrl.hash) {
            console.log('Referrer from dashboard with hash:', referrerUrl.hash);
        }
    }

    
    if (window.location.pathname.includes('editor-')) {
        
        if (!history.state) {
            history.replaceState({ page: 'editor' }, '', window.location.href);
        }
    }

    
    window.addEventListener('popstate', function(event) {
        
        if (window.location.pathname.includes('editor-')) {
            
            if (document.referrer && !document.referrer.includes('editor-')) {
                
                return;
            } else {
                
                event.preventDefault();
                window.location.href = appUrl('dashboard.php');
            }
        }
    });
})();
