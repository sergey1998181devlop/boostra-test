$(document).ready(function () {
  // Create modal HTML
  const modalHTML = `
                    <div id="cancel-modal" class="modal-overlay" style="display: none;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h3>Отказ от займа</h3>
                                <span class="modal-close">&times;</span>
                            </div>
                            <div class="modal-body">
                                <p>Вы уверены, что хотите отказаться от займа?</p>
                                <p class="modal-warning">Это действие нельзя будет отменить.</p>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-cancel" id="modal-cancel">Отмена</button>
                                <button class="btn btn-confirm" id="modal-confirm">Да, отказаться</button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="success-modal" class="modal-overlay" style="display: none;">
                        <div class="modal-content success">
                            <div class="modal-header">
                                <h3>Успешно</h3>
                            </div>
                            <div class="modal-body">
                                <div class="success-icon">✓</div>
                                <p>Заявка успешно отменена</p>
                                <p class="modal-info">Страница будет перезагружена через несколько секунд...</p>
                            </div>
                        </div>
                    </div>
                    
                    <div id="error-modal" class="modal-overlay" style="display: none;">
                        <div class="modal-content error">
                            <div class="modal-header">
                                <h3>Ошибка</h3>
                                <span class="modal-close">&times;</span>
                            </div>
                            <div class="modal-body">
                                <div class="error-icon">⚠</div>
                                <p id="error-message">Произошла ошибка</p>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-cancel" id="error-close">Закрыть</button>
                            </div>
                        </div>
                    </div>
                `;

  // Add modal CSS
  const modalCSS = `
                    <style>
                    .modal-overlay {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0, 0, 0, 0.5);
                        z-index: 10000;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        animation: fadeIn 0.3s ease;
                    }
                    
                    .modal-content {
                        background: white;
                        border-radius: 12px;
                        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                        max-width: 400px;
                        width: 90%;
                        animation: slideIn 0.3s ease;
                        overflow: hidden;
                    }
                    
                    .modal-content.success {
                        border-top: 4px solid #28a745;
                    }
                    
                    .modal-content.error {
                        border-top: 4px solid #dc3545;
                    }
                    
                    .modal-header {
                        padding: 20px 20px 10px;
                        border-bottom: 1px solid #eee;
                        position: relative;
                    }
                    
                    .modal-header h3 {
                        margin: 0;
                        color: #333;
                        font-size: 18px;
                        font-weight: 600;
                    }
                    
                    .modal-close {
                        position: absolute;
                        top: 15px;
                        right: 20px;
                        font-size: 24px;
                        cursor: pointer;
                        color: #999;
                        transition: color 0.2s;
                    }
                    
                    .modal-close:hover {
                        color: #333;
                    }
                    
                    .modal-body {
                        padding: 20px;
                        text-align: center;
                    }
                    
                    .modal-body p {
                        margin: 10px 0;
                        color: #555;
                        line-height: 1.5;
                    }
                    
                    .modal-warning {
                        color: #856404 !important;
                        background: #fff3cd;
                        padding: 8px 12px;
                        border-radius: 6px;
                        font-size: 14px;
                    }
                    
                    .modal-info {
                        color: #666 !important;
                        font-size: 14px;
                    }
                    
                    .success-icon, .error-icon {
                        font-size: 48px;
                        margin-bottom: 15px;
                    }
                    
                    .success-icon {
                        color: #28a745;
                    }
                    
                    .error-icon {
                        color: #dc3545;
                    }
                    
                    .modal-footer {
                        padding: 15px 20px 20px;
                        display: flex;
                        gap: 10px;
                        justify-content: flex-end;
                    }
                    
                    .btn {
                        padding: 10px 20px;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        font-size: 14px;
                        font-weight: 500;
                        transition: all 0.2s;
                        min-width: 80px;
                    }
                    
                    .btn-cancel {
                        background: #6c757d;
                        color: white;
                    }
                    
                    .btn-cancel:hover {
                        background: #5a6268;
                    }
                    
                    .btn-confirm {
                        background: #dc3545;
                        color: white;
                    }
                    
                    .btn-confirm:hover {
                        background: #c82333;
                    }
                    
                    @keyframes fadeIn {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }
                    
                    @keyframes slideIn {
                        from { transform: translateY(-50px); opacity: 0; }
                        to { transform: translateY(0); opacity: 1; }
                    }
                    </style>
                `;

  // Add CSS and modal to page
  $("head").append(modalCSS);
  $("body").append(modalHTML);

  // Modal functions
  function showModal(modalId) {
    $("#" + modalId).fadeIn(300);
  }

  function hideModal(modalId) {
    $("#" + modalId).fadeOut(300);
  }

  // Cancel button click handler
  $("#cooling-off-cancel-btn").on("click", function () {
    showModal("cancel-modal");
  });

  // Modal close handlers
  $(".modal-close, #modal-cancel, #error-close").on("click", function () {
    hideModal("cancel-modal");
    hideModal("error-modal");
  });

  // Confirm cancellation
  $("#modal-confirm").on("click", function () {
    hideModal("cancel-modal");

    $.ajax({
      url: "ajax/cancel_order.php",
      type: "POST",
      data: {
        action: "cancel_cooling_off",
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          showModal("success-modal");
          setTimeout(function () {
            location.reload();
          }, 2000);
        } else {
          $("#error-message").text(
            "Ошибка: " + (response.error || "Неизвестная ошибка")
          );
          showModal("error-modal");
        }
      },
      error: function () {
        $("#error-message").text("Произошла ошибка при отправке запроса");
        showModal("error-modal");
      },
    });
  });

  // Close modal on overlay click
  $(".modal-overlay").on("click", function (e) {
    if (e.target === this) {
      hideModal("cancel-modal");
      hideModal("error-modal");
    }
  });
});
