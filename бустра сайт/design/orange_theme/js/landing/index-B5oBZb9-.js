(function () {
  const l = document.createElement("link").relList;
  if (l && l.supports && l.supports("modulepreload")) return;
  for (const r of document.querySelectorAll('link[rel="modulepreload"]')) c(r);
  new MutationObserver(r => {
    for (const o of r) if (o.type === "childList") for (const u of o.addedNodes) u.tagName === "LINK" && u.rel === "modulepreload" && c(u)
  }).observe(document, { childList: !0, subtree: !0 });

  function p(r) {
    const o = {};
    return r.integrity && (o.integrity = r.integrity), r.referrerPolicy && (o.referrerPolicy = r.referrerPolicy), r.crossOrigin === "use-credentials" ? o.credentials = "include" : r.crossOrigin === "anonymous" ? o.credentials = "omit" : o.credentials = "same-origin", o
  }

  function c(r) {
    if (r.ep) return;
    r.ep = !0;
    const o = p(r);
    fetch(r.href, o)
  }
})();
(function () {
  var h = document.querySelectorAll(".js-withoutCoeff"), l = document.querySelectorAll(".js-withCoeff"),
    p = document.querySelectorAll(".js-total-output"), c = document.querySelector("[data-percent]")?.dataset?.percent;
  /*document.querySelector(".js-coeff").textContent = c;*/
  var r = x("#hero-range", ".js-hero-range-output", function () {
    u()
  }), o = x("#hero-range-long", ".js-hero-range-long-output", function () {
    u()
  });
  u();

  function u() {
    var n = parseInt(r.value), a = parseInt(o.value), e = a == 5, t = e ? 0 : c / 100, f = Math.round(n * a * t + n);
    window.Intl && (f = new Intl.NumberFormat("ru-RU").format(f)), p.forEach(function (i) {
      i.textContent = f
    }), h.forEach(function (i) {
      i.style.display = e ? "none" : "inline-block"
    }), l.forEach(function (i) {
      i.style.display = e ? "inline-block" : "none"
    })
  }

  function x(n, a, e) {
    var t = document.querySelector(n), f = document.querySelectorAll(a);
    i(), t.addEventListener("input", function (v) {
      i(), e && e(t)
    });

    function i() {
      var v = t.value, H = t.min, N = t.max, D = (v - H) / (N - H) * 100;
      t.style.backgroundSize = D + "% 100%", window.Intl && (v = new Intl.NumberFormat("ru-RU").format(v)), f.forEach(function (F) {
        F.textContent = v
      })
    }

    return t
  }

  var S = 5e3;
  q("#steps-slider", {
    destroy: !0,
    fixedWidth: 235,
    breakpoints: { 990: { destroy: !1, padding: "10px" } }
  }), q("#feedbacks-slider", {
    perPage: 2,
    perMove: 2,
    gap: "24px",
    padding: "32px",
    breakpoints: { 990: { padding: "10px", fixedWidth: 244, gap: "12px", perMove: 1, perPage: 1 } }
  });

  function q(n, a) {
    var e = document.querySelector(n), t = new Splide(e, Object.assign({
      autoplay: !0,
      arrows: !1,
      perMove: 1,
      rewind: !0,
      rewindByDrag: !0,
      cover: !1,
      pauseOnHover: !1,
      interval: S
    }, a));
    return t.mount(), e.style.setProperty("--duration", S + "ms"), t
  }

  var g = document.querySelector(".rules__btn"), _s = document.querySelectorAll(".rules__content");
  g.addEventListener("click", () => {
    _s.forEach(s => {
        s.classList.toggle("expanded"), s.style.maxHeight ? (s.style.maxHeight = null, g.textContent = "Читать") : (s.style.maxHeight = s.scrollHeight + "px", g.textContent = "Скрыть")
    })
  });
  var E = document.querySelector(".faq");
  E.addEventListener("click", function (n) {
    var a = n.target, e = a.closest(".faq-card");
    if (e) {
      e.classList.toggle("opened");
      var t = e.querySelector(".faq-card__text");
      t.style.maxHeight ? t.style.maxHeight = null : t.style.maxHeight = t.scrollHeight + "px"
    }
  });
  var C = new Date().getFullYear(), M = document.querySelectorAll(".year");
  M.forEach(function (n) {
    n.textContent = C
  });
  var m = document.querySelector(".burger"), d = document.querySelector(".header"), O = d.querySelector(".header__nav"),
    w = document.querySelector("body"), j = d.querySelector(".nav__inner"), y = !1;
  m.addEventListener("click", function () {
    y ? L() : P()
  }), window.addEventListener("resize", function () {
    _()
  });

  function P() {
    _(), m.classList.add("opened"), d.classList.add("menu-open"), w.classList.add("lock"), y = !0
  }

  function L() {
    m.classList.remove("opened"), d.classList.remove("menu-open"), w.classList.remove("lock"), y = !1
  }

  function _() {
    var n = d.offsetHeight, a = j.scrollHeight, e = n + a;
    O.style.height = window.innerHeight > e ? "auto" : window.innerHeight + "px"
  }

  var I = document.querySelector(".header-nav-overlay");
  I.addEventListener("click", function () {
    L()
  });
  var k = document.querySelector(".js-date"), b = () => {
    var n = new Date, a = n.getTime() + 1e3 * 5 * 60, e = new Date(a),
      t = e.getHours() + ":" + (e.getMinutes() < 10 ? "0" + e.getMinutes() : e.getMinutes());
    k.textContent = t
  };
  b(), setInterval(b, 1e3);
  var A = document.querySelector("#hero-btn");
  A.addEventListener("click", function () {
  })
})();
