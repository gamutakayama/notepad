import { initCodeMirror } from "/public/js/codemirror.min.js";
import {
  getPathnameLastSegment,
  initMarkdownIt,
  initSplit,
} from "/public/js/common.js";
import { setupCopyRaw } from "/public/js/menu.js";

setupCopyRaw(() => editorView.state.doc.toString());

const renameElement = document.getElementById("rename");
if (renameElement) {
  renameElement.addEventListener("click", async (e) => {
    e.preventDefault();

    const name = prompt("Please enter a new note name.");
    if (name) {
      try {
        const response = await fetch(window.location.href, {
          body: JSON.stringify({ method: "rename", name }),
          headers: { "Content-Type": "application/json" },
          method: "POST",
        });
        if (response.redirected) {
          window.location.href = response.url;
        } else if (response.ok) {
          window.open("/", "_self");
        } else {
          throw new Error();
        }
      } catch {
        alert("Rename failed!");
      }
    }
  });
}

const deleteElement = document.getElementById("delete");
if (deleteElement) {
  deleteElement.addEventListener("click", async (e) => {
    e.preventDefault();

    if (confirm("Do you really want to delete?")) {
      try {
        const response = await fetch(window.location.href, {
          body: JSON.stringify({ method: "delete" }),
          headers: { "Content-Type": "application/json" },
          method: "POST",
        });
        if (response.redirected) {
          window.location.href = response.url;
        } else if (response.ok) {
          window.open("/", "_self");
        } else {
          throw new Error();
        }
      } catch {
        alert("Delete failed!");
      }
    }
  });
}

const debounce = (callback, wait) => {
  let timeoutId = null;

  return (...args) => {
    window.clearTimeout(timeoutId);
    timeoutId = window.setTimeout(() => callback(...args), wait);
  };
};

const renderMarkdown = debounce((docString) => {
  markdownElement.innerHTML = md.render(docString);
}, 500);

const editorView = initCodeMirror((update) => {
  const docString = update.state.doc.toString();

  if (content === docString) {
    statusElement.className = "status-success";
  } else {
    statusElement.className = "status-attention";
  }

  renderMarkdown(docString);
});

const uploadContent = async () => {
  const temp = editorView.state.doc.toString();
  if (content !== temp) {
    try {
      const response = await fetch(window.location.href, {
        body: JSON.stringify({ method: "edit", text: temp }),
        headers: { "Content-Type": "application/json" },
        method: "POST",
      });
      if (response.redirected) {
        window.location.href = response.url;
      } else if (response.ok) {
        if (temp === editorView.state.doc.toString()) {
          statusElement.className = "status-success";
        }
        content = temp;
      } else {
        throw new Error();
      }
    } catch {
      statusElement.className = "status-danger";
    } finally {
      setTimeout(uploadContent, 1000);
    }
  } else {
    setTimeout(uploadContent, 1000);
  }
};

const statusElement = document.getElementById("status");
const markdownElement = document.getElementById("markdown");

let content = editorView.state.doc.toString();
uploadContent();

const md = initMarkdownIt();
markdownElement.innerHTML = md.render(content);

let direction = window.innerWidth > 789 ? "horizontal" : "vertical";
let splitH = initSplit(["#codemirror", "#markdown"], direction, "h");

window.addEventListener("resize", () => {
  const newDirection = window.innerWidth > 789 ? "horizontal" : "vertical";
  if (newDirection !== direction) {
    direction = newDirection;
    splitH.destroy();
    splitH = initSplit(["#codemirror", "#markdown"], direction, "h");
  }
});

initSplit(["#editor", "#file-drop"], "vertical", "v");

document.getElementById("loader").style.display = "none";

const fileDropElement = document.getElementById("file-drop");
const inputFileElement = document.getElementById("input-file");
const filesElement = document.getElementById("files");
const filesLoaderElement = document.getElementById("files-loader");

let dragCounter = 0;

