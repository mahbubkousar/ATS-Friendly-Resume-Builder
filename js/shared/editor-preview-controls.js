/**
 * Preview controls shared by the specialized resume editors.
 */
(function installEditorPreviewControls() {
function printPreview() {
    try {
        if (!previewIframe || !previewIframe.contentWindow) {
            throw new Error('Resume preview is unavailable.');
        }
        previewIframe.contentWindow.print();
    } catch (error) {
        console.error('Print error:', error);
        showNotificationModal('Error opening print dialog. Please try again.', 'error');
    }
}

function setupZoomControls() {
    const zoomInBtn = document.getElementById('zoomInBtn');
    const zoomOutBtn = document.getElementById('zoomOutBtn');
    const refreshBtn = document.getElementById('refreshBtn');
    const wrapper = document.getElementById('previewWrapper');
    const zoomDisplay = document.getElementById('zoomLevel');

    if (!zoomInBtn || !zoomOutBtn || !wrapper || !zoomDisplay) {
        return;
    }

    let zoomLevel = 100;
    const renderZoom = () => {
        wrapper.style.transform = `scale(${zoomLevel / 100})`;
        zoomDisplay.textContent = `${zoomLevel}%`;
    };

    zoomInBtn.addEventListener('click', () => {
        zoomLevel = Math.min(150, zoomLevel + 10);
        renderZoom();
    });

    zoomOutBtn.addEventListener('click', () => {
        zoomLevel = Math.max(50, zoomLevel - 10);
        renderZoom();
    });

    refreshBtn?.addEventListener('click', loadTemplatePreview);
}

window.EditorPreviewControls = Object.freeze({
    print: printPreview,
    setupZoom: setupZoomControls
});
})();
