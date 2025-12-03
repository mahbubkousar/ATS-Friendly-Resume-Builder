<!-- MODALS LOADED SUCCESSFULLY -->
<!-- Notification Modal -->
<div class="modal-overlay" id="notificationModal" style="display: none;">
    <div class="notification-modal" id="notificationModalContent">
        <div class="modal-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h3 id="notificationTitle">Success</h3>
        <p id="notificationMessage">Operation completed successfully</p>
        <div class="modal-buttons">
            <button class="modal-btn modal-btn-primary" id="notificationOkBtn">OK</button>
        </div>
    </div>
</div>

<!-- Analysis Progress Modal -->
<div class="modal-overlay" id="analysisProgressModal" style="display: none;">
    <div class="progress-modal">
        <div class="progress-icon">
            <i class="fas fa-wand-magic-sparkles fa-spin"></i>
        </div>
        <h3 class="progress-title">Analyzing Your Resume</h3>
        <p class="progress-stage" id="progressStage">Initializing analysis...</p>

        <div class="progress-bar-container">
            <div class="progress-bar-fill" id="progressBarFill"></div>
        </div>
        <div class="progress-percentage" id="progressPercentage">0%</div>

        <div class="progress-steps">
            <div class="progress-step" id="step1">
                <i class="fas fa-file-pdf"></i>
                <span>Extracting Text</span>
            </div>
            <div class="progress-step" id="step2">
                <i class="fas fa-align-left"></i>
                <span>Analyzing Format</span>
            </div>
            <div class="progress-step" id="step3">
                <i class="fas fa-key"></i>
                <span>Checking Keywords</span>
            </div>
            <div class="progress-step" id="step4">
                <i class="fas fa-list-check"></i>
                <span>Evaluating Structure</span>
            </div>
            <div class="progress-step" id="step5">
                <i class="fas fa-chart-bar"></i>
                <span>Generating Insights</span>
            </div>
        </div>
    </div>
</div>
