document.querySelectorAll(".menu-item").forEach((item) => {
  const title = item.querySelector(".menu-item > a");
  const dropdown = item.querySelector(".menu-dropdown");

  if (title && dropdown) {
    title.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();

      item.classList.toggle("open");
      document.querySelectorAll(".menu-item").forEach((other) => {
        if (other !== item) {
          other.classList.remove("open");
        }
      });
    });
    dropdown.addEventListener("click", (e) => {
      e.stopPropagation();
    });
  }
});
document.addEventListener("click", () => {
  document.querySelectorAll(".menu-item").forEach((item) => {
    item.classList.remove("open");
  });
});

const newElement = document.getElementById("new");
if (newElement) {
  newElement.addEventListener("click", (e) => {
    e.preventDefault();

    const name = prompt("Please enter a note name, leave blank for random.");
    if (name != null) {
      window.open(`/edit/${name}`, "_self");
    }
  });
}

const logoutElement = document.getElementById("logout");
if (logoutElement) {
  logoutElement.addEventListener("click", async (e) => {
    e.preventDefault();

    if (confirm("Do you really want to logout?")) {
      try {
        const response = await fetch("/logout", { method: "POST" });
        if (response.ok) {
          location.reload();
        } else {
          throw new Error();
        }
      } catch {
        alert("Logout failed!");
      }
    }
  });
}

export const setupCopyRaw = (getText) => {
  const copyRawElement = document.getElementById("copy-raw");
  if (copyRawElement) {
    copyRawElement.addEventListener("click", (e) => {
      e.preventDefault();

      if (copyRawElement.innerText === "Raw") {
        navigator.clipboard.writeText(getText());
        copyRawElement.innerText = "Copied!";
        setTimeout(() => {
          copyRawElement.innerText = "Raw";
        }, 1000);
      }
    });
  }
};

const copyTextElement = document.getElementById("copy-text");
if (copyTextElement) {
  copyTextElement.addEventListener("click", (e) => {
    e.preventDefault();

    if (copyTextElement.innerText === "Text") {
      navigator.clipboard.writeText(
        document.getElementById("markdown").textContent
      );
      copyTextElement.innerText = "Copied!";
      setTimeout(() => {
        copyTextElement.innerText = "Text";
      }, 1000);
    }
  });
}

const copyLinkElement = document.getElementById("copy-link");
if (copyLinkElement) {
  copyLinkElement.addEventListener("click", (e) => {
    e.preventDefault();

    if (copyLinkElement.innerText === "Link") {
      navigator.clipboard.writeText(window.location.href);
      copyLinkElement.innerText = "Copied!";
      setTimeout(() => {
        copyLinkElement.innerText = "Link";
      }, 1000);
    }
  });
}
