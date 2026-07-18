const openModal = (id) => {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.add('is-open');
  modal.setAttribute('aria-hidden', 'false');
  const firstInput = modal.querySelector('input, select, textarea, button');
  if (firstInput) firstInput.focus();
};

const closeModal = (modal) => {
  modal.classList.remove('is-open');
  modal.setAttribute('aria-hidden', 'true');
};

document.addEventListener('click', (event) => {
  const sidebarOpen = event.target.closest('[data-sidebar-open]');
  if (sidebarOpen) document.body.classList.add('sidebar-open');

  const sidebarClose = event.target.closest('[data-sidebar-close]');
  if (sidebarClose) document.body.classList.remove('sidebar-open');

  const opener = event.target.closest('[data-modal-open]');
  if (opener) openModal(opener.dataset.modalOpen);

  const closer = event.target.closest('[data-modal-close]');
  if (closer) closeModal(closer.closest('.modal'));
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') {
    document.querySelectorAll('.modal.is-open').forEach(closeModal);
    document.body.classList.remove('sidebar-open');
  }
});

document.querySelectorAll('form[data-confirm]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    if (!confirm(form.dataset.confirm)) event.preventDefault();
  });
});

document.addEventListener('click', (event) => {
  const addButton = event.target.closest('[data-payment-add]');
  if (addButton) {
    const panel = addButton.closest('[data-payment-list]');
    const row = document.createElement('div');
    row.className = 'payment-row';
    row.innerHTML = `
      <label>Amount <input type="number" name="part_payment_amount[]" min="0" step="0.01"></label>
      <label>Date and time <input type="datetime-local" name="part_payment_at[]"></label>
      <label class="payment-statement">Statement <input name="part_payment_statement[]"></label>
      <button class="danger-button" type="button" data-payment-remove>Remove</button>
    `;
    panel.insertBefore(row, addButton);
  }

  const removeButton = event.target.closest('[data-payment-remove]');
  if (removeButton) {
    const row = removeButton.closest('.payment-row');
    const panel = removeButton.closest('[data-payment-list]');
    if (panel.querySelectorAll('.payment-row').length > 1) {
      row.remove();
    } else {
      row.querySelectorAll('input').forEach((input) => input.value = '');
    }
  }
});

const urlBase64ToUint8Array = (base64String) => {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
  const rawData = atob(base64);
  return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
};

const registerPwa = async () => {
  if (!('serviceWorker' in navigator)) return;
  const registration = await navigator.serviceWorker.register('/service-worker.js');

  if (!('PushManager' in window) || Notification.permission === 'denied') return;
  const keyResponse = await fetch('/api/push/vapid-public-key');
  const { publicKey } = await keyResponse.json();
  if (!publicKey) return;

  const permission = await Notification.requestPermission();
  if (permission !== 'granted') return;

  const subscription = await registration.pushManager.subscribe({
    userVisibleOnly: true,
    applicationServerKey: urlBase64ToUint8Array(publicKey),
  });

  await fetch('/api/push/subscribe', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(subscription),
  });
};

window.addEventListener('load', () => {
  registerPwa().catch(() => {});
});
