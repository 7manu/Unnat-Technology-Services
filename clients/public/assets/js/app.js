/**
 * Client portal interactions. Everything here is progressive: each screen still
 * works with JavaScript disabled, these handlers only make it nicer to use.
 */

/* ------------------------------------------------------------------ *
 * Modals
 * ------------------------------------------------------------------ */
let lastFocused = null;

const openModal = (id) => {
  const modal = document.getElementById(id);
  if (!modal) return;
  lastFocused = document.activeElement;
  modal.classList.add('is-open');
  modal.setAttribute('aria-hidden', 'false');
  document.body.classList.add('modal-open');
  const firstInput = modal.querySelector('input:not([type=hidden]), select, textarea, button');
  if (firstInput) firstInput.focus();
};

const closeModal = (modal) => {
  if (!modal) return;
  modal.classList.remove('is-open');
  modal.setAttribute('aria-hidden', 'true');
  if (!document.querySelector('.modal.is-open')) document.body.classList.remove('modal-open');
  if (lastFocused instanceof HTMLElement) lastFocused.focus();
};

/* Keep Tab inside an open dialog. */
document.addEventListener('keydown', (event) => {
  if (event.key !== 'Tab') return;
  const modal = document.querySelector('.modal.is-open .modal-panel');
  if (!modal) return;
  const focusable = modal.querySelectorAll('a[href], button:not([disabled]), input:not([type=hidden]), select, textarea');
  if (!focusable.length) return;
  const first = focusable[0];
  const last = focusable[focusable.length - 1];
  if (event.shiftKey && document.activeElement === first) {
    event.preventDefault();
    last.focus();
  } else if (!event.shiftKey && document.activeElement === last) {
    event.preventDefault();
    first.focus();
  }
});

document.addEventListener('click', (event) => {
  if (event.target.closest('[data-sidebar-open]')) document.body.classList.add('sidebar-open');
  if (event.target.closest('[data-sidebar-close]')) document.body.classList.remove('sidebar-open');

  const opener = event.target.closest('[data-modal-open]');
  if (opener) openModal(opener.dataset.modalOpen);

  const closer = event.target.closest('[data-modal-close]');
  if (closer) closeModal(closer.closest('.modal'));

  const dismiss = event.target.closest('[data-alert-dismiss]');
  if (dismiss) dismiss.closest('.alert').remove();
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') {
    document.querySelectorAll('.modal.is-open').forEach(closeModal);
    document.body.classList.remove('sidebar-open');
  }
});

/* ------------------------------------------------------------------ *
 * Forms
 * ------------------------------------------------------------------ */
document.querySelectorAll('form[data-confirm]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    if (!confirm(form.dataset.confirm)) event.preventDefault();
  });
});

/* Stop double submissions on slow connections. */
document.addEventListener('submit', (event) => {
  const form = event.target;
  if (!(form instanceof HTMLFormElement) || form.dataset.confirm) return;
  const submit = form.querySelector('button[type=submit]');
  if (!submit || submit.disabled) return;
  window.setTimeout(() => {
    submit.disabled = true;
    submit.dataset.originalLabel = submit.textContent;
    submit.textContent = 'Working…';
  }, 0);
});

/* Repeating part-payment rows. */
const paymentMethods = ['Bank transfer', 'UPI', 'Cash', 'Cheque', 'Card', 'Other'];

const paymentRowMarkup = () => `
  <input type="hidden" name="part_payment_id[]" value="">
  <label>Amount <input type="number" name="part_payment_amount[]" min="0" step="0.01"></label>
  <label>Date and time <input type="datetime-local" name="part_payment_at[]"></label>
  <label>Method <select name="part_payment_method[]">${paymentMethods.map((m) => `<option value="${m}">${m}</option>`).join('')}</select></label>
  <label>Reference <input name="part_payment_reference[]" placeholder="UTR / cheque no."></label>
  <label class="payment-statement">Statement <input name="part_payment_statement[]"></label>
  <button class="danger-button" type="button" data-payment-remove>Remove</button>
`;

document.addEventListener('click', (event) => {
  const addButton = event.target.closest('[data-payment-add]');
  if (addButton) {
    const panel = addButton.closest('[data-payment-list]');
    const row = document.createElement('div');
    row.className = 'payment-row';
    row.innerHTML = paymentRowMarkup();
    panel.insertBefore(row, addButton);
    const amount = row.querySelector('input[name="part_payment_amount[]"]');
    if (amount) amount.focus();
  }

  const removeButton = event.target.closest('[data-payment-remove]');
  if (removeButton) {
    const row = removeButton.closest('.payment-row');
    const panel = removeButton.closest('[data-payment-list]');
    if (panel.querySelectorAll('.payment-row').length > 1) {
      row.remove();
    } else {
      row.querySelectorAll('input').forEach((input) => { input.value = ''; });
    }
  }
});

