(() => {
  "use strict";

  const whatsappButton = document.querySelector(".whatsapp-float");
  if (!whatsappButton) return;

  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const projectTypes = ["Web Platform", "Business Software", "Automation", "AI Solution", "Mobile App", "Other"];
  const state = { step: "name", name: "", mobile: "", project: "", query: "", initialized: false, busy: false };

  const create = (tag, className, text) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  };

  const launcher = create("button", "assistant-launcher");
  launcher.type = "button";
  launcher.setAttribute("aria-label", "Open Unnat AI Assistant");
  launcher.setAttribute("aria-expanded", "false");
  launcher.setAttribute("aria-controls", "uts-assistant-panel");
  launcher.innerHTML = '<span class="assistant-launcher-mark" aria-hidden="true">AI</span><span class="assistant-online-dot" aria-hidden="true"></span>';

  const panel = create("section", "assistant-panel");
  panel.id = "uts-assistant-panel";
  panel.hidden = true;
  panel.setAttribute("role", "dialog");
  panel.setAttribute("aria-modal", "false");
  panel.setAttribute("aria-labelledby", "uts-assistant-title");

  const header = create("header", "assistant-header");
  const identity = create("div", "assistant-identity");
  const avatar = create("span", "assistant-avatar", "AI");
  avatar.setAttribute("aria-hidden", "true");
  const identityText = create("div");
  const title = create("h2", "", "Unnat AI Assistant");
  title.id = "uts-assistant-title";
  const availability = create("p", "", "Online • Project inquiry assistant");
  identityText.append(title, availability);
  identity.append(avatar, identityText);
  const closeButton = create("button", "assistant-close", "×");
  closeButton.type = "button";
  closeButton.setAttribute("aria-label", "Close assistant");
  header.append(identity, closeButton);

  const messages = create("div", "assistant-messages");
  messages.setAttribute("role", "log");
  messages.setAttribute("aria-live", "polite");
  messages.setAttribute("aria-relevant", "additions");

  const choices = create("div", "assistant-choices");
  choices.hidden = true;

  const composer = create("form", "assistant-composer");
  const fieldLabel = create("label", "assistant-field-label", "Your name");
  fieldLabel.htmlFor = "assistant-input";
  const inputRow = create("div", "assistant-input-row");
  const input = create("input", "assistant-input");
  input.id = "assistant-input";
  input.type = "text";
  input.autocomplete = "name";
  input.maxLength = 25;
  input.placeholder = "Enter your name";
  const textarea = create("textarea", "assistant-input assistant-textarea");
  textarea.id = "assistant-query";
  textarea.maxLength = 420;
  textarea.rows = 3;
  textarea.placeholder = "Tell us what you want to build or improve";
  textarea.hidden = true;
  const sendButton = create("button", "assistant-send", "→");
  sendButton.type = "submit";
  sendButton.setAttribute("aria-label", "Send response");
  const errorText = create("p", "assistant-validation");
  errorText.setAttribute("role", "alert");
  inputRow.append(input, textarea, sendButton);
  composer.append(fieldLabel, inputRow, errorText);

  const privacy = create("p", "assistant-privacy", "Your details are used only to respond to this project inquiry.");
  panel.append(header, messages, choices, composer, privacy);
  document.body.append(launcher, panel);

  const scrollToLatest = () => {
    messages.scrollTop = messages.scrollHeight;
  };

  const addMessage = (text, sender = "bot") => {
    const row = create("div", `assistant-message-row ${sender}`);
    if (sender === "bot") {
      const mark = create("span", "assistant-message-avatar", "AI");
      mark.setAttribute("aria-hidden", "true");
      row.append(mark);
    }
    row.append(create("div", "assistant-message", text));
    messages.append(row);
    scrollToLatest();
    return row;
  };

  const showTyping = () => {
    const row = create("div", "assistant-message-row bot assistant-typing-row");
    const mark = create("span", "assistant-message-avatar", "AI");
    mark.setAttribute("aria-hidden", "true");
    const bubble = create("div", "assistant-message assistant-typing");
    bubble.setAttribute("aria-label", "Assistant is typing");
    bubble.innerHTML = "<i></i><i></i><i></i>";
    row.append(mark, bubble);
    messages.append(row);
    scrollToLatest();
    return row;
  };

  const botReply = async (text) => {
    const typing = showTyping();
    await new Promise((resolve) => window.setTimeout(resolve, reducedMotion ? 0 : 430));
    typing.remove();
    addMessage(text, "bot");
  };

  const clearValidation = () => {
    errorText.textContent = "";
  };

  const setValidation = (message) => {
    errorText.textContent = message;
  };

  const showComposer = (step) => {
    state.step = step;
    composer.hidden = false;
    choices.hidden = true;
    input.hidden = step === "query";
    textarea.hidden = step !== "query";
    clearValidation();

    if (step === "name") {
      fieldLabel.textContent = "Your name";
      fieldLabel.htmlFor = "assistant-input";
      input.type = "text";
      input.inputMode = "text";
      input.autocomplete = "name";
      input.maxLength = 25;
      input.placeholder = "Enter your name";
      input.value = "";
      input.focus();
    } else if (step === "mobile") {
      fieldLabel.textContent = "Mobile number";
      fieldLabel.htmlFor = "assistant-input";
      input.type = "tel";
      input.inputMode = "numeric";
      input.autocomplete = "tel";
      input.maxLength = 10;
      input.placeholder = "10-digit mobile number";
      input.value = "";
      input.focus();
    } else {
      fieldLabel.textContent = "Project query";
      fieldLabel.htmlFor = "assistant-query";
      textarea.value = "";
      textarea.focus();
    }
  };

  const showProjectChoices = () => {
    state.step = "project";
    composer.hidden = true;
    choices.hidden = false;
    choices.replaceChildren();
    projectTypes.forEach((project) => {
      const button = create("button", "assistant-choice", project);
      button.type = "button";
      button.addEventListener("click", async () => {
        if (state.busy) return;
        state.project = project;
        addMessage(project, "user");
        choices.hidden = true;
        await botReply(`Great choice. Briefly describe your ${project.toLowerCase()} requirement.`);
        showComposer("query");
      });
      choices.append(button);
    });
    choices.querySelector("button")?.focus();
  };

  const resetConversation = async () => {
    Object.assign(state, { step: "name", name: "", mobile: "", project: "", query: "", busy: false, initialized: true });
    messages.replaceChildren();
    choices.replaceChildren();
    await botReply("Hi! I’m the Unnat AI Assistant. I can raise a project inquiry for you in about a minute.");
    await botReply("What’s your name?");
    showComposer("name");
  };

  const showCompletionActions = () => {
    const actions = create("div", "assistant-completion-actions");
    const restart = create("button", "assistant-choice", "Raise another inquiry");
    restart.type = "button";
    restart.addEventListener("click", resetConversation);
    const whatsapp = create("a", "assistant-choice secondary", "Continue on WhatsApp");
    whatsapp.href = whatsappButton.href;
    whatsapp.target = "_blank";
    whatsapp.rel = "noopener";
    actions.append(restart, whatsapp);
    messages.append(actions);
    scrollToLatest();
  };

  const submitInquiry = async () => {
    state.step = "submitting";
    state.busy = true;
    composer.hidden = true;
    await botReply("Thanks — I’m raising your inquiry with our project team now.");

    try {
      const response = await fetch("backend/bot_query.php", {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        credentials: "same-origin",
        body: JSON.stringify({
          name: state.name,
          mobile: state.mobile,
          project: state.project,
          query: state.query,
          website: "",
        }),
      });
      const result = await response.json().catch(() => ({}));
      if (!response.ok || !result.success) throw new Error(result.error || "Your inquiry could not be saved.");

      const reference = result.reference ? ` Reference #${result.reference}.` : "";
      await botReply(`Done, ${state.name}! Your ${state.project.toLowerCase()} inquiry has been raised.${reference}`);
      await botReply(`Our team will contact you on ${state.mobile}.`);
      state.step = "done";
      showCompletionActions();
    } catch (error) {
      await botReply(error.message || "I couldn’t save the inquiry. Please try again or continue on WhatsApp.");
      state.step = "query";
      const retry = create("button", "assistant-choice", "Try submitting again");
      retry.type = "button";
      retry.addEventListener("click", submitInquiry, { once: true });
      choices.replaceChildren(retry);
      choices.hidden = false;
    } finally {
      state.busy = false;
    }
  };

  composer.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (state.busy) return;
    clearValidation();

    if (state.step === "name") {
      const name = input.value.trim().replace(/\s+/g, " ");
      if (name.length < 2 || name.length > 25) {
        setValidation("Please enter your name using 2–25 characters.");
        return;
      }
      state.name = name;
      addMessage(name, "user");
      composer.hidden = true;
      await botReply(`Nice to meet you, ${name}. What is your 10-digit mobile number?`);
      showComposer("mobile");
      return;
    }

    if (state.step === "mobile") {
      const mobile = input.value.replace(/\D/g, "");
      if (!/^\d{10}$/.test(mobile)) {
        setValidation("Please enter a valid 10-digit mobile number.");
        return;
      }
      state.mobile = mobile;
      addMessage(mobile, "user");
      composer.hidden = true;
      await botReply("What kind of project can we help you with?");
      showProjectChoices();
      return;
    }

    if (state.step === "query") {
      const query = textarea.value.trim().replace(/\s+/g, " ");
      if (query.length < 10 || query.length > 420) {
        setValidation("Please describe your requirement using 10–420 characters.");
        return;
      }
      state.query = query;
      addMessage(query, "user");
      await submitInquiry();
    }
  });

  const openAssistant = async () => {
    panel.hidden = false;
    requestAnimationFrame(() => panel.classList.add("open"));
    launcher.setAttribute("aria-expanded", "true");
    launcher.classList.add("active");
    if (!state.initialized) await resetConversation();
    else if (state.step === "name" || state.step === "mobile") input.focus();
    else if (state.step === "query") textarea.focus();
  };

  const closeAssistant = () => {
    panel.classList.remove("open");
    launcher.setAttribute("aria-expanded", "false");
    launcher.classList.remove("active");
    window.setTimeout(() => {
      panel.hidden = true;
      launcher.focus();
    }, reducedMotion ? 0 : 180);
  };

  launcher.addEventListener("click", () => panel.hidden ? openAssistant() : closeAssistant());
  closeButton.addEventListener("click", closeAssistant);
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !panel.hidden) closeAssistant();
  });
})();
