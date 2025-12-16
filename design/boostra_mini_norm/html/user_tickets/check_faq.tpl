<div id="checkFaqModal" class="check-faq-modal">
  <div class="check-faq-modal-content">
    <button class="modal-close" type="button" aria-label="Закрыть" onclick="closeCheckFaqModal()">&times;</button>
    <div class="modal-body">
      <p>
        Перед тем как оставить обращение, загляните в раздел «Вопрос-ответ» — возможно, ответ уже там.<br>
        Если вы уже ознакомились с информацией — можете продолжить и оставить обращение.
      </p>
      <div style="display:flex;justify-content:space-between;gap:1rem;">
        <a href="/user/faq" class="btn btn-secondary" style="flex:1;">Вопрос-ответ</a>
        <button class="btn btn-primary" style="flex:1;" onclick="openTicketCreateModalFromCheckFaq()">Продолжить</button>
      </div>
    </div>
  </div>
</div>