/* Live balance preview while part payments are edited. */
const refreshPaymentTotals = (panel) => {
  const form = panel.closest('form');
  const output = panel.querySelector('[data-payment-total]');
  if (!form || !output) return;
  const total = Number(form.querySelector('input[name="total_payment"]')?.value || 0);
  const taxPercent = Number(form.querySelector('input[name="tax_percent"]')?.value || 0);
  const grand = total * (1 + taxPercent / 100);
  let paid = 0;
  panel.querySelectorAll('input[name="part_payment_amount[]"]').forEach((input) => {
    paid += Number(input.value || 0);
  });
  const balance = Math.max(0, grand - paid);
  output.textContent = `Billable ${grand.toFixed(2)} · received ${paid.toFixed(2)} · balance ${balance.toFixed(2)}`;
};

document.addEventListener('input', (event) => {
  const field = event.target;
  if (!field.name) return;
  if (!['part_payment_amount[]', 'total_payment', 'tax_percent'].includes(field.name)) return;
  const form = field.closest('form');
  form?.querySelectorAll('[data-payment-list]').forEach(refreshPaymentTotals);
});

document.querySelectorAll('[data-payment-list]').forEach(refreshPaymentTotals);

/* ------------------------------------------------------------------ *
 * Credential share composer
 *
 * Builds the message the admin sends to a client and points WhatsApp,
 * email, SMS, the clipboard and the device share sheet at it.
 * ------------------------------------------------------------------ */
const buildShareMessage = (composer) => {
  const value = (field) => composer.querySelector(`[data-share-field="${field}"]`)?.value.trim() || '';
  const name = composer.dataset.clientName || 'there';
  const url = value('url');
  const email = value('email');
  const password = value('password');
  const note = value('note');

  const lines = [`Hello ${name},`, ''];
  lines.push('Here are your Unnat Technology Services client portal details.');
  lines.push('');
  if (url) lines.push(`Login page: ${url}`);
  if (email) lines.push(`Login ID: ${email}`);
  if (password) lines.push(`Password: ${password}`);
  if (password) {
    lines.push('');
    lines.push('Please keep this password private.');
  }
  if (note) {
    lines.push('');
    lines.push(note);
  }
  lines.push('');
  lines.push('Unnat Technology Services');

  return lines.join('\n');
};

const refreshShareComposer = (composer) => {
  const message = buildShareMessage(composer);
  const preview = composer.querySelector('[data-share-preview]');
  if (preview) preview.textContent = message;

  const encoded = encodeURIComponent(message);
  const subject = encodeURIComponent('Your Unnat Technology Services portal login');

  const whatsapp = composer.querySelector('[data-share-target="whatsapp"]');
  if (whatsapp) {
    const number = composer.dataset.whatsapp || '';
    whatsapp.href = number ? `https://wa.me/${number}?text=${encoded}` : `https://wa.me/?text=${encoded}`;
  }

  const mail = composer.querySelector('[data-share-target="email"]');
  if (mail) mail.href = `mailto:${composer.dataset.email || ''}?subject=${subject}&body=${encoded}`;

  const sms = composer.querySelector('[data-share-target="sms"]');
  if (sms) sms.href = `sms:${composer.dataset.whatsapp ? '+' + composer.dataset.whatsapp : ''}?&body=${encoded}`;
};

document.querySelectorAll('[data-share-composer]').forEach((composer) => {
  refreshShareComposer(composer);

  composer.addEventListener('input', (event) => {
    if (event.target.matches('[data-share-field]')) refreshShareComposer(composer);
  });

  const nativeButton = composer.querySelector('[data-share-native]');
  if (nativeButton && navigator.share) {
    nativeButton.hidden = false;
    nativeButton.addEventListener('click', () => {
      navigator.share({
        title: 'Client portal login',
        text: buildShareMessage(composer),
      }).catch(() => {});
    });
  }

  const copyButton = composer.querySelector('[data-share-copy]');
  if (copyButton) {
    copyButton.addEventListener('click', async () => {
      const message = buildShareMessage(composer);
      const original = copyButton.textContent;
      try {
        await navigator.clipboard.writeText(message);
      } catch (error) {
        const preview = composer.querySelector('[data-share-preview]');
        if (preview) {
          const range = document.createRange();
          range.selectNodeContents(preview);
          const selection = window.getSelection();
          selection.removeAllRanges();
          selection.addRange(range);
        }
        copyButton.textContent = 'Select and copy';
        window.setTimeout(() => { copyButton.textContent = original; }, 2400);
        return;
      }
      copyButton.textContent = 'Copied';
      window.setTimeout(() => { copyButton.textContent = original; }, 1800);
    });
  }
});

/* Opens a modal the server asked for, e.g. right after a password was set. */
const autoOpen = document.querySelector('[data-modal-autoopen]');
if (autoOpen) openModal(autoOpen.dataset.modalAutoopen);

/* ------------------------------------------------------------------ *
 * PWA and push notifications
 * ------------------------------------------------------------------ */
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
