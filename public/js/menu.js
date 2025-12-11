const loaderElement = document.getElementById("loader");

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
        loaderElement.style.display = "flex";

        const response = await fetch("/logout", { method: "POST" });
        if (response.ok) {
          location.reload();
        } else {
          throw new Error();
        }
      } catch {
        loaderElement.style.display = "none";
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
        copyToClipboard(getText());
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
      copyToClipboard(document.getElementById("markdown").textContent);
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
      copyToClipboard(window.location.href);
      copyLinkElement.innerText = "Copied!";
      setTimeout(() => {
        copyLinkElement.innerText = "Link";
      }, 1000);
    }
  });
}

const copyToClipboard = (data) => {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(data);
  } else {
    const fakeElement = document.createElement("textarea");
    fakeElement.style.border = "0";
    fakeElement.style.fontSize = "12pt";
    fakeElement.style.margin = "0";
    fakeElement.style.padding = "0";
    fakeElement.style.position = "absolute";

    const isRTL = document.documentElement.getAttribute("dir") === "rtl";
    fakeElement.style[isRTL ? "right" : "left"] = "-9999px";
    const yPosition = window.pageYOffset || document.documentElement.scrollTop;
    fakeElement.style.top = `${yPosition}px`;

    fakeElement.setAttribute("readonly", "");
    fakeElement.value = data;

    document.body.appendChild(fakeElement);

    fakeElement.select();
    fakeElement.setSelectionRange(0, fakeElement.value.length);

    document.execCommand("copy");

    fakeElement.remove();
  }
};
