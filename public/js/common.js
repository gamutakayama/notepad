export const initMarkdownIt = () => {
  const md = window
    .markdownit({ html: true, linkify: true })
    .use(window.markdownitTaskLists);
  md.linkify.set({ fuzzyEmail: false });

  // https://github.com/markdown-it/markdown-it/blob/master/docs/architecture.md#renderer
  // Remember the old renderer if overridden, or proxy to the default link_open renderer.
  const defaultLinkOpenRender =
    md.renderer.rules.link_open ||
    ((tokens, idx, options, _, self) => {
      return self.renderToken(tokens, idx, options);
    });
  // Add target="_blank" to all other links.
  md.renderer.rules.link_open = (tokens, idx, options, env, self) => {
    try {
      const href = new Map(tokens[idx].attrs).get("href");
      if (
        href.startsWith("/file/") ||
        new URL(href).origin !== window.location.origin
      ) {
        // Add a new `target` attribute, or replace the value of the existing one.
        tokens[idx].attrSet("target", "_blank");
      }
    } catch {}

    // Pass the token to the default renderer.
    return defaultLinkOpenRender(tokens, idx, options, env, self);
  };

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
        "path"
      );
      if (direction === "horizontal") {
        path.setAttribute(
          "d",
          "M7 2a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0M7 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0M7 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0m-3 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0m-3 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0"
        );
      } else {
        path.setAttribute(
          "d",
          "M2 8a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2"
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
