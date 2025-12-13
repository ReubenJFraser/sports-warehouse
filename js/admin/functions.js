/* ======================================================================
   SPORTS WAREHOUSE — ADMIN FUNCTION HANDLER (AJAX BUTTON CALLS)
   Attached globally to admin layout (admin/layout.php)
   ====================================================================== */

document.addEventListener("DOMContentLoaded", () => {
  document.body.addEventListener("click", async (e) => {
    const btn = e.target.closest("[data-run-function]");
    if (!btn) return;

    e.preventDefault();
    btn.disabled = true;
    const original = btn.innerHTML;
    btn.innerHTML = `<span class="spinner"></span>`;

    const func = btn.getAttribute("data-run-function");
    const payload = btn.getAttribute("data-payload") || null;

    try {
      const res = await fetch("/admin/run_function.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          function: func,
          payload: payload
        })
      });

      const json = await res.json();

      if (json.success) {
        btn.classList.add("btn--success");
        btn.innerHTML = json.message || "✓ Success";
      } else {
        throw new Error(json.message || "Unknown error");
      }
    } catch (err) {
      console.error("Function call failed:", err);
      btn.classList.add("btn--error");
      btn.innerHTML = "✖ Error";
    }

    setTimeout(() => {
      btn.innerHTML = original;
      btn.disabled = false;
      btn.classList.remove("btn--success", "btn--error");
    }, 2200);
  });
});