fileDropElement.addEventListener("dragenter", (e) => {
  e.preventDefault();
  if (filesLoaderElement.style.display === "none") {
    dragCounter++;
    fileDropElement.classList.add("files-drag-enter");
  }
});
fileDropElement.addEventListener("dragleave", (e) => {
  e.preventDefault();
  if (filesLoaderElement.style.display === "none") {
    dragCounter--;
    if (dragCounter === 0) {
      fileDropElement.classList.remove("files-drag-enter");
    }
  }
});
fileDropElement.addEventListener("dragover", (e) => {
  e.preventDefault();
});
fileDropElement.addEventListener("drop", (e) => {
  e.preventDefault();
  if (filesLoaderElement.style.display === "none") {
    dragCounter = 0;
    fileDropElement.classList.remove("files-drag-enter");
    uploadFile(e.dataTransfer.files, e.dataTransfer.items);
  }
});
document.getElementById("browse").addEventListener("click", (e) => {
  e.preventDefault();
  inputFileElement.click();
});
inputFileElement.addEventListener("change", (e) => {
  uploadFile(e.target.files);
  e.target.value = null;
});

const getFiles = async () => {
  try {
    filesLoaderElement.style.display = "flex";

    const response = await fetch(window.location.href, {
      body: JSON.stringify({ method: "files" }),
      headers: { "Content-Type": "application/json" },
      method: "POST",
    });
    if (response.redirected) {
      window.location.href = response.url;
    } else if (response.ok) {
      const files = await response.json();

      while (filesElement.firstChild) {
        filesElement.removeChild(filesElement.lastChild);
      }

      files.forEach((file) => {
        filesElement.appendChild(createFileElement(file));
      });
    }
  } catch (e) {
    console.log(e);
  } finally {
    filesLoaderElement.style.display = "none";
  }
};

const createFileElement = (filename) => {
  const fileElement = document.createElement("div");
  fileElement.classList.add("file");

  const filenameElement = document.createElement("a");
  filenameElement.href = `/file/${getPathnameLastSegment()}/${filename}`;
  filenameElement.target = "_blank";
  filenameElement.innerText = filename;
  fileElement.appendChild(filenameElement);

  const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
  svg.setAttribute("width", "1em");
  svg.setAttribute("height", "1em");
  svg.setAttribute("viewBox", "0 0 24 24");
  const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
  path.setAttribute("fill", "currentColor");
  path.setAttribute(
    "d",
    "M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12z"
  );
  svg.appendChild(path);

  const removeElement = document.createElement("button");
  removeElement.appendChild(svg);
  removeElement.addEventListener("click", async () => {
    if (confirm(`Do you really want to remove ${filename}?`)) {
      try {
        filesLoaderElement.style.display = "flex";

        const response = await fetch(window.location.href, {
          body: JSON.stringify({ method: "fileRemove", filename: filename }),
          headers: { "Content-Type": "application/json" },
          method: "POST",
        });
        if (response.redirected) {
          window.location.href = response.url;
        } else if (response.ok) {
          getFiles();
        } else {
          throw new Error();
        }
      } catch {
        filesLoaderElement.style.display = "none";
        alert(`Remove ${filename} failed!`);
      }
    }
  });

  fileElement.appendChild(removeElement);

  return fileElement;
};

getFiles();

const uploadFile = async (files, items) => {
  if (
    files.length !== 1 ||
    (items && items.length && items[0].webkitGetAsEntry().isDirectory)
  ) {
    alert("Please upload only one file!");
    return;
  }

  try {
    filesLoaderElement.style.display = "flex";

    const formData = new FormData();
    formData.append("file", files[0]);

    const response = await fetch(window.location.href, {
      body: formData,
      method: "POST",
    });
    if (response.redirected) {
      window.location.href = response.url;
    } else if (response.ok) {
      getFiles();
    } else {
      throw new Error();
    }
  } catch {
    filesLoaderElement.style.display = "none";
    alert("Upload failed!");
  }
};

window.addEventListener("beforeunload", (e) => {
  if (
    (statusElement.className && statusElement.className !== "status-success") ||
    filesLoaderElement.style.display !== "none"
  ) {
    e.preventDefault();
    e.returnValue = true;
  }
});
