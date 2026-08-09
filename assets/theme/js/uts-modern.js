(() => {
  "use strict";

  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const header = document.querySelector(".site-header");
  const nav = document.querySelector(".primary-nav");
  const navToggle = document.querySelector(".nav-toggle");

  const typewriter = document.querySelector("[data-typewriter]");
  if (typewriter && !reducedMotion) {
    const content = typewriter.querySelector(".typewriter-content");
    const characters = [];

    if (content) {
      const walker = document.createTreeWalker(content, NodeFilter.SHOW_TEXT);
      let textNode = walker.nextNode();
      while (textNode) {
        const value = textNode.nodeValue || "";
        [...value].forEach((character) => characters.push({ node: textNode, character }));
        textNode.nodeValue = "";
        textNode = walker.nextNode();
      }

      typewriter.classList.add("is-typing");
      const duration = Math.min(1150, Math.max(780, characters.length * 16));
      const start = performance.now();
      let rendered = 0;

      const typeNext = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        const target = Math.ceil(progress * characters.length);
        while (rendered < target) {
          const item = characters[rendered];
          item.node.nodeValue += item.character;
          rendered += 1;
        }

        if (progress < 1) {
          requestAnimationFrame(typeNext);
        } else {
          window.setTimeout(() => typewriter.classList.remove("is-typing"), 260);
        }
      };

      requestAnimationFrame(typeNext);
    }
  }

  const updateHeader = () => header?.classList.toggle("scrolled", window.scrollY > 20);
  updateHeader();
  window.addEventListener("scroll", updateHeader, { passive: true });

  if (nav && navToggle) {
    const closeNav = () => {
      nav.classList.remove("open");
      navToggle.classList.remove("active");
      navToggle.setAttribute("aria-expanded", "false");
    };

    navToggle.addEventListener("click", () => {
      const isOpen = nav.classList.toggle("open");
      navToggle.classList.toggle("active", isOpen);
      navToggle.setAttribute("aria-expanded", String(isOpen));
    });

    nav.querySelectorAll("a").forEach((link) => link.addEventListener("click", closeNav));
    document.addEventListener("keydown", (event) => event.key === "Escape" && closeNav());
    document.addEventListener("click", (event) => {
      if (!nav.contains(event.target) && !navToggle.contains(event.target)) closeNav();
    });
  }

  document.querySelectorAll("[data-year]").forEach((node) => {
    node.textContent = String(new Date().getFullYear());
  });

  const reveals = [...document.querySelectorAll(".reveal")];
  if (reducedMotion || !("IntersectionObserver" in window)) {
    reveals.forEach((node) => node.classList.add("visible"));
  } else {
    const revealObserver = new IntersectionObserver(
      (entries, observer) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          const delay = Number(entry.target.dataset.delay || 0);
          window.setTimeout(() => entry.target.classList.add("visible"), delay);
          observer.unobserve(entry.target);
        });
      },
      { threshold: 0.12, rootMargin: "0px 0px -50px" }
    );
    reveals.forEach((node) => revealObserver.observe(node));
  }

  const counters = [...document.querySelectorAll("[data-count]")];
  const animateCounter = (node) => {
    const target = Number(node.dataset.count || 0);
    const suffix = node.dataset.suffix || "";
    if (reducedMotion) {
      node.textContent = `${target}${suffix}`;
      return;
    }
    const start = performance.now();
    const duration = 1200;
    const tick = (now) => {
      const progress = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      node.textContent = `${Math.round(target * eased)}${suffix}`;
      if (progress < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  };

  if ("IntersectionObserver" in window) {
    const counterObserver = new IntersectionObserver(
      (entries, observer) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          animateCounter(entry.target);
          observer.unobserve(entry.target);
        });
      },
      { threshold: 0.5 }
    );
    counters.forEach((node) => counterObserver.observe(node));
  } else {
    counters.forEach(animateCounter);
  }

  if (!reducedMotion && window.matchMedia("(pointer: fine)").matches) {
    document.querySelectorAll("[data-tilt]").forEach((card) => {
      card.addEventListener("pointermove", (event) => {
        const rect = card.getBoundingClientRect();
        const x = (event.clientX - rect.left) / rect.width - 0.5;
        const y = (event.clientY - rect.top) / rect.height - 0.5;
        card.style.transform = `perspective(900px) rotateX(${-y * 7}deg) rotateY(${x * 8}deg) translateY(-3px)`;
      });
      card.addEventListener("pointerleave", () => {
        card.style.transform = "";
      });
    });

    document.querySelectorAll("[data-magnetic]").forEach((button) => {
      button.addEventListener("pointermove", (event) => {
        const rect = button.getBoundingClientRect();
        const x = event.clientX - rect.left - rect.width / 2;
        const y = event.clientY - rect.top - rect.height / 2;
        button.style.transform = `translate(${x * 0.1}px, ${y * 0.1}px)`;
      });
      button.addEventListener("pointerleave", () => {
        button.style.transform = "";
      });
    });

    const stage = document.querySelector(".hero-stage");
    if (stage) {
      window.addEventListener(
        "scroll",
        () => {
          if (window.scrollY < window.innerHeight * 1.2) {
            stage.style.transform = `translate3d(0, ${window.scrollY * 0.065}px, 0)`;
          }
        },
        { passive: true }
      );
    }
  }

  const canvas = document.getElementById("particle-field");
  if (canvas && !reducedMotion) {
    const context = canvas.getContext("2d", { alpha: true });
    let particles = [];
    let frameId;
    let width = 0;
    let height = 0;
    let dpr = 1;

    const resize = () => {
      const rect = canvas.getBoundingClientRect();
      dpr = Math.min(window.devicePixelRatio || 1, 1.5);
      width = rect.width;
      height = rect.height;
      canvas.width = Math.round(width * dpr);
      canvas.height = Math.round(height * dpr);
      context.setTransform(dpr, 0, 0, dpr, 0, 0);
      const particleCount = Math.min(52, Math.max(24, Math.floor(width / 25)));
      particles = Array.from({ length: particleCount }, () => ({
        x: Math.random() * width,
        y: Math.random() * height,
        vx: (Math.random() - 0.5) * 0.18,
        vy: (Math.random() - 0.5) * 0.18,
        radius: Math.random() * 1.5 + 0.55,
      }));
    };

    const render = () => {
      context.clearRect(0, 0, width, height);
      particles.forEach((particle, index) => {
        particle.x += particle.vx;
        particle.y += particle.vy;
        if (particle.x < 0 || particle.x > width) particle.vx *= -1;
        if (particle.y < 0 || particle.y > height) particle.vy *= -1;

        context.beginPath();
        context.fillStyle = "rgba(217, 119, 6, .44)";
        context.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
        context.fill();

        for (let next = index + 1; next < particles.length; next += 1) {
          const other = particles[next];
          const dx = particle.x - other.x;
          const dy = particle.y - other.y;
          const distance = Math.hypot(dx, dy);
          if (distance > 112) continue;
          context.beginPath();
          context.strokeStyle = `rgba(224, 145, 24, ${(1 - distance / 112) * 0.14})`;
          context.moveTo(particle.x, particle.y);
          context.lineTo(other.x, other.y);
          context.stroke();
        }
      });
      frameId = requestAnimationFrame(render);
    };

    resize();
    render();
    window.addEventListener("resize", resize, { passive: true });
    document.addEventListener("visibilitychange", () => {
      if (document.hidden) cancelAnimationFrame(frameId);
      else render();
    });
  }

  const params = new URLSearchParams(window.location.search);
  const formMessage = document.querySelector("[data-form-message]");
  if (formMessage) {
    const inquiryStatus = params.get("inquiry");
    // The success and error wording is editable in the admin panel and arrives on data attributes.
    const messages = {
      sent: formMessage.dataset.messageSent || "Thank you. Your inquiry has been received and our team will contact you shortly.",
      invalid: "Please check the required fields and enter a valid phone number before sending.",
      error: formMessage.dataset.messageError || "We could not save your inquiry right now. Please call or email our team instead.",
    };
    if (messages[inquiryStatus]) {
      formMessage.textContent = messages[inquiryStatus];
      formMessage.setAttribute("role", inquiryStatus === "sent" ? "status" : "alert");
    }
  }
})();
