export const initMarkdownIt = () => {
  const md = window
    .markdownit({ html: true, linkify: true })
    .use(window.markdownitTaskLists);
  md.linkify.set({ fuzzyEmail: false });

  const defaultImageRender =
    md.renderer.rules.image ||
    ((tokens, idx, options, _, self) => {
      return self.renderToken(tokens, idx, options);
    });
  md.renderer.rules.image = (tokens, idx, options, env, self) => {
    tokens[idx].attrSet("loading", "lazy");

    return defaultImageRender(tokens, idx, options, env, self);
  };

  return md;
};

export const initDOMPurify = () => {
  DOMPurify.addHook("afterSanitizeAttributes", (node) => {
    if (node.href) {
      try {
        if (new URL(node.href).origin !== window.location.origin) {
          node.setAttribute("rel", "nofollow noopener noreferrer");
          node.setAttribute("target", "_blank");
        }
      } catch {}
    }
  });

  DOMPurify.setConfig({ FORBID_TAGS: ["style"] });
};

export const getPathnameLastSegment = () => {
  const segments = window.location.pathname.split("/");

  return segments.pop() || segments.pop();
};

export const initSplit = (elements, direction, key) => {
  const storageKey = `split-${key}-${getPathnameLastSegment()}`;

  let sizes = localStorage.getItem(storageKey);
  sizes = sizes ? JSON.parse(sizes) : key === "h" ? [50, 50] : [80, 20];

  return Split(elements, {
    direction,
    gutter: (_, direction) => {
      const gutter = document.createElement("div");
      gutter.className = `gutter gutter-${direction}`;

      const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
      svg.setAttribute("width", "16");
      svg.setAttribute("height", "16");
      svg.setAttribute("viewBox", "0 0 16 16");
      svg.setAttribute("fill", "currentColor");

      const path = document.createElementNS(
        "http://www.w3.org/2000/svg",
        "path",
      );
      if (direction === "horizontal") {
        path.setAttribute(
          "d",
          "M7 2a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0M7 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0M7 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0m-3 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0m-3 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0",
        );
      } else {
        path.setAttribute(
          "d",
          "M2 8a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2",
        );
      }

      svg.appendChild(path);

      gutter.appendChild(svg);

      return gutter;
    },
    gutterSize: 16,
    sizes,
    onDragEnd: (e) => {
      localStorage.setItem(storageKey, JSON.stringify(e));
    },
  });
};
